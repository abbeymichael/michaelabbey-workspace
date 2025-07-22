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
        Schema::create('specific_crop_or_animals', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key
            $table->foreignUuid('sub_farm_type_id')->constrained('sub_farm_types')->onDelete('cascade'); // Foreign key to sub_farm_types
            $table->string('name'); // e.g., "Maize", "Layers", "Tilapia"
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable(); // e.g., {"is_crop": true, "growth_period_days": 120, "ghana_regions_suitable": ["Northern", "Brong-Ahafo"]}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specific_crop_or_animals');
    }
};
