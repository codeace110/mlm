<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'quantity',
        'total_amount',
        'method',
        'account_details',
        'status',
        'approved_at',
        'approved_by',
        'admin_notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'account_details' => 'array',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
