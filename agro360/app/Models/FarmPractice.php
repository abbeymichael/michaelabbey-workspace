<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmPractice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'specific_crop_or_animal_id',
        'farming_method_id',
        'farm_stage_id',
        'title',
        'content',
        'recommended_actions',
        'meta_data',
        'is_ai_generated',
        'ai_model_version',
        'is_active',
    ];

    protected $casts = [
        'recommended_actions' => 'array',
        'meta_data' => 'array',
        'is_ai_generated' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * A FarmPractice belongs to a SpecificCropOrAnimal.
     */
    public function specificCropOrAnimal(): BelongsTo
    {
        return $this->belongsTo(SpecificCropOrAnimal::class);
    }

    /**
     * A FarmPractice belongs to a FarmingMethod (nullable).
     */
    public function farmingMethod(): BelongsTo
    {
        return $this->belongsTo(FarmingMethod::class);
    }

    /**
     * A FarmPractice belongs to a FarmStage.
     */
    public function farmStage(): BelongsTo
    {
        return $this->belongsTo(FarmStage::class);
    }
}