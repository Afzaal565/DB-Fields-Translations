<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | This is the default language that will be used when no language is specified
    | or when the requested language is not available.
    |
    */
    'default_language' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Available Languages
    |--------------------------------------------------------------------------
    |
    | This array contains all the languages that your application supports.
    | The key is the language code and the value is the language name.
    |
    */
    'languages' => [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        // Add more languages as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure the caching behavior for translations.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 60 * 24, // 24 hours in minutes
        'prefix' => 'field_translations_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | Configure the database tables and columns used by the package.
    |
    */
    'database' => [
        'languages_table' => 'languages',
        'translations_table' => 'translations',
        'columns' => [
            'languages' => [
                'id' => 'id',
                'name' => 'name',
                'code' => 'code',
            ],
            'translations' => [
                'id' => 'id',
                'model_type' => 'model_type',
                'model_id' => 'model_id',
                'language_id' => 'language_id',
                'field' => 'field',
                'translation' => 'translation',
            ],
        ],
    ],
]; 