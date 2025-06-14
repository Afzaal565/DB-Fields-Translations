<?php

namespace FieldTranslations\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TranslationServiceInterface
{
    /**
     * Set the model for translations
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model);

    /**
     * Get translation for a specific field and language.
     *
     * @param string|array $field
     * @param string $language
     * @return string|array|null
     */
    public function getTranslation($field, string $language);

    /**
     * Set translation for a specific field and language.
     *
     * @param string|array $field
     * @param string $language
     * @param string|array $value
     * @return bool
     */
    public function setTranslation($field, string $language, $value): bool;

    /**
     * Get all translations for specific field(s).
     *
     * @param string|array $field
     * @return array
     */
    public function getTranslations($field): array;

    /**
     * Check if translation exists for a specific field and language.
     *
     * @param string|array $field
     * @param string $language
     * @return bool
     */
    public function hasTranslation($field, string $language): bool;
} 