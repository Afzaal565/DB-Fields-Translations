<?php

namespace FieldTrans\Models;

use FieldTrans\Models\Country;
use FieldTrans\Models\Translation;
use FieldTrans\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use Sluggable;

    protected $table = 'dbt_languages';

    protected $fillable = ['name', 'slug', 'code', 'country_id', 'rtl'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withDefault();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'language_id');
    }


}
