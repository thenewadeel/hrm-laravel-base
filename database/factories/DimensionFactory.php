<?php

namespace Database\Factories;

use App\Models\Dimension;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DimensionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->bothify('DIM-#####'),
            'type' => $this->faker->randomElement(['cost_center', 'project', 'branch', 'department', 'team']),
            'description' => $this->faker->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Dimension $dimension) {
            if (! empty($dimension->organization_id)) {
                return;
            }

            $dimension->organization_id = $this->resolveOrganizationId();
        });
    }

    protected function resolveOrganizationId(): int
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'operatingOrganizationId') && $user->operatingOrganizationId) {
            return (int) $user->operatingOrganizationId;
        }

        $firstOrganization = DB::table('organizations')->value('id');
        if ($firstOrganization) {
            return (int) $firstOrganization;
        }

        throw new \RuntimeException('No organization available for the dimension factory.');
    }
}
