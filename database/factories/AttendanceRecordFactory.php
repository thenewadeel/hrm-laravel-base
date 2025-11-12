<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition()
    {
        $recordDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $punchIn = $this->faker->dateTimeBetween($recordDate->format('Y-m-d') . ' 08:00:00', $recordDate->format('Y-m-d') . ' 10:00:00');
        $punchOut = $this->faker->dateTimeBetween($recordDate->format('Y-m-d') . ' 16:00:00', $recordDate->format('Y-m-d') . ' 18:00:00');

        $status = $this->faker->randomElement(['present', 'late', 'absent', 'leave', 'missed_punch']);

        return [
            'employee_id' => Employee::factory(),
            'organization_id' => Organization::factory(),
            'record_date' => $recordDate,
            'punch_in' => $status === 'absent' ? null : $punchIn,
            'punch_out' => $status === 'absent' ? null : $punchOut,
            'total_hours' => $status === 'absent' ? 0 : $this->faker->randomFloat(2, 6, 10),
            'status' => $status,
            'biometric_id' => $this->faker->optional()->numerify('BIO#####'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function present()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'present',
                'punch_in' => $this->faker->dateTimeBetween($attributes['record_date']->format('Y-m-d') . ' 08:00:00', $attributes['record_date']->format('Y-m-d') . ' 09:00:00'),
                'punch_out' => $this->faker->dateTimeBetween($attributes['record_date']->format('Y-m-d') . ' 16:00:00', $attributes['record_date']->format('Y-m-d') . ' 17:00:00'),
                'total_hours' => $this->faker->randomFloat(2, 7, 9),
            ];
        });
    }

    public function late()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'late',
                'punch_in' => $this->faker->dateTimeBetween($attributes['record_date']->format('Y-m-d') . ' 09:15:00', $attributes['record_date']->format('Y-m-d') . ' 10:30:00'),
                'punch_out' => $this->faker->dateTimeBetween($attributes['record_date']->format('Y-m-d') . ' 17:00:00', $attributes['record_date']->format('Y-m-d') . ' 18:00:00'),
                'total_hours' => $this->faker->randomFloat(2, 6, 8),
            ];
        });
    }

    public function absent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'absent',
                'punch_in' => null,
                'punch_out' => null,
                'total_hours' => 0,
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
}
