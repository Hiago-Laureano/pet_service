<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(4)->create();
        Pet::factory(10)->create();
        Service::factory(3)->create();
    }
}