<?php

namespace FieldTranslations;

use Illuminate\Support\ServiceProvider;
use FieldTranslations\Services\TranslationService;
use FieldTranslations\Contracts\TranslationServiceInterface;

class FieldTranslationsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/field-translations.php', 'field-translations'
        );

        $this->app->singleton(TranslationServiceInterface::class, function ($app) {
            return new TranslationService($app['config']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/field-translations.php' => config_path('field-translations.php'),
        ], 'field-translations-config');
    }
} 