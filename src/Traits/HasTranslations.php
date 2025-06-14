<?php

namespace FieldTranslations\Traits;

use FieldTranslations\Contracts\TranslationServiceInterface;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Get translation for a specific field and language.
     *
     * @param string $field
     * @param string|null $language
     * @return string|null
     */
    public function getTranslation(string $field, ?string $language = null): ?string
    {
        if (!in_array($field, $this->translatable)) {
            return null;
        }

        $language = $language ?? App::getLocale();
        return app(TranslationServiceInterface::class)->getTranslation($field, $language);
    }

    /**
     * Set translation for a specific field and language.
     *
     * @param string $field
     * @param string $language
     * @param string $value
     * @return bool
     */
    public function setTranslation(string $field, string $language, string $value): bool
    {
        if (!in_array($field, $this->translatable)) {
            return false;
        }

        return app(TranslationServiceInterface::class)->setTranslation($field, $language, $value);
    }

    /**
     * Get all translations for a specific field.
     *
     * @param string $field
     * @return array
     */
    public function getTranslations(string $field): array
    {
        if (!in_array($field, $this->translatable)) {
            return [];
        }

        return app(TranslationServiceInterface::class)->getTranslations($field);
    }

    /**
     * Check if translation exists for a specific field and language.
     *
     * @param string $field
     * @param string $language
     * @return bool
     */
    public function hasTranslation(string $field, string $language): bool
    {
        if (!in_array($field, $this->translatable)) {
            return false;
        }

        return app(TranslationServiceInterface::class)->hasTranslation($field, $language);
    }
} 