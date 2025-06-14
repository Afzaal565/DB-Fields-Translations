<?php

namespace FieldTranslations\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use FieldTranslations\Tests\TestModel;
use FieldTranslations\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FieldTranslations\Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    protected TestModel $model;

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

        // Create a test model instance
        $this->model = TestModel::create([
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);

        // Set translations for the test model
        $this->model->setTranslation('name', 'en', 'English Name');
        $this->model->setTranslation('name', 'es', 'Producto de Prueba');
        $this->model->setTranslation('name', 'fr', 'Nom en Français');
        $this->model->setTranslation('description', 'en', 'English Description');
        $this->model->setTranslation('description', 'es', 'Descripción de Prueba');
    }

    /** @test */
    public function it_can_set_and_get_translations()
    {
        $this->model->setTranslation('name', 'en', 'English Name');
        $this->assertEquals('English Name', $this->model->getTranslation('name', 'en'));
    }

    /** @test */
    public function it_returns_null_for_non_translatable_fields()
    {
        $this->assertNull($this->model->getTranslation('non_translatable_field', 'en'));
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