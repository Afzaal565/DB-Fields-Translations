<?php

namespace FieldTranslations\Models;

use FieldTranslations\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $table;

    protected $fillable = ['model_id', 'model_type', 'language_id', 'field', 'translation'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('field_translation.table_names.translations', 'dbt_translations');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
