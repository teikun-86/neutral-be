<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Airport;
use App\Models\Country;
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
        $airports = collect(json_decode(file_get_contents(storage_path('app/seed/airports.json')), true))->filter(function($airport) {
            return $airport['type'] === 'airport';
        });
        $this->command->info("Seeding Airports");
        $fillable = (new Airport())->getFillable();
        $this->command->withProgressBar($airports, function ($airport) use($fillable) {
            Airport::create(collect($airport)->only($fillable)->toArray());
        });
        $this->command->info("Finish!");
        $this->seedCountry();
    }

    public function seedCountry()
    {
        $countries = collect(json_decode(file_get_contents(storage_path('app/seed/countries.json')), true));
        $this->command->info("Seeding Countries");
        $fillable = (new Country())->getFillable();
        $this->command->withProgressBar($countries, function ($country) use($fillable) {
            $fields = collect($country)->only($fillable)->toArray();
            $fields['flag'] = "https://purecatamphetamine.github.io/country-flag-icons/3x2/{$country['code']}.svg";
            Country::create($fields);
        });
        $this->command->info("Finish!");
    }
}
