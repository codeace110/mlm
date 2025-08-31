<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'percentage',
        'min_amount',
        'max_amount',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}