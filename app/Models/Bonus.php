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
        'approved_at',
        'paid_at',
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

    /**
     * Create a cash bonus (100 pesos)
     */
    public static function createCashBonus(array $attributes): self
    {
        $attributes['amount'] = 100.00;
        $attributes['is_product'] = false;
        $attributes['description'] = $attributes['description'] ?? 'Cash Bonus - 100 Pesos';

        return self::create($attributes);
    }

    /**
     * Create a product bonus (0 amount, is_product = true)
     */
    public static function createProductBonus(array $attributes): self
    {
        $attributes['amount'] = 0.00;
        $attributes['is_product'] = true;
        $attributes['description'] = $attributes['description'] ?? 'Product Bonus';

        return self::create($attributes);
    }

    /**
     * Scope for cash bonuses
     */
    public function scopeCash($query)
    {
        return $query->where('is_product', false)->where('amount', '>', 0);
    }

    /**
     * Scope for product bonuses
     */
    public function scopeProduct($query)
    {
        return $query->where('is_product', true);
    }

    /**
     * Scope for direct bonuses
     */
    public function scopeDirect($query)
    {
        return $query->where('reward_type', 'direct');
    }

    /**
     * Scope for level bonuses
     */
    public function scopeLevel($query)
    {
        return $query->where('reward_type', 'level');
    }

    /**
     * Scope for pending bonuses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid bonuses
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for spillover bonuses
     */
    public function scopeSpillover($query)
    {
        return $query->where('reward_type', 'spillover');
    }

    /**
     * Scope for approved bonuses
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for cancelled bonuses
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Mark bonus as approved
     */
    public function approve(): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark bonus as paid
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
