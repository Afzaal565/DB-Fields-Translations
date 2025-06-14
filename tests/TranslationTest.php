<?php

namespace FieldTranslations\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TranslationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });

        // Create languages table
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // Create translations table
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('language_id');
            $table->string('field');
            $table->text('translation');
            $table->timestamps();
        });

        // Insert test languages
        $this->app['db']->table('languages')->insert([
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Spanish', 'code' => 'es'],
        ]);
    }

    /** @test */
    public function it_can_set_and_get_translations()
    {
        $model = TestModel::create([
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);

        // Set translations
        $model->setTranslation('name', 'es', 'Producto de Prueba');
        $model->setTranslation('description', 'es', 'Descripción de Prueba');

        // Get translations
        $this->assertEquals('Producto de Prueba', $model->getTranslation('name', 'es'));
        $this->assertEquals('Descripción de Prueba', $model->getTranslation('description', 'es'));
    }

    /** @test */
    public function it_returns_null_for_non_translatable_fields()
    {
        $model = TestModel::create([
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);

        $this->assertNull($model->getTranslation('non_translatable_field', 'en'));
    }

    /** @test */
    public function it_can_check_if_translation_exists()
    {
        $model = TestModel::create([
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);

        $model->setTranslation('name', 'es', 'Producto de Prueba');

        $this->assertTrue($model->hasTranslation('name', 'es'));
        $this->assertFalse($model->hasTranslation('name', 'fr'));
    }

    /** @test */
    public function it_can_get_all_translations_for_a_field()
    {
        $model = TestModel::create([
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);

        $model->setTranslation('name', 'es', 'Producto de Prueba');
        $model->setTranslation('name', 'fr', 'Produit de Test');

        $translations = $model->getTranslations('name');

        $this->assertArrayHasKey('es', $translations);
        $this->assertArrayHasKey('fr', $translations);
        $this->assertEquals('Producto de Prueba', $translations['es']);
        $this->assertEquals('Produit de Test', $translations['fr']);
    }
} 