<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinaryTree extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'left_child_id',
        'right_child_id',
        'left_volume',
        'right_volume',
        'left_spillover',
        'right_spillover',
        'carryover_left',
        'carryover_right',
        'total_left_volume',
        'total_right_volume',
        'left_consumed',
        'right_consumed',
        'level_index',
        'reward_count',
        'direct_pairs_paid',
    ];

    protected $casts = [
        'left_volume' => 'decimal:2',
        'right_volume' => 'decimal:2',
        'left_spillover' => 'decimal:2',
        'right_spillover' => 'decimal:2',
        'carryover_left' => 'decimal:2',
        'carryover_right' => 'decimal:2',
        'total_left_volume' => 'decimal:2',
        'total_right_volume' => 'decimal:2',
        'left_consumed' => 'decimal:2',
        'right_consumed' => 'decimal:2',
        'level_index' => 'integer',
        'reward_count' => 'integer',
        'direct_pairs_paid' => 'integer',
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
