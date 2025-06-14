<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('field_translation.table_names.translations', 'dbt_translations');
        $languageTable = config('field_translation.table_names.languages', 'dbt_languages');

        Schema::create($tableName, function (Blueprint $table) use ($languageTable) {
            $table->bigIncrements('id');
            $table->foreignId('language_id')->constrained($languageTable)->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], $languageTable.'_model_id_model_type_index');
            $table->string('field');
            $table->text('translation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbt_translations');
    }
};
