<?php

use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Notifications\OvertimeRequestNotification;
use Illuminate\Support\Facades\Notification;

$employee = Employee::where('full_name', 'Employee Medis 1')->first();
$request = OvertimeRequest::create([
    'employee_id' => $employee->id,
    'date' => now()->toDateString(),
    'start_time' => '17:00',
    'end_time' => '20:00',
    'description' => 'Triggering from script',
    'status' => 'pending_manager',
]);
$approvers = User::role('manager')->get();
Notification::send($approvers, new OvertimeRequestNotification($request, $employee, 'submitted'));
