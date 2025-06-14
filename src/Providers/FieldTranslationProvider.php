<?php

namespace FieldTranslations\Providers;

use Illuminate\Support\ServiceProvider;
use FieldTranslations\Services\TranslationService;
use FieldTranslations\Contracts\TranslationServiceInterface;

class FieldTranslationProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package config with the application's config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/field-translations.php',
            'field-translations'
        );

        // Register the translation service
        $this->app->singleton(TranslationServiceInterface::class, function ($app) {
            return new TranslationService($app['config']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish the config file
            $this->publishes([
                __DIR__ . '/../../config/field-translations.php' => config_path('field-translations.php'),
            ], 'field-translations-config');

            // Publish the database migrations
            $this->publishes([
                __DIR__ . '/../../database/migrations/' => database_path('migrations'),
            ], 'field-translations-migrations');

            // Publish seeders if they exist
            if (is_dir(__DIR__ . '/../../database/seeders')) {
                $this->publishes([
                    __DIR__ . '/../../database/seeders/' => database_path('seeders'),
                ], 'field-translations-seeders');
            }

            // Register commands
            $this->commands([
                // Add your package commands here
            ]);
        }

        // Load migrations from the package
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load translations if they exist
        if (is_dir(__DIR__ . '/../../resources/lang')) {
            $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'field-translations');
        }

        // Load views if they exist
        if (is_dir(__DIR__ . '/../../resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'field-translations');
        }
    }
}
