<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'is_product',
        'reward_type',
        'level_index',
        'pair_count',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_product' => 'boolean',
        'level_index' => 'integer',
        'pair_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
