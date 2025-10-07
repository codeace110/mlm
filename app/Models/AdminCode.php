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
        'generated_by',
        'batch_id',
        'batch_name',
        'expires_at',
        'status',
        'used_by_user_id',
        'used_at',
        'issued_to_user_id',
        'issued_by_admin_id',
        'issued_at',
        'notes',
        'tracker',
    ];

    /**
     * Boot the model and add global scope for case-insensitive code uniqueness
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically convert code to uppercase when setting
        static::saving(function ($model) {
            if ($model->code) {
                $model->code = strtoupper($model->code);
            }
        });
    }

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the distributor who owns this code
     */
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function issuedTo()
    {
        return $this->belongsTo(User::class, 'issued_to_user_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by_admin_id');
    }

    public function usedByUser()
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    /**
     * Mark the admin code as used with transaction and locking
     */
    public function markAsUsed(string $userId, $callback = null)
    {
        return \DB::transaction(function () use ($userId, $callback) {
            // Lock the record for update to prevent race conditions
            $code = self::lockForUpdate()->find($this->id);

            if (!in_array($code->status, ['available', 'assigned'])) {
                throw new \Exception("Admin code is not available for use. Current status: {$code->status}");
            }

            $code->update([
                'status' => 'used',
                'used_by_user_id' => $userId,
                'used_at' => now(),
            ]);

            if ($callback && is_callable($callback)) {
                $callback($code);
            }

            return $code;
        });
    }

    /**
     * Check if code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if code is available for use
     */
    public function isAvailable(): bool
    {
        return in_array($this->status, ['available', 'assigned']) && !$this->isExpired();
    }

    /**
     * Scope for finding available codes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for finding assigned codes
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope for finding used codes
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    /**
     * Scope for finding expired codes
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Check if a code exists (case-insensitive)
     */
    public static function codeExists(string $code): bool
    {
        $upperCode = strtoupper($code);
        return self::where('code', $upperCode)->exists();
    }

    /**
     * Find a code by value (case-insensitive)
     */
    public static function findByCode(string $code)
    {
        $upperCode = strtoupper($code);
        return self::where('code', $upperCode)->first();
    }

    /**
     * Generate a unique admin code
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
        } while (self::codeExists($code));

        return $code;
    }

    /**
     * Create a new admin code with auto-generated unique code
     */
    public static function createWithUniqueCode(array $attributes = []): self
    {
        $attributes['code'] = self::generateUniqueCode();
        return self::create($attributes);
    }
}
