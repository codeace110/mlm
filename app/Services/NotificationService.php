<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Earning;
use App\Models\Withdrawal;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createNotification($userId, $type, $title, $message, $icon = 'bell', $color = 'primary', $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'data' => $data,
        ]);
    }

    /**
     * Create notification for new referral
     */
    public function notifyNewReferral(User $sponsor, User $newUser)
    {
        $this->createNotification(
            $sponsor->id,
            'success',
            'New Referral Joined!',
            "Congratulations! {$newUser->name} has joined your network using your referral link.",
            'user-plus',
            'success',
            ['referral_id' => $newUser->id, 'referral_name' => $newUser->name]
        );
    }

    /**
     * Create notification for earnings
     */
    public function notifyEarnings(User $user, Earning $earning)
    {
        $type = $earning->status === 'pending' ? 'info' : 'success';
        $statusText = $earning->status === 'pending' ? 'pending' : 'credited';

        $this->createNotification(
            $user->id,
            $type,
            ucfirst($earning->type) . ' Earnings ' . ucfirst($statusText),
            "You have earned ₱" . number_format($earning->amount, 2) . " from {$earning->type} bonus.",
            'coins',
            $type,
            ['earning_id' => $earning->id, 'amount' => $earning->amount, 'type' => $earning->type]
        );
    }

    /**
     * Create notification for withdrawal status
     */
    public function notifyWithdrawalStatus(User $user, Withdrawal $withdrawal)
    {
        $type = match($withdrawal->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'info',
            default => 'primary'
        };

        $message = match($withdrawal->status) {
            'approved' => "Your withdrawal request of ₱" . number_format($withdrawal->amount, 2) . " has been approved and is being processed.",
            'rejected' => "Your withdrawal request of ₱" . number_format($withdrawal->amount, 2) . " has been rejected. Please contact support for details.",
            'pending' => "Your withdrawal request of ₱" . number_format($withdrawal->amount, 2) . " is being reviewed.",
            default => "Withdrawal status updated."
        };

        $this->createNotification(
            $user->id,
            $type,
            'Withdrawal ' . ucfirst($withdrawal->status),
            $message,
            'money-bill-wave',
            $type,
            ['withdrawal_id' => $withdrawal->id, 'amount' => $withdrawal->amount, 'status' => $withdrawal->status]
        );
    }

    /**
     * Create notification for level upgrade
     */
    public function notifyLevelUpgrade(User $user, $oldLevel, $newLevel)
    {
        $this->createNotification(
            $user->id,
            'success',
            'Level Upgraded!',
            "Congratulations! You have been promoted from Level {$oldLevel} to Level {$newLevel}.",
            'trophy',
            'warning',
            ['old_level' => $oldLevel, 'new_level' => $newLevel]
        );
    }

    /**
     * Create notification for binary pair matching
     */
    public function notifyPairMatching(User $user, $pairs, $bonusAmount)
    {
        $this->createNotification(
            $user->id,
            'success',
            'Binary Pair Bonus!',
            "Congratulations! You earned ₱" . number_format($bonusAmount, 2) . " from {$pairs} matched pairs.",
            'sitemap',
            'success',
            ['pairs' => $pairs, 'bonus_amount' => $bonusAmount]
        );
    }

    /**
     * Create notification for matching bonus
     */
    public function notifyMatchingBonus(User $user, $fromUser, $amount)
    {
        $this->createNotification(
            $user->id,
            'info',
            'Matching Bonus Received',
            "You received ₱" . number_format($amount, 2) . " matching bonus from {$fromUser->name}'s earnings.",
            'hand-holding-usd',
            'info',
            ['from_user_id' => $fromUser->id, 'from_user_name' => $fromUser->name, 'amount' => $amount]
        );
    }

    /**
     * Create notification for profile completion
     */
    public function notifyProfileComplete(User $user)
    {
        $this->createNotification(
            $user->id,
            'success',
            'Profile Completed!',
            "Your profile is now complete. You can now access all dashboard features.",
            'check-circle',
            'success'
        );
    }

    /**
     * Create notification for account verification
     */
    public function notifyAccountVerified(User $user)
    {
        $this->createNotification(
            $user->id,
            'success',
            'Account Verified!',
            "Your account has been successfully verified. You can now make withdrawals.",
            'shield-check',
            'success'
        );
    }

    /**
     * Get notifications for a user with pagination
     */
    public function getUserNotifications($userId, $perPage = 20)
    {
        return Notification::forUser($userId)
            ->recent()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount($userId)
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return Notification::forUser($userId)->unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Delete a notification
     */
    public function deleteNotification($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            return $notification->delete();
        }

        return false;
    }

    /**
     * Delete all read notifications for a user
     */
    public function deleteAllRead($userId)
    {
        return Notification::forUser($userId)->read()->delete();
    }

    /**
     * Clean up old notifications (older than 7 days)
     */
    public function cleanupOldNotifications()
    {
        return \App\Models\Notification::where('created_at', '<', now()->subDays(7))->delete();
    }
}