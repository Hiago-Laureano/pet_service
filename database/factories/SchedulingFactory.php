<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scheduling>
 */
class SchedulingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "user_id" => User::all()->random()->id,
            "pet_id" => Pet::all()->random()->id,
            "service_id" => Service::all()->random()->id,
            "date" => fake()->dateTimeThisMonth(),
            "finished" => fake()->boolean()
        ];
    }
}
