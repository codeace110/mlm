<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.Dashboard');
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_codes_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_codes.index');
    }

    public function test_regular_user_cannot_access_admin_codes_index()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.admin_codes.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_codes_create()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_codes.create');
    }

    public function test_regular_user_cannot_access_admin_codes_create()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.admin_codes.create'));

        $response->assertStatus(403);
    }

    public function test_admin_can_generate_admin_codes()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 10,
                'batch_name' => 'Test Batch'
            ]);

        $response->assertRedirect(route('admin.admin_codes.index'));
        $response->assertSessionHas('success');
    }

    public function test_regular_user_cannot_generate_admin_codes()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 10,
                'batch_name' => 'Test Batch'
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_codes_batches()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.batches'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_codes.batches');
    }

    public function test_regular_user_cannot_access_admin_codes_batches()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.admin_codes.batches'));

        $response->assertStatus(403);
    }

    public function test_admin_can_download_admin_codes_csv()
    {
        // Create some test codes
        AdminCode::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="all_admin_codes_*.csv"');
    }

    public function test_regular_user_cannot_download_admin_codes_csv()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.admin_codes.download'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_users_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    public function test_regular_user_cannot_access_admin_users_index()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_network()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.network.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.network.index');
    }

    public function test_regular_user_cannot_access_admin_network()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.network.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_earnings()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.earnings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.earnings.index');
    }

    public function test_regular_user_cannot_access_admin_earnings()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.earnings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_withdrawals()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.withdrawals.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.withdrawals.index');
    }

    public function test_regular_user_cannot_access_admin_withdrawals()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.withdrawals.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_genealogy()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.genealogy.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.genealogy.index');
    }

    public function test_regular_user_cannot_access_admin_genealogy()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.genealogy.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_bonus_rules()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bonus_rules.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.bonus_rules.index');
    }

    public function test_regular_user_cannot_access_bonus_rules()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.bonus_rules.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_bonus_settings()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bonus_settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.bonus_settings.index');
    }

    public function test_regular_user_cannot_access_bonus_settings()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.bonus_settings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_referral_codes()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.referral_codes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.referral_codes.index');
    }

    public function test_regular_user_cannot_access_referral_codes()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.referral_codes.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_perform_user_actions()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Test approve user
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.approve', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Test deny user
        $user2 = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.deny', $user2));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_regular_user_cannot_perform_user_actions()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.users.approve', $user));

        $response->assertStatus(403);
    }

    public function test_admin_can_manage_bonus_rules()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bonus_rules.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.bonus_rules.create');
    }

    public function test_regular_user_cannot_manage_bonus_rules()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.bonus_rules.create'));

        $response->assertStatus(403);
    }

    public function test_admin_can_manage_withdrawals()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $withdrawal = \App\Models\Withdrawal::factory()->create(['user_id' => $user->id]);

        // Test approve withdrawal
        $response = $this->actingAs($this->admin)
            ->post(route('admin.withdrawals.approve', $withdrawal));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Test deny withdrawal
        $withdrawal2 = \App\Models\Withdrawal::factory()->create(['user_id' => $user->id]);
        $response = $this->actingAs($this->admin)
            ->post(route('admin.withdrawals.deny', $withdrawal2));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_regular_user_cannot_manage_withdrawals()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $withdrawal = \App\Models\Withdrawal::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.withdrawals.approve', $withdrawal));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_genealogy_search()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.genealogy.search'));

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_genealogy_search()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.genealogy.search'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_genealogy_network()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.genealogy.network', $user->id));

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_genealogy_network()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.genealogy.network', $user->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_bonus_settings()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.bonus_settings.update'), [
                'direct_bonus_amount' => 100,
                'pair_bonus_amount' => 100,
                'product_reward_interval' => 5
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_regular_user_cannot_update_bonus_settings()
    {
        $response = $this->actingAs($this->regularUser)
            ->put(route('admin.bonus_settings.update'), [
                'direct_bonus_amount' => 100,
                'pair_bonus_amount' => 100,
                'product_reward_interval' => 5
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_generate_referral_codes()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.referral_codes.generate'), [
                'user_id' => $this->regularUser->id,
                'count' => 5
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_regular_user_cannot_generate_referral_codes()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.referral_codes.generate'), [
                'user_id' => $this->regularUser->id,
                'count' => 5
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_access_referral_code_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.referral_codes.statistics'));

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_referral_code_statistics()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.referral_codes.statistics'));

        $response->assertStatus(403);
    }

    public function test_admin_can_perform_bulk_operations_on_referral_codes()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.referral_codes.bulk_export'));

        $response->assertStatus(200); // May return JSON or redirect
    }

    public function test_regular_user_cannot_perform_bulk_operations_on_referral_codes()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.referral_codes.bulk_export'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_show_page()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
    }

    public function test_regular_user_cannot_access_user_show_page()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_referral_code_show_page()
    {
        $referralCode = \App\Models\ReferralCode::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.referral_codes.show', $referralCode));

        $response->assertStatus(200);
        $response->assertViewIs('admin.referral_codes.show');
    }

    public function test_regular_user_cannot_access_referral_code_show_page()
    {
        $referralCode = \App\Models\ReferralCode::factory()->create();

        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.referral_codes.show', $referralCode));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_code_show_page()
    {
        $adminCode = AdminCode::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.show', $adminCode));

        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_codes.show');
    }

    public function test_regular_user_cannot_access_admin_code_show_page()
    {
        $adminCode = AdminCode::factory()->create();

        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.admin_codes.show', $adminCode));

        $response->assertStatus(403);
    }
}