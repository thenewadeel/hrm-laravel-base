<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +5 days');
        $totalDays = $startDate->diff($endDate)->days + 1;

        return [
            'employee_id' => Employee::factory(),
            'organization_id' => Organization::factory(),
            'leave_type' => $this->faker->randomElement(['sick', 'vacation', 'personal', 'emergency']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null,
            'rejected_by' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'applied_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'approved_by' => null,
                'rejected_by' => null,
                'approved_at' => null,
                'rejected_at' => null,
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_by' => User::factory(),
                'approved_at' => $this->faker->dateTimeBetween($attributes['applied_at'], 'now'),
                'rejected_by' => null,
                'rejected_at' => null,
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejected_by' => User::factory(),
                'rejected_at' => $this->faker->dateTimeBetween($attributes['applied_at'], 'now'),
                'rejection_reason' => $this->faker->sentence(),
                'approved_by' => null,
                'approved_at' => null,
            ];
        });
    }

    public function sick()
    {
        return $this->state(function (array $attributes) {
            return [
                'leave_type' => 'sick',
                'reason' => $this->faker->randomElement(['Feeling unwell', 'Medical appointment', 'Health issues']),
            ];
        });
    }

    public function vacation()
    {
        return $this->state(function (array $attributes) {
            return [
                'leave_type' => 'vacation',
                'reason' => $this->faker->randomElement(['Family vacation', 'Personal travel', 'Holiday']),
            ];
        });
    }

    public function forOrganization(Organization $organization)
    {
        return $this->state(function (array $attributes) use ($organization) {
            return [
                'organization_id' => $organization->id,
            ];
        });
    }

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
                'organization_id' => $user->current_organization_id,
            ];
        });
    }

    public function currentYear()
    {
        return $this->state(function (array $attributes) {
            $year = now()->year;
            $startDate = $this->faker->dateTimeBetween("{$year}-01-01", "{$year}-12-31");
            $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +7 days');
            $totalDays = $startDate->diff($endDate)->days + 1;

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
            ];
        });
    }
}
