<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table primary key type.
     */
    protected $keyType = 'string';

    /**
     * Primary key is not auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'referral_code',
        'registration_code',
        'sponsor_id',
        'placement_side',
        'is_admin',
        'status',
        'level',
        'balancing_mode',
        'profile_image',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'shipping_name',
        'account_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'account_balance' => 'decimal:2',
        'balancing_mode' => 'string',
    ];

    /**
     * Relationships
     */
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'user_id');
    }

    public function sponsorReferrals()
    {
        return $this->hasMany(Referral::class, 'sponsor_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function downlines()
    {
        return $this->hasMany(User::class, 'sponsor_id');
    }

    public function earnings()
    {
        return $this->hasMany(Earning::class);
    }

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function referralCodes()
    {
        return $this->hasMany(ReferralCode::class, 'assigned_to');
    }

    public function usedReferralCodes()
    {
        return $this->hasMany(ReferralCode::class, 'used_by');
    }

    public function generatedReferralCodes()
    {
        return $this->hasMany(ReferralCode::class, 'generated_by');
    }

    public function binaryTree()
    {
        return $this->hasOne(BinaryTree::class, 'user_id');
    }

    /**
     * Get the binary tree record for this user
     */
    public function getOrCreateBinaryTree(): BinaryTree
    {
        return $this->binaryTree ?? $this->binaryTree()->create([
            'user_id' => $this->id,
            'total_left_volume' => 0,
            'total_right_volume' => 0,
            'left_consumed' => 0,
            'right_consumed' => 0,
            'level_index' => 1,
            'reward_count' => 0,
            'direct_pairs_paid' => 0,
            'spillover_pairs_paid' => 0,
            'left_spillover' => 0,
            'right_spillover' => 0,
        ]);
    }

    /**
     * Get all direct downline users
     */
    public function getDirectDownline(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('sponsor_id', $this->id)->get();
    }

    /**
     * Get downline users by placement side
     */
    public function getDownlineBySide(string $side): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('sponsor_id', $this->id)
            ->where('placement_side', $side)
            ->get();
    }

    /**
     * Get total downline count
     */
    public function getTotalDownlineCount(): int
    {
        return $this->getAllDownline()->count();
    }

    /**
     * Get all downline users recursively
     */
    public function getAllDownline(): \Illuminate\Database\Eloquent\Collection
    {
        $downline = collect();
        $directDownline = $this->getDirectDownline();

        foreach ($directDownline as $directUser) {
            $downline->push($directUser);
            $downline = $downline->merge($directUser->getAllDownline());
        }

        return $downline;
    }

    /**
     * Get network level (distance from root)
     */
    public function getNetworkLevel(): int
    {
        $level = 1;
        $current = $this;

        while ($current->sponsor_id) {
            $sponsor = self::find($current->sponsor_id);
            if (!$sponsor) break;

            $level++;
            $current = $sponsor;

            // Prevent infinite loops
            if ($level > 1000) break;
        }

        return $level;
    }

    /**
     * Get upline users
     */
    public function getUpline(): \Illuminate\Database\Eloquent\Collection
    {
        $upline = collect();
        $current = $this;

        while ($current->sponsor_id) {
            $sponsor = self::find($current->sponsor_id);
            if (!$sponsor) break;

            $upline->push($sponsor);
            $current = $sponsor;

            // Prevent infinite loops
            if ($upline->count() > 1000) break;
        }

        return $upline;
    }

    /**
     * Get left leg downline
     */
    public function getLeftLegDownline(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getDownlineBySide('left')->merge(
            $this->getDownlineBySide('left')->flatMap->getAllDownline()
        );
    }

    /**
     * Get right leg downline
     */
    public function getRightLegDownline(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getDownlineBySide('right')->merge(
            $this->getDownlineBySide('right')->flatMap->getAllDownline()
        );
    }

    /**
     * Get network statistics
     */
    public function getNetworkStats(): array
    {
        $tree = $this->binaryTree;
        $leftCount = $this->getLeftLegDownline()->count();
        $rightCount = $this->getRightLegDownline()->count();

        return [
            'total_downline' => $this->getTotalDownlineCount(),
            'left_leg_count' => $leftCount,
            'right_leg_count' => $rightCount,
            'direct_downline' => $this->getDirectDownline()->count(),
            'network_level' => $this->getNetworkLevel(),
            'is_balanced' => $tree ? $tree->isBalanced() : false,
            'volume_difference' => $tree ? $tree->getVolumeDifference() : 0,
            'current_level_quota' => $tree ? $tree->getNextLevelQuota() : 0,
            'level_quota_reached' => $tree ? $tree->isLevelQuotaReached() : false,
        ];
    }

    /**
     * Check if user is active in the system
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get user's current level in MLM structure
     */
    public function getCurrentMLMLevel(): int
    {
        return $this->level ?? 1;
    }

    /**
     * Get available balance for withdrawal
     */
    public function getAvailableBalance(): float
    {
        return $this->account_balance ?? 0.00;
    }

    public function totalEarnings()
    {
        return $this->earnings()->sum('amount');
    }

    /**
     * Auto-generate custom ID when creating user.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = 'AKEN' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 6));
            }
        });
    }
}
