<?php

namespace FieldTranslations\Traits;

use FieldTranslations\Models\Language;
use FieldTranslations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;

trait Translateable
{

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

    public function translationTo(Request $request, Language $language, $fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if ($request->$field !== null) {
                    $this->translations()->updateOrCreate(
                        ['language_id' => $language->id, 'field' => $field],
                        ['translation' => $request->$field]
                    );
                }
            }
        } else {
            if ($request->$fields !== null) {
                $this->translations()->updateOrCreate(
                    ['language_id' => $language->id, 'field' => $fields],
                    ['translation' => $request->$fields]
                );
            }
        }
    }
}
