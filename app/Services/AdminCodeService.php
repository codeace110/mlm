<?php

namespace App\Services;

use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * AdminCodeService handles the complete lifecycle of admin codes for MLM registration.
 *
 * REQUIREMENTS:
 * - Generate batches of codes (>=15 per batch)
 * - Enforce case-insensitive uniqueness
 * - Support issuing, revoking, and marking codes as used
 * - Proper concurrency handling with database locks
 * - Comprehensive audit trail and error handling
 */
class AdminCodeService
{
    /**
     * Minimum codes per batch
     */
    private const MIN_BATCH_SIZE = 15;

    /**
     * Code length for generated codes
     */
    private const CODE_LENGTH = 8;

    /**
     * Valid characters for code generation
     */
    private const CODE_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generate a batch of unique admin codes
     *
     * @param User $distributor The distributor who will own these codes
     * @param string $batchName Optional batch name for identification
     * @param int $count Number of codes to generate (minimum 15)
     * @return array Array of generated codes
     * @throws \Exception If batch size is invalid or generation fails
     */
    public function generateBatch(User $distributor, string $batchName = null, int $count = self::MIN_BATCH_SIZE): array
    {
        if ($count < self::MIN_BATCH_SIZE) {
            throw new \Exception("Batch size must be at least " . self::MIN_BATCH_SIZE . " codes");
        }

        return DB::transaction(function() use ($distributor, $batchName, $count) {
            $batchId = (string) Str::uuid();
            $codes = [];
            $attempts = 0;
            $maxAttempts = $count * 10; // Prevent infinite loops

            while (count($codes) < $count && $attempts < $maxAttempts) {
                $code = $this->generateUniqueCode();

                // Check for uniqueness with case-insensitive comparison
                $existingCode = AdminCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])
                    ->lockForUpdate()
                    ->exists();

                if (!$existingCode) {
                    $adminCode = AdminCode::create([
                        'code' => strtoupper($code), // Store in uppercase for consistency
                        'distributor_id' => $distributor->id,
                        'status' => 'available', // Start as available, will be assigned later
                        'batch_id' => $batchId,
                        'batch_name' => $batchName,
                    ]);

                    $codes[] = $adminCode->code;
                }

                $attempts++;
            }

            if (count($codes) < $count) {
                throw new \Exception("Failed to generate {$count} unique codes after {$maxAttempts} attempts");
            }

            Log::info('Admin code batch generated', [
                'batch_id' => $batchId,
                'distributor_id' => $distributor->id,
                'batch_name' => $batchName,
                'count' => count($codes),
            ]);

            return $codes;
        });
    }

    /**
     * Issue a code to a distributor
     *
     * @param string $code The code to issue
     * @param User $distributor The distributor to issue the code to
     * @return bool Success status
     */
    public function issueCode(string $code, User $distributor): bool
    {
        return DB::transaction(function() use ($code, $distributor) {
            $adminCode = AdminCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$adminCode) {
                Log::warning('Attempted to issue non-existent or already issued code', [
                    'code' => $code,
                    'distributor_id' => $distributor->id,
                ]);
                return false;
            }

            $adminCode->update([
                'distributor_id' => $distributor->id,
                'status' => 'assigned',
                'issued_at' => Carbon::now(),
                'issued_by_admin_id' => $distributor->id, // Admin issuing the code
            ]);

            \Log::info('Admin code issued', [
                'code' => $code,
                'distributor_id' => $distributor->id,
            ]);

            return true;
        });
    }

    /**
     * Revoke a code from a distributor
     *
     * @param string $code The code to revoke
     * @param User $distributor The distributor who owns the code
     * @return bool Success status
     */
    public function revokeCode(string $code, User $distributor): bool
    {
        return DB::transaction(function() use ($code, $distributor) {
            $adminCode = AdminCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])
                ->where('distributor_id', $distributor->id)
                ->whereIn('status', ['available', 'assigned'])
                ->lockForUpdate()
                ->first();

            if (!$adminCode) {
                Log::warning('Attempted to revoke non-existent, used, or unauthorized code', [
                    'code' => $code,
                    'distributor_id' => $distributor->id,
                ]);
                return false;
            }

            // Only allow revoking if not used
            if ($adminCode->status === 'used') {
                Log::warning('Cannot revoke already used code', [
                    'code' => $code,
                    'used_by' => $adminCode->used_by_user_id,
                ]);
                return false;
            }

            $adminCode->update([
                'distributor_id' => null,
                'status' => 'available',
                'issued_at' => null,
                'issued_by_admin_id' => null,
            ]);

            \Log::info('Admin code revoked', [
                'code' => $code,
                'distributor_id' => $distributor->id,
            ]);

            return true;
        });
    }

    /**
     * Validate and use a code for user registration
     *
     * @param string $code The code to validate and use
     * @param User|null $newUser The user using the code (optional for validation only)
     * @return User|false The distributor (sponsor) if valid, false otherwise
     */
    public function validateAndUseCode(string $code, ?User $newUser = null)
    {
        return DB::transaction(function() use ($code, $newUser) {
            // Use case-insensitive code lookup with proper locking
            $adminCode = AdminCode::with('distributor')
                ->whereRaw('UPPER(code) = ?', [strtoupper($code)])
                ->whereIn('status', ['available', 'assigned'])
                ->lockForUpdate()
                ->first();

            if (!$adminCode) {
                Log::warning('Invalid or unavailable admin code used', [
                    'code' => $code,
                    'provided_by_user' => $newUser ? $newUser->id : null,
                ]);
                return false;
            }

            if (!$adminCode->distributor) {
                Log::warning('Admin code has no associated distributor', [
                    'code' => $code,
                    'code_id' => $adminCode->id,
                ]);
                return false;
            }

            // Mark as used if a user is provided
            if ($newUser) {
                $adminCode->update([
                    'used_by_user_id' => $newUser->id,
                    'status' => 'used',
                    'used_at' => Carbon::now(),
                ]);

                Log::info('Admin code used successfully', [
                    'code' => $code,
                    'distributor_id' => $adminCode->distributor_id,
                    'used_by_user_id' => $newUser->id,
                ]);
            }

            return $adminCode->distributor; // Return the distributor as sponsor
        });
    }

    /**
     * Get batch information
     *
     * @param string $batchId The batch ID to query
     * @return array Batch information
     */
    public function getBatchInfo(string $batchId): array
    {
        $codes = AdminCode::where('batch_id', $batchId)
            ->get()
            ->groupBy('status');

        return [
            'batch_id' => $batchId,
            'total_codes' => $codes->sum->count,
            'available' => $codes->get('available', collect())->count(),
            'assigned' => $codes->get('assigned', collect())->count(),
            'used' => $codes->get('used', collect())->count(),
            'codes' => $codes->toArray(),
        ];
    }

    /**
     * Generate a unique code
     *
     * @return string Generated unique code
     */
    private function generateUniqueCode(): string
    {
        $characters = self::CODE_CHARACTERS;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * Get available codes for a distributor
     *
     * @param User $distributor The distributor to query
     * @return \Illuminate\Database\Eloquent\Collection Available codes
     */
    public function getAvailableCodes(User $distributor)
    {
        return AdminCode::where('distributor_id', $distributor->id)
            ->whereIn('status', ['available', 'assigned'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get usage statistics for a distributor
     *
     * @param User $distributor The distributor to query
     * @return array Usage statistics
     */
    public function getUsageStats(User $distributor): array
    {
        $codes = AdminCode::where('distributor_id', $distributor->id)
            ->get()
            ->groupBy('status');

        return [
            'total_codes' => $codes->sum->count,
            'available' => $codes->get('available', collect())->count(),
            'assigned' => $codes->get('assigned', collect())->count(),
            'used' => $codes->get('used', collect())->count(),
            'used_today' => AdminCode::where('distributor_id', $distributor->id)
                ->where('status', 'used')
                ->whereDate('used_at', Carbon::today())
                ->count(),
        ];
    }
}