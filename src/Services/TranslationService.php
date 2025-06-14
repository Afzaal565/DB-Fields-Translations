<?php

namespace FieldTranslations\Services;

use FieldTranslations\Contracts\TranslationServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;

class TranslationService implements TranslationServiceInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Create a new translation service instance.
     *
     * @param ConfigRepository $config
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config->get('field-translations', [
            'cache' => [
                'enabled' => false,
                'ttl' => 60 * 24,
                'prefix' => 'field_translations_'
            ]
        ]);
    }

    /**
     * Set the model for translations
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get translation for a specific field and language.
     *
     * @param string $field
     * @param string $language
     * @return string|null
     */
    public function getTranslation(string $field, string $language): ?string
    {
        $cacheKey = $this->getCacheKey($field, $language);

        if (isset($this->config['cache']['enabled']) && $this->config['cache']['enabled']) {
            return Cache::remember($cacheKey, $this->config['cache']['ttl'] ?? 60 * 24, function () use ($field, $language) {
                return $this->fetchTranslation($field, $language);
            });
        }

        return $this->fetchTranslation($field, $language);
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
        $cacheKey = $this->getCacheKey($field, $language);

        // Clear cache if enabled
        if (isset($this->config['cache']['enabled']) && $this->config['cache']['enabled']) {
            Cache::forget($cacheKey);
        }

        // Store translation in database
        return $this->storeTranslation($field, $language, $value);
    }

    /**
     * Get all translations for a specific field.
     *
     * @param string $field
     * @return array
     */
    public function getTranslations(string $field): array
    {
        $cacheKey = $this->getCacheKey($field, 'all');

        if ($this->config['cache']['enabled']) {
            return Cache::remember($cacheKey, $this->config['cache']['ttl'], function () use ($field) {
                return $this->fetchAllTranslations($field);
            });
        }

        return $this->fetchAllTranslations($field);
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
        return $this->getTranslation($field, $language) !== null;
    }

    /**
     * Get cache key for a field and language.
     *
     * @param string $field
     * @param string $language
     * @return string
     */
    protected function getCacheKey(string $field, string $language): string
    {
        return $this->config['cache']['prefix'] . $field . '_' . $language;
    }

    /**
     * Fetch translation from database.
     *
     * @param string $field
     * @param string $language
     * @return string|null
     */
    protected function fetchTranslation(string $field, string $language): ?string
    {
        // Implement database query to fetch translation
        // This is a placeholder - implement actual database logic
        return null;
    }

    /**
     * Store translation in database.
     *
     * @param string $field
     * @param string $language
     * @param string $value
     * @return bool
     */
    protected function storeTranslation(string $field, string $language, string $value): bool
    {
        try {
            $languageModel = DB::table($this->config['database']['languages_table'])
                ->where('code', $language)
                ->first();

            if (!$languageModel) {
                return false;
            }

            $translation = DB::table($this->config['database']['translations_table'])
                ->updateOrInsert(
                    [
                        'model_type' => get_class($this->model),
                        'model_id' => $this->model->id,
                        'language_id' => $languageModel->id,
                        'field' => $field,
                    ],
                    [
                        'translation' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

            return true;
        } catch (\Exception $e) {
            Log::error('Translation storage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all translations for a field.
     *
     * @param string $field
     * @return array
     */
    protected function fetchAllTranslations(string $field): array
    {
        // Implement database query to fetch all translations
        // This is a placeholder - implement actual database logic
        return [];
    }
} 