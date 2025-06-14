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
    public function setModel($model): void
    {
        $this->model = $model;
    }

    /**
     * Get translation for a specific field and language.
     *
     * @param string|array $field
     * @param string $language
     * @return string|null
     */
    public function getTranslation($field, string $language): ?string
    {
        try {
            Log::info("Getting translation for field: {$field}, language: {$language}", [
                'model' => get_class($this->model),
                'id' => $this->model->id
            ]);

            $translation = $this->model->translations()
                ->with('language')
                ->where('field', $field)
                ->whereHas('language', function ($query) use ($language) {
                    $query->where('code', $language);
                })
                ->first();

            Log::info("Translation result:", [
                'translation' => $translation ? $translation->translation : null
            ]);

            return $translation ? $translation->translation : null;
        } catch (\Exception $e) {
            Log::error("Error getting translation: " . $e->getMessage());
            return null;
        }
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
        try {
            Log::info("Setting translation for field: {$field}, language: {$language}, value: {$value}", [
                'model' => get_class($this->model),
                'id' => $this->model->id
            ]);

            $languageModel = Language::where('code', $language)->first();
            if (!$languageModel) {
                Log::error("Language not found: {$language}");
                return false;
            }

            $translation = $this->model->translations()
                ->where('field', $field)
                ->where('language_id', $languageModel->id)
                ->first();

            if ($translation) {
                $translation->translation = $value;
                $translation->save();
            } else {
                $this->model->translations()->create([
                    'field' => $field,
                    'translation' => $value,
                    'language_id' => $languageModel->id
                ]);
            }

            Log::info("Translation set successfully.");
            return true;
        } catch (\Exception $e) {
            Log::error("Error setting translation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all translations for a specific field.
     *
     * @param string|array $field
     * @return array
     */
    public function getTranslations($field): array
    {
        try {
            Log::info("Getting translations for field: {$field}", [
                'model' => get_class($this->model),
                'id' => $this->model->id
            ]);

            $translations = $this->model->translations()
                ->with('language')
                ->where('field', $field)
                ->get();

            $result = $translations->mapWithKeys(function ($translation) {
                return [$translation->language->code => $translation->translation];
            })->toArray();

            Log::info("Translations result:", [
                'translations' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error getting translations: " . $e->getMessage());
            return [];
        }
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
        try {
            return $this->model->translations()
                ->where('field', $field)
                ->whereHas('language', function ($query) use ($language) {
                    $query->where('code', $language);
                })
                ->exists();
        } catch (\Exception $e) {
            Log::error("Error checking translation: " . $e->getMessage());
            return false;
        }
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
            Log::info("Fetching all translations", [
                'field' => $field,
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id
            ]);

            $translations = $this->model->translations()
                ->with('language')
                ->where('field', $field)
                ->get();

            Log::info("Found translations", [
                'count' => $translations->count(),
                'translations' => $translations->toArray()
            ]);

            $result = $translations->mapWithKeys(function ($translation) {
                return [$translation->language->code => $translation->translation];
            })->toArray();

            Log::info("Processed result", $result);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error fetching all translations: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getTranslationsForLanguage(string $language): array
    {
        try {
            $translations = $this->model->translations()
                ->with('language')
                ->whereHas('language', function ($query) use ($language) {
                    $query->where('code', $language);
                })
                ->get();

            return $translations->mapWithKeys(function ($translation) {
                return [$translation->field => $translation->translation];
            })->toArray();
        } catch (\Exception $e) {
            Log::error("Error getting translations for language: " . $e->getMessage());
            return [];
        }
    }
} 