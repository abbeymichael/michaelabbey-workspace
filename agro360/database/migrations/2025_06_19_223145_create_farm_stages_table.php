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
        Schema::create('farm_stages', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->string('name')->unique(); // e.g., "Land Preparation", "Planting", "Weeding", "Pest Control", "Harvest"
            $table->integer('order')->default(0); // For sequencing stages
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable(); // e.g., {"applies_to_crop": true, "applies_to_animal": false}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_stages');
    }
};
