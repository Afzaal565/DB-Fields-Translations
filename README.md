# DB Fields Translations

A Laravel package for managing field translations in your database tables.

## Installation

You can install the package via composer:

```bash
composer require afzaal565/field-translations
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="FieldTranslations\Providers\FieldTranslationProvider"
```

This will create a `field-translations.php` config file in your config directory.

## Usage

### 1. Add the Trait to Your Model

```php
use FieldTranslations\Traits\HasTranslations;

class YourModel extends Model
{
    use HasTranslations;
    
    protected $translatable = [
        'title',
        'description',
        // Add your translatable fields here
    ];
}
```

### 2. Create Translation Tables

Run the migrations:

```bash
php artisan migrate
```

### 3. Working with Translations

```php
// Set translations
$model->setTranslation('title', 'en', 'English Title');
$model->setTranslation('title', 'es', 'Spanish Title');

// Get translations
$model->getTranslation('title', 'en'); // Returns: English Title
$model->getTranslation('title', 'es'); // Returns: Spanish Title

// Get all translations for a field
$model->getTranslations('title'); // Returns array of all translations

// Check if translation exists
$model->hasTranslation('title', 'en'); // Returns boolean
```

## Features

- Easy to integrate with any Laravel model
- Support for multiple languages
- Automatic translation table creation
- Flexible configuration options
- Cache support for better performance

## Configuration Options

In your `config/field-translations.php` file, you can configure:

- Default language
- Available languages
- Cache settings
- Table naming conventions

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email afzaalhussain565@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
