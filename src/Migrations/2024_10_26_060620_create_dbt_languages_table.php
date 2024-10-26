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
        Schema::create('dbt_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'name' column
            $table->string('slug')->unique(); // 'slug' column, set as unique
            $table->string('code', 10); // 'code' column, with a max length of 10 characters
            $table->foreignId('country_id')->constrained('dbt_countries')->onDelete('cascade'); // 'country_id' foreign key
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
