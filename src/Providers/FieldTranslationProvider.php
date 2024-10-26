<?php


namespace FieldTranslations\Providers;

use Illuminate\Support\ServiceProvider;

class FieldTranslationProvider extends ServiceProvider
{
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            // Publish the config file
            $this->publishes([
                __DIR__ . '/../../config/field_translation.php' => config_path('field_translation.php'),
            ], 'config');

            // Publish the database migrations
            $this->publishes([
                __DIR__ . '/../../database/migrations/' => database_path('migrations'),
            ], 'migrations');

            // Publish seeders if any
            $this->publishes([
                __DIR__ . '/../../database/seeders/' => database_path('seeders'),
            ], 'seeders');
        }

        // Automatically load migrations from the package
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // // Optionally still allow publishing the migrations
        // if ($this->app->runningInConsole()) {
        //     $this->publishes([
        //         __DIR__ . '/../Migrations/' => database_path('migrations'),
        //     ], 'your-package-migrations');
        // }
    }


    public function register()
    {
        // Optionally, merge package config with the application's config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/field_translation.php',
            'field_translation'
        );
    }
}
