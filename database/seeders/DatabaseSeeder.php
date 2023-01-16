<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->seedAirports();
    }

    public function seedAirports()
    {
        $airports = collect(json_decode(file_get_contents(storage_path('app/seed/airports.json')), true));
        $this->command->info("Seeding Airports");
        $this->command->withProgressBar($airports, function ($airport) {
            $ap = new Airport();
            $ap->fill($airport);
            $ap->save();
        });
        $this->command->info("Finish!");
    }
}
