<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            ['name' => 'English', 'code' => 'en', 'slug' => Str::slug('English')],
            ['name' => 'Spanish', 'code' => 'es', 'slug' => Str::slug('Spanish')],
            ['name' => 'French', 'code' => 'fr', 'slug' => Str::slug('French')],
            ['name' => 'German', 'code' => 'de', 'slug' => Str::slug('German')],
            ['name' => 'Italian', 'code' => 'it', 'slug' => Str::slug('Italian')],
            ['name' => 'Portuguese', 'code' => 'pt', 'slug' => Str::slug('Portuguese')],
            ['name' => 'Russian', 'code' => 'ru', 'slug' => Str::slug('Russian')],
            ['name' => 'Chinese', 'code' => 'zh', 'slug' => Str::slug('Chinese')],
            ['name' => 'Japanese', 'code' => 'ja', 'slug' => Str::slug('Japanese')],
            ['name' => 'Korean', 'code' => 'ko', 'slug' => Str::slug('Korean')],
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