<?php

namespace FieldTranslations\Traits;

use FieldTranslations\Models\Language;
use FieldTranslations\Models\Translation;
use FieldTranslations\Services\TranslationService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;

trait Translateable
{
    protected $translationService;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->translationService = app(TranslationService::class);
        $this->translationService->setModel($this);
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
        return $this->translationService->setTranslation($field, $language, $value);
    }

    public function getTranslation($field, $language)
    {
        return $this->translationService->getTranslation($field, $language);
    }

    public function translationTo(Request $request, Language $language, $fields)
    {
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
