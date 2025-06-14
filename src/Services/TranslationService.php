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
     * @param string|array $field
     * @param string $language
     * @return string|null
     */
    public function getTranslation($field, string $language)
    {
        $fields = is_array($field) ? $field : [$field];
        $results = [];
        
        foreach ($fields as $f) {
            $cacheKey = $this->getCacheKey($f, $language);
            $results[$f] = isset($this->config['cache']['enabled']) && $this->config['cache']['enabled']
                ? Cache::remember($cacheKey, $this->config['cache']['ttl'] ?? 60 * 24, function () use ($f, $language) {
                    return $this->fetchTranslation($f, $language);
                })
                : $this->fetchTranslation($f, $language);
        }

        return is_array($field) ? $results : ($results[$field] ?? null);
    }

    /**
     * Set translation for a specific field and language.
     *
     * @param string|array $field
     * @param string $language
     * @param string|array $value
     * @param string|null $modelType
     * @param int|null $modelId
     * @return bool
     */
    public function setTranslation($field, string $language, $value, ?string $modelType = null, ?int $modelId = null): bool
    {
        $fields = is_array($field) ? $field : [$field];
        $values = is_array($value) ? $value : [$field => $value];
        $success = true;

        foreach ($fields as $f) {
            $cacheKey = $this->getCacheKey($f, $language);
            if (isset($this->config['cache']['enabled']) && $this->config['cache']['enabled']) {
                Cache::forget($cacheKey);
            }
            $success = $success && $this->storeTranslation($f, $language, $values[$f], $modelType, $modelId);
        }

        return $success;
    }

    /**
     * Get all translations for a specific field.
     *
     * @param string|array $field
     * @return array
     */
    public function getTranslations($field): array
    {
        $fields = is_array($field) ? $field : [$field];
        $results = [];

        foreach ($fields as $f) {
            $cacheKey = $this->getCacheKey($f, 'all');
            $results[$f] = $this->config['cache']['enabled']
                ? Cache::remember($cacheKey, $this->config['cache']['ttl'], function () use ($f) {
                    return $this->fetchAllTranslations($f);
                })
                : $this->fetchAllTranslations($f);
        }

        return is_array($field) ? $results : ($results[$field] ?? []);
    }

    /**
     * Check if translation exists for a specific field and language.
     *
     * @param string|array $field
     * @param string $language
     * @return bool
     */
    public function hasTranslation($field, string $language): bool
    {
        $fields = is_array($field) ? $field : [$field];
        foreach ($fields as $f) {
            if ($this->getTranslation($f, $language) === null) {
                return false;
            }
        }
        return true;
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
        try {
            $languageModel = DB::table($this->config['database']['languages_table'])
                ->where('code', $language)
                ->first();

            if (!$languageModel) {
                Log::error("Language not found: {$language}");
                return null;
            }

            // Use provided model type/id or fall back to the instance model
            $modelType = isset($this->model) ? get_class($this->model) : null;
            $modelId = isset($this->model) ? $this->model->id : null;

            if (!$modelType || !$modelId) {
                Log::error("Model type or ID missing for translation fetch. Model type: {$modelType}, Model ID: {$modelId}");
                return null;
            }

            $translation = DB::table($this->config['database']['translations_table'])
                ->where([
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'language_id' => $languageModel->id,
                    'field' => $field,
                ])
                ->first();

            Log::info("Fetched translation for field {$field} in language {$language}: " . ($translation ? $translation->translation : 'null'));
            return $translation ? $translation->translation : null;
        } catch (\Exception $e) {
            Log::error('Translation fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store translation in database.
     *
     * @param string $field
     * @param string $language
     * @param string $value
     * @param string|null $modelType
     * @param int|null $modelId
     * @return bool
     */
    protected function storeTranslation(string $field, string $language, string $value, ?string $modelType = null, ?int $modelId = null): bool
    {
        try {
            $languageModel = DB::table($this->config['database']['languages_table'])
                ->where('code', $language)
                ->first();

            if (!$languageModel) {
                return false;
            }

            // Use provided model type/id or fall back to the instance model
            $modelType = $modelType ?? (isset($this->model) ? get_class($this->model) : null);
            $modelId = $modelId ?? (isset($this->model) ? $this->model->id : null);

            if (!$modelType || !$modelId) {
                Log::error('Translation storage error: Model type or ID is missing');
                return false;
            }

            $translation = DB::table($this->config['database']['translations_table'])
                ->updateOrInsert(
                    [
                        'model_type' => $modelType,
                        'model_id' => $modelId,
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
        try {
            // Use provided model type/id or fall back to the instance model
            $modelType = isset($this->model) ? get_class($this->model) : null;
            $modelId = isset($this->model) ? $this->model->id : null;

            if (!$modelType || !$modelId) {
                Log::error("Model type or ID missing for fetching all translations. Model type: {$modelType}, Model ID: {$modelId}");
                return [];
            }

            $translations = DB::table($this->config['database']['translations_table'])
                ->join($this->config['database']['languages_table'], 'languages.id', '=', 'translations.language_id')
                ->where([
                    'translations.model_type' => $modelType,
                    'translations.model_id' => $modelId,
                    'translations.field' => $field,
                ])
                ->select('languages.code as language', 'translations.translation')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->language => $item->translation];
                })
                ->toArray();

            Log::info("Fetched all translations for field {$field}: " . json_encode($translations));
            return $translations;
        } catch (\Exception $e) {
            Log::error('Error fetching all translations: ' . $e->getMessage());
            return [];
        }
    }
} 