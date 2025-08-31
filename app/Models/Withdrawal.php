<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'account_details',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'account_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}