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
        Schema::create('farm_practices', function (Blueprint $table) {
                 $table->uuid('id')->primary(); // UUID as primary key

            // Foreign keys to define the context of the practice
            $table->foreignUuid('specific_crop_or_animal_id')->constrained('specific_crop_or_animals')->onDelete('cascade');
            $table->foreignUuid('farming_method_id')->nullable()->constrained('farming_methods')->onDelete('set null'); // A practice might apply regardless of method, or be specific
            $table->foreignUuid('farm_stage_id')->constrained('farm_stages')->onDelete('cascade');
            $table->string('title'); // E.g., "Optimal Maize Planting Depth"
            $table->longText('content'); // The detailed AI-generated advice/practice
            $table->json('recommended_actions')->nullable(); // JSON array of actionable steps, e.g., ["Use spacing of X", "Apply Y fertilizer"]
            $table->json('meta_data')->nullable(); // e.g., {"seasonal_relevance": "major_rainy_season", "estimated_labor_hours": 8}
            $table->boolean('is_ai_generated')->default(false); // Track if content came from AI
            $table->string('ai_model_version')->nullable(); // To track which AI model generated it
            $table->boolean('is_active')->default(true);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_practices');
    }
};
