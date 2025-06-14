<?php

namespace FieldTranslations\Traits;

use FieldTranslations\Contracts\TranslationServiceInterface;
use FieldTranslations\Models\Language;
use FieldTranslations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use FieldTranslations\Services\TranslationService;

trait Translatable
{
    protected ?TranslationServiceInterface $translationService = null;

    protected static function bootTranslatable()
    {
        static::created(function ($model) {
            $model->initializeTranslationService();
        });

        static::deleting(function ($model) {
            $model->translations()->delete();
        });
    }

    protected function initializeTranslationService()
    {
        if (!$this->translationService) {
            $this->translationService = app(TranslationServiceInterface::class);
            $this->translationService->setModel($this);
        }
    }

    /**
     * Get the translations relationship.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'model');
    }

    /**
     * Get translation for a specific field and language.
     * If no language is provided, uses the current application locale.
     */
    public function getTranslation(string $field, string $languageCode = null): ?string
    {
        try {
            Log::info("Getting translation for field: {$field}, language: {$languageCode}", [
                'model' => get_class($this),
                'id' => $this->id
            ]);

            $this->initializeTranslationService();
            $languageCode = $languageCode ?? App::getLocale();
            
            $result = $this->translationService->getTranslation($field, $languageCode);
            
            Log::info("Translation result:", [
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error getting translation: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get all translations for specific field(s).
     */
    public function getTranslations(string $field): array
    {
        try {
            Log::info("Getting translations for field: {$field}", [
                'model' => get_class($this),
                'id' => $this->id
            ]);

            $this->initializeTranslationService();
            $result = $this->translationService->getTranslations($field);
            
            Log::info("Translations result:", [
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error getting translations: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Set translation for a specific field and language.
     */
    public function setTranslation(string $field, string $languageCode, string $value): bool
    {
        try {
            Log::info("Setting translation", [
                'field' => $field,
                'language' => $languageCode,
                'value' => $value,
                'model' => get_class($this),
                'id' => $this->id
            ]);

            $this->initializeTranslationService();
            $result = $this->translationService->setTranslation($field, $languageCode, $value);
            
            Log::info("Set translation result:", [
                'success' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error setting translation: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get all translations for a specific language.
     */
    public function getTranslationsForLanguage(string $languageCode): array
    {
        try {
            Log::info("Getting translations for language: {$languageCode}", [
                'model' => get_class($this),
                'id' => $this->id
            ]);

            $this->initializeTranslationService();
            $result = $this->translationService->getTranslationsForLanguage($languageCode);
            
            Log::info("Language translations result:", [
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Error getting translations for language: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get all translations for all languages.
     */
    public function getAllTranslations(): array
    {
        $translations = [];
        foreach ($this->translatable as $field) {
            $translations[$field] = $this->getTranslations($field);
        }
        return $translations;
    }

    /**
     * Set translations from request data.
     */
    public function setTranslationsFromRequest(Request $request, Language $language, array $fields = null): void
    {
        $fields = $fields ?? $this->translatable;
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->setTranslation($field, $language->code, $request->input($field));
            }
        }
    }

    /**
     * Check if translation exists for a specific field and language.
     */
    public function hasTranslation(string $field, string $language): bool
    {
        if (!in_array($field, $this->translatable)) {
            return false;
        }

        $this->initializeTranslationService();
        return $this->translationService->hasTranslation($field, $language);
    }

    /**
     * Scope to get models without translation for a specific language.
     */
    public function scopeWithoutTranslationForLanguage($query, string $language)
    {
        return $query->whereDoesntHave('translations', function ($query) use ($language) {
            $query->whereHas('language', function ($q) use ($language) {
                $q->where('code', $language);
            });
        });
    }
} 