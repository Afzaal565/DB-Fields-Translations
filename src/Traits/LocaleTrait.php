<?php

namespace FieldTranslations\Traits;

use Illuminate\Support\Facades\App;
use FieldTranslations\Models\Language;

trait LocaleTrait
{
    public function getLanguage()
    {
        $locale = App::getLocale();
        return Language::where('code', $locale)->first();
    }
}
