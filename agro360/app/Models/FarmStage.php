<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmStage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'order',
        'description',
        'meta_data',
        'is_active',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * A FarmStage can have many FarmPractices.
     */
    public function farmPractices(): HasMany
    {
        return $this->hasMany(FarmPractice::class);
    }
}