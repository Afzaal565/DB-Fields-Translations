<?php

namespace FieldTranslations\Traits;

use FieldTranslations\Models\Language;
use FieldTranslations\Models\Translation;
use FieldTranslations\Services\TranslationService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait Translateable
{
    protected $translationService;

    protected static function bootTranslateable()
    {
        static::created(function ($model) {
            $model->initializeTranslationService();
        });
    }

    public function initializeTranslationService()
    {
        if (!$this->translationService) {
            $this->translationService = app(TranslationService::class);
            $this->translationService->setModel($this);
        }
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'model');
    }

    public function getTranslatedFields($languageId)
    {
        return $this->translations()->where('language_id', $languageId)->get();
    }

    public function getFirstTranslation($languageId)
    {
        return $this->translations()->where('language_id', $languageId)->first()?->translation;
    }

    public function tranlationByField($languageId, $field)
    {
        return $this->translations()->where(['language_id' => $languageId, 'field' => $field])->first()?->translation;
    }

    public function scopeWithoutTranslationForLanguage($query, $languageId)
    {
        return $query->whereDoesntHave('translations', function ($query) use ($languageId) {
            $query->where('language_id', $languageId);
        });
    }

    public function setTranslation($field, $language, $value)
    {
        $this->initializeTranslationService();
        
        Log::info('Translateable trait setTranslation', [
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

    public function getTranslation($field, $language)
    {
        $this->initializeTranslationService();
        return $this->translationService->getTranslation($field, $language);
    }

    public function translationTo(Request $request, Language $language, $fields)
    {
        $this->initializeTranslationService();
        
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if ($request->$field !== null) {
                    $this->setTranslation($field, $language->code, $request->$field);
                }
            }
        } else {
            if ($request->$fields !== null) {
                $this->setTranslation($fields, $language->code, $request->$fields);
            }
        }
    }
}
