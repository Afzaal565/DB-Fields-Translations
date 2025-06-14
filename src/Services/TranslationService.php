<?php

namespace FieldTranslations\Services;

use FieldTranslations\Contracts\TranslationServiceInterface;
use FieldTranslations\Models\Language;
use FieldTranslations\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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
            $languageModel = Language::where('code', $language)->first();

            if (!$languageModel) {
                Log::error("Language not found: {$language}");
                return null;
            }

            $modelType = isset($this->model) ? get_class($this->model) : null;
            $modelId = isset($this->model) ? $this->model->id : null;

            if (!$modelType || !$modelId) {
                Log::error("Model type or ID missing for translation fetch. Model type: {$modelType}, Model ID: {$modelId}");
                return null;
            }

            $translation = Translation::where([
                (new Translation)->getModelTypeColumn() => $modelType,
                (new Translation)->getModelIdColumn() => $modelId,
                (new Translation)->getFieldColumn() => $field,
            ])->whereHas('language', function ($query) use ($language) {
                $query->where('code', $language);
            })->first();

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
            $languageModel = Language::where('code', $language)->first();

            if (!$languageModel) {
                return false;
            }

            $modelType = $modelType ?? (isset($this->model) ? get_class($this->model) : null);
            $modelId = $modelId ?? (isset($this->model) ? $this->model->id : null);

            if (!$modelType || !$modelId) {
                Log::error('Translation storage error: Model type or ID is missing');
                return false;
            }

            $translation = new Translation();
            Translation::updateOrCreate(
                [
                    $translation->getModelTypeColumn() => $modelType,
                    $translation->getModelIdColumn() => $modelId,
                    $translation->getFieldColumn() => $field,
                    config('field-translations.database.columns.translations.language_id') => $languageModel->id,
                ],
                [
                    $translation->getTranslationColumn() => $value,
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
            $modelType = isset($this->model) ? get_class($this->model) : null;
            $modelId = isset($this->model) ? $this->model->id : null;

            if (!$modelType || !$modelId) {
                Log::error("Model type or ID missing for fetching all translations. Model type: {$modelType}, Model ID: {$modelId}");
                return [];
            }

            $translation = new Translation();
            $translations = Translation::with('language')
                ->where([
                    $translation->getModelTypeColumn() => $modelType,
                    $translation->getModelIdColumn() => $modelId,
                    $translation->getFieldColumn() => $field,
                ])
                ->get()
                ->mapWithKeys(function ($translation) {
                    return [$translation->language->code => $translation->translation];
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