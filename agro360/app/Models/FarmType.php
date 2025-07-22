<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Important for UUIDs
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmType extends Model
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

    /**
     * A FarmType can have many SubFarmTypes.
     */
    public function subFarmTypes(): HasMany
    {
        return $this->hasMany(SubFarmType::class);
    }
}