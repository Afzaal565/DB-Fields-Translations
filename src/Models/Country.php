<?php

namespace FieldTranslations\Models;

use FieldTranslations\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use Sluggable;

    protected $table = 'dbt_countries';

    protected $fillable = ['name', 'slug', 'code', 'flag'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('field-translations.database.countries_table', $this->table);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }
}
