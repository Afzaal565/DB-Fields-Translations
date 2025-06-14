<?php

namespace FieldTranslations\Traits;

use FieldTranslations\Contracts\TranslationServiceInterface;
use FieldTranslations\Models\Language;
use FieldTranslations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

trait Translatable
{
    protected $translationService;

    protected static function bootTranslatable()
    {
        static::created(function ($model) {
            $model->initializeTranslationService();
        });
    }

    public function initializeTranslationService()
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
    public function getTranslation(string $field, ?string $language = null): ?string
    {
        if (!in_array($field, $this->translatable)) {
            return null;
        }

        $this->initializeTranslationService();
        $language = $language ?? App::getLocale();
        
        return $this->translationService->getTranslation($field, $language);
    }

    /**
     * Get all translations for a specific field.
     */
    public function getTranslations(string $field): array
    {
        if (!in_array($field, $this->translatable)) {
            return [];
        }

        $this->initializeTranslationService();
        return $this->translationService->getTranslations($field);
    }

    /**
     * Set translation for a specific field and language.
     */
    public function setTranslation(string $field, string $language, string $value): bool
    {
        if (!in_array($field, $this->translatable)) {
            return false;
        }

        $this->initializeTranslationService();
        
        Log::info('Setting translation', [
            'field' => $field,
            'language' => $language,
            'value' => $value,
            'model_id' => $this->id,
            'model_class' => get_class($this)
        ]);

        return $this->translationService->setTranslation(
            $field, 
            $language, 
            $value,
            get_class($this),
            $this->id
        );
    }

    /**
     * Get all translations for a specific language.
     */
    public function getTranslationsForLanguage(string $language): array
    {
        $translations = [];
        foreach ($this->translatable as $field) {
            $translations[$field] = $this->getTranslation($field, $language);
        }
        return $translations;
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