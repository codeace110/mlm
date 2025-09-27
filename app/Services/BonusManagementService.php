<?php

namespace App\Services;

use App\Models\Bonus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bonus Management Service
 *
 * Handles comprehensive bonus lifecycle management including
 * approval, payment, and reconciliation processes.
 */
class BonusManagementService
{
    /**
     * Approve pending bonuses for a user
     */
    public function approveUserBonuses(User $user, array $bonusIds = null): array
    {
        return DB::transaction(function() use ($user, $bonusIds) {
            $query = $user->bonuses()->where('status', 'pending');

            if ($bonusIds) {
                $query->whereIn('id', $bonusIds);
            }

            $bonuses = $query->lockForUpdate()->get();
            $approved = [];
            $failed = [];

            foreach ($bonuses as $bonus) {
                try {
                    if ($bonus->approve()) {
                        $approved[] = $bonus->id;

                        // Update user's account balance
                        if ($bonus->amount > 0) {
                            $user->increment('account_balance', $bonus->amount);
                        }

                        Log::info('Bonus approved', [
                            'bonus_id' => $bonus->id,
                            'user_id' => $user->id,
                            'amount' => $bonus->amount,
                        ]);
                    }
                } catch (\Exception $e) {
                    $failed[] = $bonus->id;
                    Log::error('Failed to approve bonus', [
                        'bonus_id' => $bonus->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'approved' => $approved,
                'failed' => $failed,
                'total_amount' => array_sum(array_map(fn($id) => Bonus::find($id)->amount ?? 0, $approved)),
            ];
        });
    }

    /**
     * Mark bonuses as paid
     */
    public function markBonusesAsPaid(array $bonusIds): array
    {
        return DB::transaction(function() use ($bonusIds) {
            $bonuses = Bonus::whereIn('id', $bonusIds)
                ->where('status', 'approved')
                ->lockForUpdate()
                ->get();

            $paid = [];
            $failed = [];

            foreach ($bonuses as $bonus) {
                try {
                    if ($bonus->markAsPaid()) {
                        $paid[] = $bonus->id;

                        Log::info('Bonus marked as paid', [
                            'bonus_id' => $bonus->id,
                            'user_id' => $bonus->user_id,
                            'amount' => $bonus->amount,
                        ]);
                    }
                } catch (\Exception $e) {
                    $failed[] = $bonus->id;
                    Log::error('Failed to mark bonus as paid', [
                        'bonus_id' => $bonus->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'paid' => $paid,
                'failed' => $failed,
            ];
        });
    }

    /**
     * Get bonus statistics for admin dashboard
     */
    public function getBonusStatistics(): array
    {
        $totalBonuses = Bonus::count();
        $pendingBonuses = Bonus::where('status', 'pending')->count();
        $approvedBonuses = Bonus::where('status', 'approved')->count();
        $paidBonuses = Bonus::where('status', 'paid')->count();
        $cancelledBonuses = Bonus::where('status', 'cancelled')->count();

        $totalAmount = Bonus::where('status', '!=', 'cancelled')->sum('amount');
        $pendingAmount = Bonus::where('status', 'pending')->sum('amount');
        $approvedAmount = Bonus::where('status', 'approved')->sum('amount');
        $paidAmount = Bonus::where('status', 'paid')->sum('amount');

        $todayBonuses = Bonus::whereDate('created_at', today())->count();
        $todayAmount = Bonus::whereDate('created_at', today())->sum('amount');

        return [
            'counts' => [
                'total' => $totalBonuses,
                'pending' => $pendingBonuses,
                'approved' => $approvedBonuses,
                'paid' => $paidBonuses,
                'cancelled' => $cancelledBonuses,
                'today' => $todayBonuses,
            ],
            'amounts' => [
                'total' => $totalAmount,
                'pending' => $pendingAmount,
                'approved' => $approvedAmount,
                'paid' => $paidAmount,
                'today' => $todayAmount,
            ],
            'percentages' => [
                'pending_rate' => $totalBonuses > 0 ? round(($pendingBonuses / $totalBonuses) * 100, 2) : 0,
                'approval_rate' => $pendingBonuses > 0 ? round(($approvedBonuses / ($pendingBonuses + $approvedBonuses)) * 100, 2) : 0,
                'payment_rate' => $approvedBonuses > 0 ? round(($paidBonuses / ($approvedBonuses + $paidBonuses)) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get user bonus summary
     */
    public function getUserBonusSummary(User $user): array
    {
        $bonuses = $user->bonuses();

        return [
            'total_earned' => $bonuses->sum('amount'),
            'pending_amount' => $bonuses->where('status', 'pending')->sum('amount'),
            'approved_amount' => $bonuses->where('status', 'approved')->sum('amount'),
            'paid_amount' => $bonuses->where('status', 'paid')->sum('amount'),
            'pending_count' => $bonuses->where('status', 'pending')->count(),
            'approved_count' => $bonuses->where('status', 'approved')->count(),
            'paid_count' => $bonuses->where('status', 'paid')->count(),
            'by_type' => [
                'direct' => $bonuses->where('reward_type', 'direct')->sum('amount'),
                'spillover' => $bonuses->where('reward_type', 'spillover')->sum('amount'),
                'level' => $bonuses->where('reward_type', 'level')->sum('amount'),
            ],
        ];
    }

    /**
     * Cancel bonuses (admin function)
     */
    public function cancelBonuses(array $bonusIds, string $reason): array
    {
        return DB::transaction(function() use ($bonusIds, $reason) {
            $bonuses = Bonus::whereIn('id', $bonusIds)
                ->whereIn('status', ['pending', 'approved'])
                ->lockForUpdate()
                ->get();

            $cancelled = [];
            $failed = [];

            foreach ($bonuses as $bonus) {
                try {
                    $oldStatus = $bonus->status;

                    $bonus->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => $reason,
                    ]);

                    // If it was approved, deduct from user's balance
                    if ($oldStatus === 'approved' && $bonus->amount > 0) {
                        $bonus->user->decrement('account_balance', $bonus->amount);
                    }

                    $cancelled[] = $bonus->id;

                    Log::info('Bonus cancelled', [
                        'bonus_id' => $bonus->id,
                        'user_id' => $bonus->user_id,
                        'reason' => $reason,
                    ]);
                } catch (\Exception $e) {
                    $failed[] = $bonus->id;
                    Log::error('Failed to cancel bonus', [
                        'bonus_id' => $bonus->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'cancelled' => $cancelled,
                'failed' => $failed,
            ];
        });
    }

    /**
     * Bulk approve bonuses with validation
     */
    public function bulkApproveBonuses(array $filters = []): array
    {
        return DB::transaction(function() use ($filters) {
            $query = Bonus::where('status', 'pending');

            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (isset($filters['reward_type'])) {
                $query->where('reward_type', $filters['reward_type']);
            }

            if (isset($filters['min_amount'])) {
                $query->where('amount', '>=', $filters['min_amount']);
            }

            if (isset($filters['max_amount'])) {
                $query->where('amount', '<=', $filters['max_amount']);
            }

            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            $bonuses = $query->lockForUpdate()->get();
            $results = ['approved' => [], 'failed' => []];

            foreach ($bonuses as $bonus) {
                try {
                    if ($bonus->approve()) {
                        $results['approved'][] = $bonus->id;
                    }
                } catch (\Exception $e) {
                    $results['failed'][] = $bonus->id;
                    Log::error('Bulk approval failed for bonus', [
                        'bonus_id' => $bonus->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $results;
        });
    }
}