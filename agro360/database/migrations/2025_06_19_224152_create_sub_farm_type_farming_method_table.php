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
        Schema::create('sub_farm_type_farming_method', function (Blueprint $table) {
               // Using foreignUuid for consistency with your UUID setup
            $table->foreignUuid('sub_farm_type_id')->constrained('sub_farm_types')->onDelete('cascade');
            $table->foreignUuid('farming_method_id')->constrained('farming_methods')->onDelete('cascade');

            // Define a composite primary key to ensure uniqueness for each combination
            $table->primary(['sub_farm_type_id', 'farming_method_id'], 'sub_farm_method_primary');

            $table->timestamps(); // Optional: timestamps for when a method was associated with a sub-type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_farm_type_farming_method');
    }
};
