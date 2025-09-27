<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_type',
        'amount',
        'left_spillover',
        'right_spillover',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'left_spillover' => 'decimal:2',
        'right_spillover' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
