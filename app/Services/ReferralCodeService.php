<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralCodeService
{
    public function generateCodes(User $admin, int $count = 50, ?User $assignTo = null)
    {
        $codes = [];
        $batchId = now()->format('YmdHis') . '-' . $admin->id;

        for ($i = 0; $i < $count; $i++) {
            do {
                $code = $this->generateSecureCode();
            } while (ReferralCode::where('code', $code)->exists());

            $codes[] = ReferralCode::create([
                'code' => $code,
                'generated_by' => $admin->id,
                'assigned_to' => $assignTo?->id,
                'status' => $assignTo ? 'assigned' : 'available',
                'batch_id' => $batchId,
            ]);
        }

        return $codes;
    }

    public function generateBulkCodes(User $admin, array $options = [])
    {
        $count = $options['count'] ?? 50;
        $assignTo = isset($options['distributor_id']) ? User::find($options['distributor_id']) : null;
        $prefix = $options['prefix'] ?? null;

        return $this->generateCodes($admin, $count, $assignTo);
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
            'usage_rate' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    public function getCodesByBatch(string $batchId)
    {
        return ReferralCode::where('batch_id', $batchId)
            ->with(['assignedTo', 'usedBy', 'generatedBy'])
            ->orderBy('created_at')
            ->get();
    }

    public function getDistributorCodes(User $distributor)
    {
        return ReferralCode::where('assigned_to', $distributor->id)
            ->with(['usedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUsedCodesByUser(User $user)
    {
        return ReferralCode::where('used_by', $user->id)
            ->with(['assignedTo', 'generatedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function expireUnusedCodes()
    {
        return ReferralCode::where('status', 'available')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    public function getAvailableCodesForAssignment()
    {
        return ReferralCode::where('status', 'available')
            ->orderBy('created_at')
            ->get();
    }
}