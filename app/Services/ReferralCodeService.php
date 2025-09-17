<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralCodeService
{
    public function generateCodes(User $admin, int $count = 50)
    {
        // Check if there are any unused codes from previous batches
        $unusedCodes = ReferralCode::whereIn('status', ['available', 'assigned'])->count();
        if ($unusedCodes > 0) {
            throw new \Exception('Cannot generate new codes while unused codes exist. All codes must be used before generating a new batch.');
        }

        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            do {
                $code = $this->generateSecureCode();
            } while (ReferralCode::where('code', $code)->exists());

            $codes[] = ReferralCode::create([
                'code' => $code,
                'generated_by' => $admin->id,
                'status' => 'available',
            ]);
        }

        return $codes;
    }

    private function generateSecureCode(): string
    {
        // Generate a 16-character code with letters, numbers, and symbols
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $code = '';
        for ($i = 0; $i < 16; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    public function assignCodeToDistributor(ReferralCode $code, User $distributor)
    {
        $code->update([
            'assigned_to' => $distributor->id,
            'status' => 'assigned',
        ]);
    }

    public function validateAndUseCode(string $code, ?User $newUser = null)
    {
        $referralCode = ReferralCode::where('code', $code)->first();

        if (!$referralCode || $referralCode->used_by) {
            return false;
        }

        // Allow both 'assigned' and 'available' codes
        if ($referralCode->status !== 'assigned' && $referralCode->status !== 'available') {
            return false;
        }

        if ($newUser) {
            $referralCode->update([
                'used_by' => $newUser->id,
                'status' => 'used',
            ]);
        }

        // Return the assigned user if exists, otherwise the generated user (for admin codes)
        return $referralCode->assignedTo ?? $referralCode->generatedBy;
    }

    public function getCodeStatistics(): array
    {
        $total = ReferralCode::count();
        $used = ReferralCode::where('status', 'used')->count();
        $assigned = ReferralCode::where('status', 'assigned')->count();
        $available = ReferralCode::where('status', 'available')->count();
        $expired = ReferralCode::where('status', 'expired')->count();

        return [
            'total' => $total,
            'used' => $used,
            'assigned' => $assigned,
            'available' => $available,
            'expired' => $expired,
        ];
    }
}