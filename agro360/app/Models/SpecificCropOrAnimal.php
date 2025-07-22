<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecificCropOrAnimal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sub_farm_type_id',
        'name',
        'description',
        'meta_data',
        'is_active',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * A SpecificCropOrAnimal belongs to a SubFarmType.
     */
    public function subFarmType(): BelongsTo
    {
        return $this->belongsTo(SubFarmType::class);
    }

    /**
     * A SpecificCropOrAnimal can have many FarmPractices.
     */
    public function farmPractices(): HasMany
    {
        return $this->hasMany(FarmPractice::class);
    }
}