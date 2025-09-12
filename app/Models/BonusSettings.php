<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_value',
        'direct_bonus_percent',
        'pair_bonus_amount',
        'balancer_ratio',
        'matching_bonus_percent',
    ];

    protected $casts = [
        'package_value' => 'decimal:2',
        'direct_bonus_percent' => 'decimal:2',
        'pair_bonus_amount' => 'decimal:2',
        'matching_bonus_percent' => 'decimal:2',
    ];
}
