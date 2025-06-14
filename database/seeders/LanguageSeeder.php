<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        $countrySlug = Str::slug('United States');
        $countryId = DB::table(config('field_translation.table_names.countries', 'dbt_countries'))
            ->where('slug', $countrySlug)
            ->value('id');

        $languages = [
            ['name' => 'English', 'code' => 'en', 'slug' => Str::slug('English'), 'country_id' => $countryId],
            ['name' => 'Spanish', 'code' => 'es', 'slug' => Str::slug('Spanish'), 'country_id' => $countryId],
            ['name' => 'French', 'code' => 'fr', 'slug' => Str::slug('French'), 'country_id' => $countryId],
            ['name' => 'German', 'code' => 'de', 'slug' => Str::slug('German'), 'country_id' => $countryId],
            ['name' => 'Italian', 'code' => 'it', 'slug' => Str::slug('Italian'), 'country_id' => $countryId],
            ['name' => 'Portuguese', 'code' => 'pt', 'slug' => Str::slug('Portuguese'), 'country_id' => $countryId],
            ['name' => 'Russian', 'code' => 'ru', 'slug' => Str::slug('Russian'), 'country_id' => $countryId],
            ['name' => 'Chinese', 'code' => 'zh', 'slug' => Str::slug('Chinese'), 'country_id' => $countryId],
            ['name' => 'Japanese', 'code' => 'ja', 'slug' => Str::slug('Japanese'), 'country_id' => $countryId],
            ['name' => 'Korean', 'code' => 'ko', 'slug' => Str::slug('Korean'), 'country_id' => $countryId],
        ];

        $table = config('field_translation.table_names.languages', 'dbt_languages');
        foreach ($languages as $language) {
            DB::table($table)->updateOrInsert(
                ['code' => $language['code']],
                $language
            );
        }
    }
} 