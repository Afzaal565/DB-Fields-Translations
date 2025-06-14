<?php

namespace FieldTranslations\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use FieldTranslations\Providers\FieldTranslationProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FieldTranslationProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup package config
        $app['config']->set('field-translations', [
            'default_language' => 'en',
            'languages' => [
                'en' => 'English',
                'es' => 'Spanish',
            ],
            'cache' => [
                'enabled' => false,
                'ttl' => 60 * 24,
                'prefix' => 'field_translations_',
            ],
        ]);

        // Set the environment variables for the languages and translations tables
        $app['config']->set('field-translations.database.languages_table', 'languages');
        $app['config']->set('field-translations.database.translations_table', 'translations');
    }
} 