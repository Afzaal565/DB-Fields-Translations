<?php

namespace FieldTrans\Traits;

use Illuminate\Support\Facades\App;
use FieldTrans\Models\Language;

trait LocaleTrait
{
    public function getLanguage()
    {
        $locale = App::getLocale();
        return Language::where('code', $locale)->first();
    }
}
