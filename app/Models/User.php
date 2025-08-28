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
        'sponsor_id',
        'placement_side',
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
    ];

    /**
     * Relationships
     */
    public function referral()
    {
        return $this->hasOne(Referral::class);
    }

    public function referralUsed()
    {
        return $this->hasOne(Referral::class, 'used_by');
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
