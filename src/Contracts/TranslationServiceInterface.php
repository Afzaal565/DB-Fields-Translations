<?php

namespace FieldTranslations\Contracts;

interface TranslationServiceInterface
{
    /**
     * Get translation for a specific field and language.
     *
     * @param string $field
     * @param string $language
     * @return string|null
     */
    public function getTranslation(string $field, string $language): ?string;

    /**
     * Set translation for a specific field and language.
     *
     * @param string $field
     * @param string $language
     * @param string $value
     * @return bool
     */
    public function setTranslation(string $field, string $language, string $value): bool;

    /**
     * Get all translations for a specific field.
     *
     * @param string $field
     * @return array
     */
    public function getTranslations(string $field): array;

    /**
     * Check if translation exists for a specific field and language.
     *
     * @param string $field
     * @param string $language
     * @return bool
     */
    public function hasTranslation(string $field, string $language): bool;
} 