<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    public function run()
    {
        $districtsByRegion = [
            'Dar es Salaam' => ['Ilala', 'Kinondoni', 'Temeke', 'Kigamboni', 'Ubungo'],
            'Arusha' => ['Arusha City', 'Arumeru', 'Karatu', 'Monduli', 'Longido', 'Ngorongoro'],
            'Dodoma' => ['Dodoma Urban', 'Bahi', 'Chamwino', 'Chemba', 'Kondoa', 'Kongwa', 'Mpwapwa'],
            // Add all other regions and their districts here...
        ];

        foreach ($districtsByRegion as $regionName => $districts) {
            $region = Region::where('name', $regionName)->first();
            if ($region) {
                foreach ($districts as $districtName) {
                    District::create([
                        'region_id' => $region->id,
                        'name' => $districtName,
                    ]);
                }
            }
        }
    }
}

