<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralCodeService
{
    public function generateCodes(User $admin, int $count = 50)
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = Str::upper(Str::random(8));

            $codes[] = ReferralCode::create([
                'code' => $code,
                'generated_by' => $admin->id,
                'status' => 'available',
            ]);
        }

        return $codes;
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

        if (!$referralCode || $referralCode->status !== 'assigned' || $referralCode->used_by) {
            return false;
        }

        if ($newUser) {
            $referralCode->update([
                'used_by' => $newUser->id,
                'status' => 'used',
            ]);
        }

        return $referralCode->assignedTo; // Return the distributor who owns the code
    }
}