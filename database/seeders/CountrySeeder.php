<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $country = [
            'name' => 'United States',
            'slug' => Str::slug('United States'),
            'flag' => 'us',
            'time_zone' => 'America/New_York',
            'currency_code' => 'USD',
        ];

        DB::table(config('field_translation.table_names.countries', 'dbt_countries'))->updateOrInsert(
            ['slug' => $country['slug']],
            $country
        );
    }
} 