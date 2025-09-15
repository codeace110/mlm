<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'distributor_id',
        'status',
        'used_by_user_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function usedByUser()
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }
}
