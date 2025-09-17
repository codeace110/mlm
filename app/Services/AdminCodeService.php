<?php

namespace App\Services;

use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminCodeService
{
    public function validateAndUseCode(string $code, ?User $newUser = null)
    {
        return DB::transaction(function() use ($code, $newUser) {
            $adminCode = AdminCode::with('distributor')
                ->where('code', $code)
                ->whereIn('status', ['issued', 'unused'])
                ->lockForUpdate()
                ->first();

            if (!$adminCode || !$adminCode->distributor) {
                return false;
            }

            if ($newUser) {
                $adminCode->update([
                    'used_by_user_id' => $newUser->id,
                    'status' => 'used',
                    'used_at' => now(),
                ]);
            }

            return $adminCode->distributor; // Return the distributor as sponsor
        });
    }
}