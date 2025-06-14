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
        $tableName = config('field_translation.table_names.languages', 'dbt_languages');
        $countryTable = config('field_translation.table_names.countries', 'dbt_countries');

        Schema::create($tableName, function (Blueprint $table) use ($countryTable) {
            $table->id();
            $table->string('name'); // 'name' column
            $table->string('slug')->unique(); // 'slug' column, set as unique
            $table->string('code', 10); // 'code' column, with a max length of 10 characters
            $table->foreignId('country_id')->constrained($countryTable)->onDelete('cascade'); // 'country_id' foreign key
            $table->boolean('rtl')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbt_languages');
    }
};
