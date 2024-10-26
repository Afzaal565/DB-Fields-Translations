<?php

namespace FieldTrans\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable()
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->name); // Generate slug from the name
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->name); // Generate slug from the name
        });
    }
}
