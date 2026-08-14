<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\ReplacementGroup;
use App\Models\Shift;
use App\Models\ShiftChangeRequest;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RoleAndPermissionSeeder::class);
    $this->admin = User::where('email', 'admin@admin.com')->first();

    // Setup basic org structure
    $this->department = Department::create(['name' => 'Medis']);
    $this->position = Position::create(['name' => 'Dokter']);
    $this->shift = Shift::create([
        'name' => 'Pagi',
        'start_time' => '07:00',
        'end_time' => '14:00',
        'department_id' => $this->department->id,
        'is_active' => true,
    ]);

    // Setup employees
    $this->employeeA = Employee::factory()->create([
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'full_name' => 'Employee Alpha',
    ]);
    $this->employeeA->user->assignRole('employee');

    $this->employeeB = Employee::factory()->create([
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'full_name' => 'Employee Beta',
    ]);
    $this->employeeB->user->assignRole('employee');

    // Create a Karu user and assign managed department
    $this->karu = User::factory()->create(['email' => 'karu@test.com']);
    $this->karu->assignRole('employee');
    $this->karu->givePermissionTo('shift-change-request.approve.manager');
    $this->karu->managedDepartments()->attach($this->department->id);
});

test('employee can view shift change requests index', function () {
    actingAs($this->employeeA->user)
        ->get(route('shift-change-requests.index'))
        ->assertStatus(200);
});

test('employee can submit a shift change request to a colleague', function () {
    actingAs($this->employeeA->user)
        ->post(route('shift-change-requests.store'), [
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $this->shift->id,
            'target_id' => $this->employeeB->id,
            'reason' => 'Ada keperluan keluarga',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('shift_change_requests', [
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'status' => 'pending_manager',
    ]);
});

test('admin can submit a shift change request on behalf of an employee', function () {
    actingAs($this->admin)
        ->post(route('shift-change-requests.store'), [
            'requester_id' => $this->employeeA->id,
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $this->shift->id,
            'target_id' => $this->employeeB->id,
            'reason' => 'Created by admin',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('shift_change_requests', [
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'status' => 'pending_hrd',
    ]);
});

test('karu can approve a shift change request', function () {
    $request = ShiftChangeRequest::create([
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'request_date' => now()->addDay()->format('Y-m-d'),
        'requester_shift_id' => $this->shift->id,
        'status' => 'pending_manager',
        'reason' => 'Test',
    ]);

    actingAs($this->karu)
        ->post(route('shift-change-requests.approve-manager', $request))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($request->refresh()->status)->toBe('pending_hrd');
});

test('hr admin can approve a manager-approved shift change request', function () {
    $request = ShiftChangeRequest::create([
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'request_date' => now()->addDay()->format('Y-m-d'),
        'requester_shift_id' => $this->shift->id,
        'status' => 'pending_hrd',
        'manager_approved_by' => $this->karu->id,
        'manager_approved_at' => now(),
        'reason' => 'Test',
    ]);

    actingAs($this->admin)
        ->post(route('shift-change-requests.approve-hrd', $request))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($request->refresh()->status)->toBe('approved');
});

test('duplicate shift change request is prevented', function () {
    ShiftChangeRequest::create([
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'request_date' => now()->addDay()->format('Y-m-d'),
        'requester_shift_id' => $this->shift->id,
        'status' => 'pending_manager',
        'reason' => 'Existing',
    ]);

    actingAs($this->employeeA->user)
        ->post(route('shift-change-requests.store'), [
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $this->shift->id,
            'target_id' => $this->employeeB->id,
            'reason' => 'Duplicate',
        ])
        ->assertSessionHas('error');
});

test('shift change request can be rejected', function () {
    $request = ShiftChangeRequest::create([
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'request_date' => now()->addDay()->format('Y-m-d'),
        'requester_shift_id' => $this->shift->id,
        'status' => 'pending_manager',
        'reason' => 'Test',
    ]);

    actingAs($this->karu)
        ->post(route('shift-change-requests.reject', $request), [
            'notes' => 'Not allowed',
        ])
        ->assertRedirect();

    expect($request->refresh()->status)->toBe('rejected');
    expect($request->refresh()->notes)->toBe('Not allowed');
});

test('unauthorized user cannot view shift change request', function () {
    $request = ShiftChangeRequest::create([
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'request_date' => now()->addDay()->format('Y-m-d'),
        'requester_shift_id' => $this->shift->id,
        'status' => 'pending_manager',
        'reason' => 'Secret',
    ]);

    $otherUser = User::factory()->create();
    $otherUser->assignRole('employee');

    actingAs($otherUser)
        ->get(route('shift-change-requests.show', $request))
        ->assertForbidden();
});

test('apoteker can request a shift change targeting asisten apoteker in the same replacement group', function () {
    $farmasi = Department::create(['name' => 'Farmasi & Keuangan']);
    $apoteker = Position::create(['name' => 'Apoteker', 'department_id' => $farmasi->id]);
    $asisten = Position::create(['name' => 'Asisten Apoteker', 'department_id' => $farmasi->id]);
    $group = ReplacementGroup::create(['name' => 'Farmasi']);
    $apoteker->update(['replacement_group_id' => $group->id]);
    $asisten->update(['replacement_group_id' => $group->id]);

    $requester = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $apoteker->id,
        'full_name' => 'Apoteker Satu',
    ]);
    $requester->user->assignRole('employee');

    $target = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $asisten->id,
        'full_name' => 'Asisten Apoteker Satu',
    ]);
    $target->user->assignRole('employee');

    $shift = Shift::create([
        'name' => 'Pagi Farmasi',
        'start_time' => '07:00',
        'end_time' => '14:00',
        'department_id' => $farmasi->id,
        'is_active' => true,
    ]);

    actingAs($requester->user)
        ->post(route('shift-change-requests.store'), [
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $shift->id,
            'target_id' => $target->id,
            'reason' => 'Ada keperluan keluarga',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('shift_change_requests', [
        'requester_id' => $requester->id,
        'target_id' => $target->id,
        'status' => 'pending_manager',
    ]);
});

test('shift change request is rejected when target is in a different replacement group', function () {
    $farmasi = Department::create(['name' => 'Farmasi & Keuangan']);
    $apoteker = Position::create(['name' => 'Apoteker', 'department_id' => $farmasi->id]);
    $asisten = Position::create(['name' => 'Asisten Apoteker', 'department_id' => $farmasi->id]);
    $groupFarmasi = ReplacementGroup::create(['name' => 'Farmasi']);
    $groupKeuangan = ReplacementGroup::create(['name' => 'Keuangan']);
    $apoteker->update(['replacement_group_id' => $groupFarmasi->id]);
    $asisten->update(['replacement_group_id' => $groupKeuangan->id]);

    $requester = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $apoteker->id,
    ]);
    $requester->user->assignRole('employee');

    $target = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $asisten->id,
    ]);
    $target->user->assignRole('employee');

    $shift = Shift::create([
        'name' => 'Pagi Farmasi',
        'start_time' => '07:00',
        'end_time' => '14:00',
        'department_id' => $farmasi->id,
        'is_active' => true,
    ]);

    actingAs($requester->user)
        ->post(route('shift-change-requests.store'), [
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $shift->id,
            'target_id' => $target->id,
            'reason' => 'Test cross group',
        ])
        ->assertSessionHasErrors('target_id');

    $this->assertDatabaseCount('shift_change_requests', 0);
});

test('shift change request is rejected when target is in a different department', function () {
    $deptA = Department::create(['name' => 'Departemen A']);
    $deptB = Department::create(['name' => 'Departemen B']);
    $group = ReplacementGroup::create(['name' => 'Grup Bersama']);

    $positionA = Position::create(['name' => 'Staff A', 'department_id' => $deptA->id]);
    $positionB = Position::create(['name' => 'Staff B', 'department_id' => $deptB->id]);
    $positionA->update(['replacement_group_id' => $group->id]);
    $positionB->update(['replacement_group_id' => $group->id]);

    $requester = Employee::factory()->create([
        'department_id' => $deptA->id,
        'position_id' => $positionA->id,
    ]);
    $requester->user->assignRole('employee');

    $target = Employee::factory()->create([
        'department_id' => $deptB->id,
        'position_id' => $positionB->id,
    ]);
    $target->user->assignRole('employee');

    $shift = Shift::create([
        'name' => 'Pagi A',
        'start_time' => '07:00',
        'end_time' => '14:00',
        'department_id' => $deptA->id,
        'is_active' => true,
    ]);

    actingAs($requester->user)
        ->post(route('shift-change-requests.store'), [
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $shift->id,
            'target_id' => $target->id,
            'reason' => 'Test cross department',
        ])
        ->assertSessionHasErrors('target_id');

    $this->assertDatabaseCount('shift_change_requests', 0);
});

test('create page only returns group-eligible target employees', function () {
    $farmasi = Department::create(['name' => 'Farmasi & Keuangan']);
    $apoteker = Position::create(['name' => 'Apoteker', 'department_id' => $farmasi->id]);
    $asisten = Position::create(['name' => 'Asisten Apoteker', 'department_id' => $farmasi->id]);
    $groupFarmasi = ReplacementGroup::create(['name' => 'Farmasi']);
    $groupKeuangan = ReplacementGroup::create(['name' => 'Keuangan']);
    $apoteker->update(['replacement_group_id' => $groupFarmasi->id]);
    $asisten->update(['replacement_group_id' => $groupFarmasi->id]);

    $requester = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $apoteker->id,
    ]);
    $requester->user->assignRole('employee');

    $eligible = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $asisten->id,
        'full_name' => 'Asisten Apoteker Satu',
    ]);
    $eligible->user->assignRole('employee');

    // Same department but different group → not eligible.
    $otherGroupPosition = Position::create(['name' => 'Keuangan', 'department_id' => $farmasi->id]);
    $otherGroupPosition->update(['replacement_group_id' => $groupKeuangan->id]);
    $notEligibleByGroup = Employee::factory()->create([
        'department_id' => $farmasi->id,
        'position_id' => $otherGroupPosition->id,
        'full_name' => 'Staf Keuangan',
    ]);
    $notEligibleByGroup->user->assignRole('employee');

    actingAs($requester->user)
        ->get(route('shift-change-requests.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shift-change-requests/create')
            ->has('targetEmployees', 1)
            ->where('targetEmployees.0.id', $eligible->id)
            ->where('targetEmployees.0.position.replacement_group_name', 'Farmasi')
        );
});

test('edit update rejects target outside the replacement group', function () {
    $this->employeeA->user->givePermissionTo('shift-change-request.edit');

    $deptB = Department::create(['name' => 'Departemen B']);
    $group = ReplacementGroup::create(['name' => 'Grup B']);

    $positionB = Position::create(['name' => 'Staff B', 'department_id' => $deptB->id]);
    $positionB->update(['replacement_group_id' => $group->id]);

    $outsideTarget = Employee::factory()->create([
        'department_id' => $deptB->id,
        'position_id' => $positionB->id,
    ]);
    $outsideTarget->user->assignRole('employee');

    $request = ShiftChangeRequest::create([
        'requester_id' => $this->employeeA->id,
        'target_id' => $this->employeeB->id,
        'request_date' => now()->addDay()->format('Y-m-d'),
        'requester_shift_id' => $this->shift->id,
        'status' => 'pending_manager',
        'reason' => 'Test',
    ]);

    actingAs($this->employeeA->user)
        ->put(route('shift-change-requests.update', $request), [
            'request_date' => now()->addDay()->format('Y-m-d'),
            'requester_shift_id' => $this->shift->id,
            'target_id' => $outsideTarget->id,
            'reason' => 'Updated reason',
        ])
        ->assertSessionHasErrors('target_id');

    expect($request->refresh()->target_id)->toBe($this->employeeB->id);
});
