<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\Scheduling;
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
        User::factory(5)->create();
        Pet::factory(15)->create();
        Service::factory(5)->create();
        Scheduling::factory(5)->create();
        MedicalRecord::factory(5)->create();
    }
}
