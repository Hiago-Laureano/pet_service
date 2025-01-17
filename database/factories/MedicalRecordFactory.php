<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "access_code" => fake()->unique()->regexify("[A-Za-z0-9]{60}"),
            "user_id" => User::all()->random()->id,
            "pet_id" => Pet::all()->random()->id,
            "observation" => fake()->text()
        ];
    }
}
