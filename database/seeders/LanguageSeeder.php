<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Russian', 'code' => 'ru'],
            ['name' => 'Chinese', 'code' => 'zh'],
            ['name' => 'Japanese', 'code' => 'ja'],
            ['name' => 'Korean', 'code' => 'ko'],
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