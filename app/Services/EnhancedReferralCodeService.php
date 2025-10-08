<?php

namespace App\Services;

use App\Models\AdminCode;
use App\Models\User;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Enhanced Referral Code Service
 *
 * Consolidates AdminCode and ReferralCode functionality with proper tracking,
 * expiration, and audit trails for MLM referral system.
 */
class EnhancedReferralCodeService
{
    /**
     * Generate a batch of unique referral codes
     */
    public function generateBatch(User $generatedBy, int $count = 50, ?string $batchName = null, ?int $expiresInDays = 30): array
    {
        $batchId = (string) Str::uuid();

        return DB::transaction(function() use ($generatedBy, $count, $batchName, $expiresInDays, $batchId) {
            $codes = [];
            $attempts = 0;
            $maxAttempts = $count * 10;

            while (count($codes) < $count && $attempts < $maxAttempts) {
                $code = $this->generateUniqueCode();

                // Check for uniqueness across both tables
                $existingCode = AdminCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])
                    ->lockForUpdate()
                    ->exists();

                if (!$existingCode) {
                    $expiresAt = $expiresInDays ? Carbon::now()->addDays($expiresInDays) : null;

                    $adminCode = AdminCode::create([
                        'code' => strtoupper($code),
                        'generated_by' => $generatedBy->id,
                        'status' => 'available',
                        'batch_id' => $batchId,
                        'batch_name' => $batchName,
                        'expires_at' => $expiresAt,
                    ]);

                    $codes[] = $adminCode->code;
                }

                $attempts++;
            }

            if (count($codes) < $count) {
                throw new \Exception("Failed to generate {$count} unique codes after {$maxAttempts} attempts");
            }

            Log::info('Referral code batch generated', [
                'batch_id' => $batchId,
                'generated_by' => $generatedBy->id,
                'count' => count($codes),
                'expires_in_days' => $expiresInDays,
            ]);

            return $codes;
        });
    }

    /**
     * Assign a single code to a distributor
     */
    public function assignCodeToDistributor(AdminCode $code, User $distributor): AdminCode
    {
        return DB::transaction(function() use ($code, $distributor) {
            $code->update([
                'distributor_id' => $distributor->id,
                'status' => 'assigned',
                'issued_at' => now(),
            ]);

            Log::info('Code assigned to distributor', [
                'code' => $code->code,
                'distributor_id' => $distributor->id,
                'assigned_by' => auth()->id(),
            ]);

            return $code->fresh();
        });
    }

    /**
     * Assign codes to a distributor with proper tracking
     */
    public function assignCodesToDistributor(User $distributor, array $codeIds): array
    {
        return DB::transaction(function() use ($distributor, $codeIds) {
            $assigned = [];
            $failed = [];

            foreach ($codeIds as $codeId) {
                $code = AdminCode::where('id', $codeId)
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->first();

                if ($code) {
                    $code->update([
                        'distributor_id' => $distributor->id,
                        'status' => 'assigned',
                        'issued_at' => now(),
                    ]);

                    $assigned[] = $code->code;

                    Log::info('Code assigned to distributor', [
                        'code' => $code->code,
                        'distributor_id' => $distributor->id,
                        'assigned_by' => auth()->id(),
                    ]);
                } else {
                    $failed[] = $codeId;
                }
            }

            return [
                'assigned' => $assigned,
                'failed' => $failed,
            ];
        });
    }

    /**
     * Validate and use a code during registration
     */
    public function validateAndUseCode(string $code, User $newUser): ?User
    {
        return DB::transaction(function() use ($code, $newUser) {
            // First check if it's an available/assigned code
            $adminCode = AdminCode::with('distributor')
                ->whereRaw('UPPER(code) = ?', [strtoupper($code)])
                ->whereIn('status', ['available', 'assigned'])
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->first();

            if (!$adminCode) {
                Log::warning('Invalid or expired referral code used', [
                    'code' => $code,
                    'user_id' => $newUser->id,
                    'searched_statuses' => ['available', 'assigned'],
                ]);

                // Debug: Check if code exists with different status
                $existingCode = AdminCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])->first();
                if ($existingCode) {
                    Log::warning('Code exists but wrong status', [
                        'code' => $code,
                        'current_status' => $existingCode->status,
                        'distributor_id' => $existingCode->distributor_id,
                    ]);
                }

                return null;
            }

            // Mark as used
            $adminCode->update([
                'used_by_user_id' => $newUser->id,
                'status' => 'used',
                'used_at' => now(),
            ]);

            // Create tracking record
            $this->createUsageTracking($adminCode, $newUser);

            Log::info('Referral code used successfully', [
                'code' => $code,
                'used_by' => $newUser->id,
                'distributor_id' => $adminCode->distributor_id,
            ]);

            return $adminCode->distributor ?: null; // Return the distributor as sponsor
        });
    }

    /**
     * Get comprehensive code statistics
     */
    public function getCodeStatistics(): array
    {
        $totalCodes = AdminCode::count();
        $availableCodes = AdminCode::where('status', 'available')->count();
        $assignedCodes = AdminCode::where('status', 'assigned')->count();
        $usedCodes = AdminCode::where('status', 'used')->count();
        $expiredCodes = AdminCode::where('status', 'available')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();

        $todayUsed = AdminCode::where('status', 'used')
            ->whereDate('used_at', today())
            ->count();

        $thisWeekUsed = AdminCode::where('status', 'used')
            ->whereBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            'total' => $totalCodes,
            'available' => $availableCodes,
            'assigned' => $assignedCodes,
            'used' => $usedCodes,
            'expired' => $expiredCodes,
            'today_used' => $todayUsed,
            'week_used' => $thisWeekUsed,
            'usage_rate' => $totalCodes > 0 ? round(($usedCodes / $totalCodes) * 100, 2) : 0,
        ];
    }

    /**
     * Get distributor performance statistics
     */
    public function getDistributorStats(User $distributor): array
    {
        $codes = AdminCode::where('distributor_id', $distributor->id)
            ->get()
            ->groupBy('status');

        $usedCodes = $codes->get('used', collect());

        return [
            'total_assigned' => $codes->sum->count,
            'available' => $codes->get('assigned', collect())->count(),
            'used' => $usedCodes->count(),
            'conversion_rate' => $codes->sum->count > 0 ? round(($usedCodes->count() / $codes->sum->count) * 100, 2) : 0,
            'today_used' => $usedCodes->where('used_at', '>=', today())->count(),
            'this_week_used' => $usedCodes->where('used_at', '>=', now()->startOfWeek())->count(),
        ];
    }

    /**
     * Clean up expired codes
     */
    public function cleanupExpiredCodes(): int
    {
        return DB::transaction(function() {
            $expiredCount = AdminCode::where('status', 'available')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->update([
                    'status' => 'expired',
                    'expired_at' => now(),
                ]);

            Log::info("Cleaned up {$expiredCount} expired codes");
            return $expiredCount;
        });
    }

    /**
     * Generate unique UUID-based referral code (AKEN + 15 random characters)
     */
    private function generateUniqueCode(): string
    {
        $length = 15;
        $randomPart = strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
        return 'AKEN' . $randomPart;
    }

    /**
     * Create usage tracking record
     */
    private function createUsageTracking(AdminCode $code, User $user): void
    {
        // Create a record in the referrals table for tracking
        \App\Models\Referral::create([
            'user_id' => $user->id,
            'sponsor_id' => $code->distributor_id,
            'referral_code' => $code->code,
            'status' => 'approved', // Use 'approved' instead of 'active'
            'joined_at' => now(),
        ]);
    }
}