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
        'carryover_left',
        'carryover_right',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'carryover_left' => 'decimal:2',
        'carryover_right' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
