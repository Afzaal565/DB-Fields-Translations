# 🈺 DB Fields Translations

A lightweight Laravel package to handle multi-language translations for model fields using polymorphic relationships.

---

## 📦 Installation

Install the package via Composer:

```bash
composer require afzaal565/db-fields-translations


## 📦 Publish the config and migration files

php artisan vendor:publish --provider="Afzaal565\DBFieldsTranslations\DBFieldsTranslationsServiceProvider"
php artisan migrate


🧬 Database Structure
languages table
Stores supported language codes:

Schema::create('languages', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique(); // e.g., "en", "de"
    $table->timestamps();
});
