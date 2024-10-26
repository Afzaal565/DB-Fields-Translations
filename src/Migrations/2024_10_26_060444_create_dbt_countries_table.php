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
        Schema::create('dbt_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'name' column
            $table->string('slug')->unique(); // 'slug' column, set as unique
            $table->string('flag')->nullable(); // 'flag' column, allowing null values
            $table->string('time_zone'); // 'time_zone' column
            $table->string('currency_code'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbt_countries');
    }
};
