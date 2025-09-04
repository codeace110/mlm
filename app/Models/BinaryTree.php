<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinaryTree extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'left_child_id',
        'right_child_id',
        'left_volume',
        'right_volume',
        'carryover_left',
        'carryover_right',
    ];

    protected $casts = [
        'left_volume' => 'decimal:2',
        'right_volume' => 'decimal:2',
        'carryover_left' => 'decimal:2',
        'carryover_right' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leftChild()
    {
        return $this->belongsTo(User::class, 'left_child_id');
    }

    public function rightChild()
    {
        return $this->belongsTo(User::class, 'right_child_id');
    }
}
