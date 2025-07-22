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
        Schema::create('sub_farm_types', function (Blueprint $table) {
              $table->uuid('id')->primary(); // UUID as primary key
            $table->foreignUuid('farm_type_id')->constrained('farm_types')->onDelete('cascade'); // Foreign key to farm_types
            $table->string('name'); // e.g., "Grains", "Poultry"
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable(); // For additional flexible data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['farm_type_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_farm_types');
    }
};
