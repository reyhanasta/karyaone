<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Notifications\OvertimeRequestNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permissions = [
        'overtime-request.view',
        'overtime-request.create',
        'overtime-request.create.any',
        'overtime-request.edit',
        'overtime-request.approve.hrd',
        'overtime-request.approve.manager',
        'overtime-request.approve.director',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm]);
    }

    $hrAdminRole = Role::firstOrCreate(['name' => 'hr-admin']);
    $hrAdminRole->syncPermissions(['overtime-request.view', 'overtime-request.approve.hrd']);
    $this->hrAdminUser = User::factory()->create();
    $this->hrAdminUser->assignRole('hr-admin');

    $managerRole = Role::firstOrCreate(['name' => 'manager']);
    $managerRole->syncPermissions(['overtime-request.view', 'overtime-request.approve.manager']);
    $this->managerUser = User::factory()->create();
    $this->managerUser->assignRole('manager');

    $employeeRole = Role::firstOrCreate(['name' => 'employee']);
    $employeeRole->syncPermissions(['overtime-request.view', 'overtime-request.create']);

    $department = Department::firstOrCreate(['name' => 'IT Department']);
    $this->managerUser->managedDepartments()->attach($department->id);
    $position = Position::firstOrCreate(['name' => 'IT Staff']);

    $this->employeeUser = User::factory()->create();
    $this->employeeUser->assignRole('employee');
    $this->employee = Employee::create([
        'user_id' => $this->employeeUser->id,
        'full_name' => 'Normal Employee',
        'department_id' => $department->id,
        'position_id' => $position->id,
        'join_date' => now()->toDateString(),
        'leave_quota' => 12,
    ]);
});

test('overtime request submission broadcasts a notification to HR and Managers', function () {
    Event::fake([NotificationSent::class]);

    $response = $this->actingAs($this->employeeUser)->post(route('overtime-requests.store'), [
        'date' => now()->toDateString(),
        'start_time' => '17:00',
        'end_time' => '20:00',
        'description' => 'Fixing bug XYZ',
    ]);

    $response->assertRedirect(route('overtime-requests.index'));

    Event::assertDispatched(
        NotificationSent::class,
        function ($event) {
            if (! $event->notification instanceof OvertimeRequestNotification) {
                return false;
            }
            if ($event->channel !== 'broadcast') {
                return false;
            }

            $broadcastData = $event->notification->toBroadcast($event->notifiable)->data;

            return $broadcastData['type'] === 'overtime_request'
                && $broadcastData['action'] === 'submitted'
                && $broadcastData['employee_id'] === $this->employee->id;
        }
    );
});
