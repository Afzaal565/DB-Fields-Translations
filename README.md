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


translations table
Stores translated fields for any model:

php
Copy
Edit
Schema::create('translations', function (Blueprint $table) {
    $table->id();
    $table->morphs('model'); // model_type & model_id
    $table->unsignedBigInteger('language_id');
    $table->string('field');
    $table->text('translation');
    $table->timestamps();
});
⚙️ Usage
Step 1: Add Traits to Your Model
php
Copy
Edit
use FieldTranslations\Traits\Translateable;

class Product extends Model
{
    use Translateable;

    protected $fillable = ['name', 'description'];
}
Step 2: Use LocaleTrait (Optional)
php
Copy
Edit
use FieldTranslations\Traits\LocaleTrait;

class ExampleController extends Controller
{
    use LocaleTrait;

    public function currentLanguage()
    {
        return $this->getLanguage(); // Based on App::getLocale()
    }
}
📝 Storing Translations
php
Copy
Edit
public function store(Request $request)
{
    $product = Product::create([
        'name' => $request->name,
        'description' => $request->description,
    ]);

    $language = Language::where('code', 'de')->first();

    $product->translationTo($request, $language, ['name', 'description']);
}
🔍 Retrieving Translations
php
Copy
Edit
$currentLangId = Language::where('code', app()->getLocale())->value('id');

$product = Product::find($id);

// Get translation for a specific field
$name = $product->tranlationByField($currentLangId, 'name') ?? $product->name;
🔎 Filtering Models Without a Translation
php
Copy
Edit
$languageId = Language::where('code', 'de')->value('id');

$productsWithoutGerman = Product::withoutTranslationForLanguage($languageId)->get();
🔧 Available Methods
Method	Description
translations()	MorphMany relationship
getTranslatedFields($languageId)	Get all translations for the language
getFirstTranslation($languageId)	Get the first translated value
tranlationByField($languageId, $field)	Get a specific field translation
translationTo($request, $language, $fields)	Save or update translation(s)
scopeWithoutTranslationForLanguage($languageId)	Scope to find records without translation

✅ Example JSON Output
json
Copy
Edit
{
  "id": 1,
  "name": "Shoes",
  "translations": [
    {
      "language_id": 2,
      "field": "name",
      "translation": "Schuhe"
    }
  ]
}
📄 License
This package is open-source and licensed under the MIT License.

yaml
Copy
Edit

---

### ✅ Tips
- Paste this into your `README.md` file.
- Adjust model and table names if your project uses different naming conventions.
- Add badges and links if you plan to publish to Packagist.

Let me know if you'd like a version with Vue.js frontend usage too!
