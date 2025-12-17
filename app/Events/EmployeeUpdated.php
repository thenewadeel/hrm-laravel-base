<?php

namespace App\Events;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;

    public int $organization_id;

    public Employee $employee;

    public function __construct(User $user, Employee $employee)
    {
        $this->user = $user;
        $this->organization_id = $employee->organization_id;
        $this->employee = $employee;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
