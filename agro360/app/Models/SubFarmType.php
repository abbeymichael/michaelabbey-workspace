<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubFarmType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'farm_type_id',
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
     * A SubFarmType belongs to a FarmType.
     */
    public function farmType(): BelongsTo
    {
        return $this->belongsTo(FarmType::class);
    }

    /**
     * A SubFarmType can have many SpecificCropOrAnimals.
     */
    public function specificCropOrAnimals(): HasMany
    {
        return $this->hasMany(SpecificCropOrAnimal::class);
    }

     public function farmingMethods(): BelongsToMany
    {

        return $this->belongsToMany(FarmingMethod::class, 'sub_farm_type_farming_method');
    }
}