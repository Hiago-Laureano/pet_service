<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
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
            "name" => fake()->name(),
            "species" => fake()->numerify("species-##"),
            "breed" => fake()->numerify("breed-##"),
            "weight" => fake()->randomFloat(1, 1, 50),
            "age" => fake()->numberBetween(1, 15),
            "gender" => fake()->randomElement(["M", "F"]),
            "agressive" => fake()->boolean(),
            "active" => true
        ];
    }
}
