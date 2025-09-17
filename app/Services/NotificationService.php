<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bonus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify user about earnings/bonuses.
     */
    public static function notifyEarnings(User $user, $bonus): void
    {
        $isProduct = $bonus->is_product ?? false;
        $message = $isProduct
            ? "Congratulations! You've earned a product reward."
            : "Congratulations! You've earned ₱{$bonus->amount}.";

        // Log the notification
        Log::info("Bonus notification for user {$user->id}: {$message}");

        // Here you could send email, push notification, etc.
        // For example:
        // Mail::to($user->email)->send(new BonusNotification($bonus));

        // Or use Laravel's notification system:
        // $user->notify(new BonusNotification($bonus));
    }

    /**
     * Notify user about level completion.
     */
    public static function notifyLevelCompletion(User $user, int $level): void
    {
        $message = "Congratulations! You've completed level {$level}.";

        Log::info("Level completion notification for user {$user->id}: {$message}");

        // Send notification
    }

    /**
     * Notify user about direct referral bonus.
     */
    public static function notifyDirectBonus(User $user): void
    {
        $message = "You've received a direct referral bonus!";

        Log::info("Direct bonus notification for user {$user->id}: {$message}");

        // Send notification
    }

    /**
     * Get unread notifications count for user.
     */
    public static function getUnreadCount($user): int
    {
        // For now, return 0 as we don't have a notifications table
        return 0;
    }

    /**
     * Notify user about pair matching.
     */
    public static function notifyPairMatching(User $user, int $pairs, float $bonus): void
    {
        $message = "You've earned ₱{$bonus} from {$pairs} pair(s)!";

        Log::info("Pair matching notification for user {$user->id}: {$message}");

        // Send notification
    }

    /**
     * Notify user about pair bonus with product count.
     */
    public static function notifyPairBonus(User $user, int $pairsProcessed, int $productCount): void
    {
        $message = "You've processed {$pairsProcessed} pairs";
        if ($productCount > 0) {
            $message .= " and earned {$productCount} product reward(s)";
        }

        Log::info("Pair bonus notification for user {$user->id}: {$message}");

        // Send notification
    }

    /**
     * Create a notification.
     */
    public static function createNotification(string $userId, string $type, string $title, string $message, string $icon = 'info', string $color = 'info', array $data = []): void
    {
        Log::info("Notification for user {$userId}: {$title} - {$message}");

        // Create notification record
        \App\Models\Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify about new referral.
     */
    public static function notifyNewReferral(User $user, User $newUser): void
    {
        $message = "New referral joined: {$newUser->name}";

        Log::info("New referral notification for user {$user->id}: {$message}");

        // Send notification
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(string $userId): void
    {
        \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Delete all read notifications for a user.
     */
    public static function deleteAllRead(string $userId): void
    {
        \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Get paginated notifications for a user.
     */
    public function getUserNotifications(string $userId, int $perPage = 15)
    {
        return \App\Models\Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create sample notifications for a new user.
     */
    public static function createSampleNotifications(User $user): void
    {
        // Welcome notification
        self::createNotification(
            $user->id,
            'success',
            'Welcome to AKEN MLM!',
            'Your account has been successfully created. Start building your network today!',
            'rocket',
            'success'
        );

        // Profile completion reminder
        if (!$user->phone || !$user->address) {
            self::createNotification(
                $user->id,
                'info',
                'Complete Your Profile',
                'Please complete your profile information to unlock all features.',
                'user-edit',
                'info'
            );
        }

        // Referral code notification
        self::createNotification(
            $user->id,
            'info',
            'Your Referral Link is Ready',
            'Share your referral link to start earning commissions: ' . url('/register?ref=' . $user->referral_code),
            'link',
            'primary'
        );

        // Network building tips
        $downlinesCount = User::where('sponsor_id', $user->id)->count();
        if ($downlinesCount == 0) {
            self::createNotification(
                $user->id,
                'info',
                'Start Building Your Network',
                'Add your first referral to begin earning from the binary compensation plan.',
                'users',
                'primary'
            );
        }

        // Recent earnings notification (if any)
        $recentEarnings = \App\Models\Earning::where('user_id', $user->id)->latest()->first();
        if ($recentEarnings) {
            self::notifyEarnings($user, $recentEarnings);
        }

        // Pending withdrawals notification
        $pendingWithdrawals = \App\Models\Withdrawal::where('user_id', $user->id)->where('status', 'pending')->count();
        if ($pendingWithdrawals > 0) {
            self::createNotification(
                $user->id,
                'info',
                'Withdrawal Pending Review',
                "You have {$pendingWithdrawals} withdrawal request(s) currently being reviewed.",
                'clock',
                'warning'
            );
        }
    }
}