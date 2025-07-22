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
        Schema::create('farm_types', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->string('name')->unique(); // e.g., "Crop Farming", "Livestock"
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable(); // For additional flexible data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_types');
    }
};
