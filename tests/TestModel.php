<?php

namespace FieldTranslations\Tests;

use FieldTranslations\Traits\Translateable;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use Translateable;

    protected $table = 'test_models';
    protected $fillable = ['name', 'description'];
    protected $translatable = ['name', 'description'];
} 