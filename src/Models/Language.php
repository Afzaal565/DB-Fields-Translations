<?php

namespace FieldTranslations\Models;

use FieldTranslations\Models\Country;
use FieldTranslations\Models\Translation;
use FieldTranslations\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use Sluggable;

    protected $table = 'dbt_languages';
    protected $fillable = ['name', 'slug', 'code', 'country_id', 'rtl'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('field-translations.database.languages_table', $this->table);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withDefault();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, config('field-translations.database.columns.translations.language_id'));
    }
}
