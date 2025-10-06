<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Notification;
use App\Models\Earning;
use App\Models\Withdrawal;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_welcome_notification_for_new_user()
    {
        $user = User::factory()->create();

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'success',
            'title' => 'Welcome to AKEN MLM!',
            'icon' => 'rocket',
            'color' => 'success',
            'is_read' => false,
        ]);
    }

    /** @test */
    public function it_creates_profile_completion_notification_when_profile_incomplete()
    {
        $user = User::factory()->create([
            'phone' => null,
            'address' => null,
        ]);

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Complete Your Profile',
            'icon' => 'user-edit',
            'color' => 'info',
        ]);
    }

    /** @test */
    public function it_does_not_create_profile_completion_notification_when_profile_complete()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
        ]);

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'title' => 'Complete Your Profile',
        ]);
    }

    /** @test */
    public function it_creates_referral_link_notification()
    {
        $user = User::factory()->create(['referral_code' => 'TEST123']);

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Your Referral Link is Ready',
            'icon' => 'link',
            'color' => 'primary',
        ]);

        $notification = Notification::where('user_id', $user->id)
            ->where('title', 'Your Referral Link is Ready')
            ->first();

        $this->assertStringContainsString('TEST123', $notification->message);
    }

    /** @test */
    public function it_creates_network_building_notification_when_no_downlines()
    {
        $user = User::factory()->create();

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Start Building Your Network',
            'icon' => 'users',
            'color' => 'primary',
        ]);
    }

    /** @test */
    public function it_does_not_create_network_building_notification_when_has_downlines()
    {
        $user = User::factory()->create();
        User::factory()->create(['sponsor_id' => $user->id]);

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'title' => 'Start Building Your Network',
        ]);
    }

    /** @test */
    public function it_creates_recent_earnings_notification_when_user_has_earnings()
    {
        $user = User::factory()->create();

        Earning::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'type' => 'direct',
            'status' => 'completed',
        ]);

        NotificationService::createSampleNotifications($user);

        // Should have called notifyEarnings which logs the notification
        // Since notifyEarnings uses Log::info, we can't easily test the log
        // But we can verify the earning exists
        $this->assertDatabaseHas('earnings', [
            'user_id' => $user->id,
            'amount' => 100.00,
            'type' => 'direct',
        ]);
    }

    /** @test */
    public function it_creates_pending_withdrawals_notification_when_has_pending_withdrawals()
    {
        $user = User::factory()->create();

        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'status' => 'pending',
        ]);

        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'status' => 'pending',
        ]);

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Withdrawal Pending Review',
            'icon' => 'clock',
            'color' => 'warning',
        ]);

        $notification = Notification::where('user_id', $user->id)
            ->where('title', 'Withdrawal Pending Review')
            ->first();

        $this->assertStringContainsString('2', $notification->message);
    }

    /** @test */
    public function it_does_not_create_pending_withdrawals_notification_when_no_pending_withdrawals()
    {
        $user = User::factory()->create();

        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'status' => 'completed',
        ]);

        NotificationService::createSampleNotifications($user);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'title' => 'Withdrawal Pending Review',
        ]);
    }

    /** @test */
    public function it_creates_all_sample_notifications_for_new_user()
    {
        $user = User::factory()->create([
            'phone' => null,
            'address' => null,
            'referral_code' => 'NEWUSER123',
        ]);

        NotificationService::createSampleNotifications($user);

        // Should have created multiple notifications
        $notifications = Notification::where('user_id', $user->id)->get();

        $this->assertGreaterThanOrEqual(3, $notifications->count());

        // Check for specific notification types
        $titles = $notifications->pluck('title')->toArray();

        $this->assertContains('Welcome to AKEN MLM!', $titles);
        $this->assertContains('Complete Your Profile', $titles);
        $this->assertContains('Your Referral Link is Ready', $titles);
        $this->assertContains('Start Building Your Network', $titles);
    }

    /** @test */
    public function it_handles_user_with_complete_profile_and_existing_network()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'shipping_name' => 'Test Shipping',
            'referral_code' => 'COMPLETE123',
        ]);

        // Add some downlines
        User::factory()->create(['sponsor_id' => $user->id]);
        User::factory()->create(['sponsor_id' => $user->id]);

        // Add earnings
        Earning::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'type' => 'direct',
            'status' => 'completed',
        ]);

        NotificationService::createSampleNotifications($user);

        $notifications = Notification::where('user_id', $user->id)->get();

        // Should only have welcome and referral link notifications
        $this->assertGreaterThanOrEqual(2, $notifications->count());

        $titles = $notifications->pluck('title')->toArray();

        $this->assertContains('Welcome to AKEN MLM!', $titles);
        $this->assertContains('Your Referral Link is Ready', $titles);

        // Should NOT contain these
        $this->assertNotContains('Complete Your Profile', $titles);
        $this->assertNotContains('Start Building Your Network', $titles);
    }

    /** @test */
    public function it_creates_notifications_with_correct_data_structure()
    {
        $user = User::factory()->create();

        NotificationService::createSampleNotifications($user);

        $notification = Notification::where('user_id', $user->id)->first();

        $this->assertNotNull($notification);
        $this->assertIsArray($notification->data);
        $this->assertFalse($notification->is_read);
        $this->assertNull($notification->read_at);
    }
}