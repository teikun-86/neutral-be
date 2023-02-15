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
        $cities = [];
        $airports = collect(json_decode(file_get_contents(storage_path('app/seed/airports.json')), true))
            ->filter(function($airport) {
                return $airport['type'] === 'airport';
            });
        $airports->each(function($airport) use(&$cities) {
            $cities[$airport['country']][] = $airport['city'];
        });

        $this->seedCountryAndCity($cities);

        $countries = Country::with('cities')->get();

        $this->command->info("Seeding Airports");
        $this->command->withProgressBar($airports, function ($airport) use($countries) {
            $country = $countries->where('name', $airport['country'])->first();
            if (!$country) {
                $this->command->info("Country {$airport['country']} is not found. Creating new country...");
                $country = Country::create([
                    'name' => $airport['country'],
                    'code' => $airport['country_code'],
                    'flag' => "https://purecatamphetamine.github.io/country-flag-icons/3x2/{$airport['country_code']}.svg"
                ]);
            }
            
            $city = $country->cities->where('name', $airport['city'])->first();
            if (!$city) {
                $this->command->info("City {$airport['city']} is not found. Creating new city...");
                $city = $country->cities()->create([
                    'name' => $airport['city']
                ]);
            }
            
            $data = [
                'name' => $airport['name'],
                'iata' => $airport['iata'],
                'location' => $airport['location'],
                'type' => $airport['type'],
                'alias' => $airport['alias'],
                'country_id' => $country->id,
                'city_id' => $city->id
            ];
            
            Airport::create($data);
        });
        $this->command->info("Finish!");
    }

    public function seedCountryAndCity($cities)
    {
        $this->command->info("Seeding Countries");
        $countries = collect(json_decode(file_get_contents(storage_path('app/seed/countries.json')), true));
        $fillable = (new Country())->getFillable();
        $this->command->withProgressBar($countries, function ($country) use($fillable, $cities) {
            if (!isset($cities[$country['name']])) {
                return;
            }
            
            $fields = collect($country)->only($fillable)->toArray();
            $fields['flag'] = "https://purecatamphetamine.github.io/country-flag-icons/3x2/{$country['code']}.svg";
            $country =  new Country($fields);
            $country->save();
            $cityResults = collect($cities[$country->name])->map(function($city) use($country) {
                return [
                    'name' => $city,
                    'country_id' => $country->id
                ];
            });
            $country->cities = $country->cities()->createMany($cityResults->toArray());
        });
        $this->command->info("Finish!");
    }
}
