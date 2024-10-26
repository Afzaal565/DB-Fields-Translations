<?php

return [

    'models' => [

        /*
         * The model you want to use for country for language.
         */

        'country' => FieldTranslations\Models\Country::class,

        /*
         * The model you want to use for language.
         */

        'language' => FieldTranslations\Models\Language::class,

        /*
         * The model you want to use for translateable traits
         */

        'translation' => FieldTranslations\Models\Language::class,

    ],

    'table_names' => [

        /*
         * When using the trait from this package, we need to know which
         */
        'countries' => 'dbt_countries',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         */
        'languages' => 'dbt_languages',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         */
        'translations' => 'dbt_translations',

    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null, //default 'role_id',
        'permission_pivot_key' => null, //default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */

        'model_morph_key' => 'model_id',

        /*
         * Change this if you want to use the teams feature and your related model's
         * foreign key is other than `team_id`.
         */

        'team_foreign_key' => 'team_id',
    ]
];
