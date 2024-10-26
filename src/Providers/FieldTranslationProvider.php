<?php


namespace FieldTranslations\Providers;

use Illuminate\Support\ServiceProvider;

class FieldTranslationProvider extends ServiceProvider
{
    public function boot()
    {
        // Automatically load migrations from the package
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');

        // Optionally still allow publishing the migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Migrations/' => database_path('migrations'),
            ], 'your-package-migrations');
        }
    }
}
