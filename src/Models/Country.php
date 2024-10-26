<?php

namespace FieldTranslations\Models;

use FieldTranslations\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use Sluggable;

    protected $table = 'dbt_countries';

    protected $fillable = ['name', 'slug', 'flag', 'time_zone', 'currency_code'];



}
