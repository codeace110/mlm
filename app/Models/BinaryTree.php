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
        'left_volume' => 'float',
        'right_volume' => 'float',
        'left_spillover' => 'float',
        'right_spillover' => 'float',
        'carryover_left' => 'float',
        'carryover_right' => 'float',
        'total_left_volume' => 'float',
        'total_right_volume' => 'float',
        'left_consumed' => 'float',
        'right_consumed' => 'float',
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
