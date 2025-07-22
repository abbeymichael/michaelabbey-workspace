<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FarmingMethod extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'meta_data',
        'is_active',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'is_active' => 'boolean',
    ];

     public function subFarmTypes(): BelongsToMany
    {
        return $this->belongsToMany(SubFarmType::class, 'sub_farm_type_farming_method');
    }
}