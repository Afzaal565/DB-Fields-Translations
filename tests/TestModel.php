<?php

namespace FieldTranslations\Tests;

use Illuminate\Database\Eloquent\Model;
use FieldTranslations\Traits\HasTranslations;

class TestModel extends Model
{
    use HasTranslations;

    protected $table = 'test_models';
    protected $fillable = ['name', 'description'];
    protected $translatable = ['name', 'description'];
} 