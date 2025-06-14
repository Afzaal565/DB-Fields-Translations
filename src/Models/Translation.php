<?php

namespace FieldTranslations\Models;

use FieldTranslations\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $table = 'dbt_translations';
    protected $fillable = [
        'model_id',
        'model_type',
        'language_id',
        'field',
        'translation'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('field-translations.database.translations_table', $this->table);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, config('field-translations.database.columns.translations.language_id'));
    }

    protected function getModelTypeColumn(): string
    {
        return config('field-translations.database.columns.translations.model_type');
    }

    protected function getModelIdColumn(): string
    {
        return config('field-translations.database.columns.translations.model_id');
    }

    protected function getFieldColumn(): string
    {
        return config('field-translations.database.columns.translations.field');
    }

    protected function getTranslationColumn(): string
    {
        return config('field-translations.database.columns.translations.translation');
    }
}
