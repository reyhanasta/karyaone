<?php
$employee = \App\Models\Employee::where('full_name', 'Employee Medis 1')->first();
$request = \App\Models\OvertimeRequest::create([
    'employee_id' => $employee->id,
    'date' => now()->toDateString(),
    'start_time' => '17:00',
    'end_time' => '20:00',
    'description' => 'Triggering from script',
    'status' => 'pending_manager'
]);
$approvers = \App\Models\User::role('manager')->get();
\Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\OvertimeRequestNotification($request, $employee, 'submitted'));
