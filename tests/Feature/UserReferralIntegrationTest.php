<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReferralIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected ReferralCodeService $referralCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->referralCodeService = new ReferralCodeService();
    }

    public function test_complete_referral_workflow()
    {
        // Step 1: Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Step 2: Admin generates referral codes
        $codes = $this->referralCodeService->generateCodes($admin, 2);
        $this->assertCount(2, $codes);

        // Step 3: Create distributor and assign first code
        $distributor = User::factory()->create();
        $this->referralCodeService->assignCodeToDistributor($codes[0], $distributor);

        // Step 4: New user registers using the assigned code
        $newUser = User::factory()->create([
            'sponsor_id' => null, // Initially no sponsor
        ]);

        $result = $this->referralCodeService->validateAndUseCode($codes[0]->code, $newUser);

        // Step 5: Verify the referral relationship is established
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        // Set sponsor_id as done in registration
        $newUser->sponsor_id = $result->id;
        $newUser->save();

        $newUser->refresh();
        $this->assertEquals($distributor->id, $newUser->sponsor_id);

        // Step 6: Verify code is marked as used
        $codes[0]->refresh();
        $this->assertEquals('used', $codes[0]->status);
        $this->assertEquals($newUser->id, $codes[0]->used_by);

        // Step 8: Try to use the same code again (should fail)
        $anotherUser = User::factory()->create();
        $secondResult = $this->referralCodeService->validateAndUseCode($codes[0]->code, $anotherUser);
        $this->assertFalse($secondResult);

        // Step 9: Use the second code with another user
        $secondUser = User::factory()->create();
        $secondResult = $this->referralCodeService->validateAndUseCode($codes[1]->code, $secondUser);

        // Since second code is available (not assigned), sponsor should be the admin
        $this->assertInstanceOf(User::class, $secondResult);
        $this->assertEquals($admin->id, $secondResult->id);

        // Set sponsor_id as done in registration
        $secondUser->sponsor_id = $secondResult->id;
        $secondUser->save();

        $secondUser->refresh();
        $this->assertEquals($admin->id, $secondUser->sponsor_id);

        // Step 10: Check referral statistics
        $stats = $this->referralCodeService->getCodeStatistics();
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(2, $stats['used']);
        $this->assertEquals(0, $stats['assigned']);
        $this->assertEquals(0, $stats['available']);
    }

    public function test_referral_hierarchy_creation()
    {
        // Create a 3-level hierarchy
        $admin = User::factory()->create(['is_admin' => true]);

        // Level 1: Direct referral from admin
        $level1User = User::factory()->create(['sponsor_id' => $admin->id]);
        Referral::factory()->create([
            'user_id' => $level1User->id,
            'sponsor_id' => $admin->id,
            'level' => 1,
        ]);

        // Level 2: Referral from level 1 user
        $level2User = User::factory()->create(['sponsor_id' => $level1User->id]);
        Referral::factory()->create([
            'user_id' => $level2User->id,
            'sponsor_id' => $level1User->id,
            'level' => 1,
        ]);

        // Level 3: Referral from level 2 user
        $level3User = User::factory()->create(['sponsor_id' => $level2User->id]);
        Referral::factory()->create([
            'user_id' => $level3User->id,
            'sponsor_id' => $level2User->id,
            'level' => 1,
        ]);

        // Verify the hierarchy
        $this->assertEquals($admin->id, $level1User->sponsor_id);
        $this->assertEquals($level1User->id, $level2User->sponsor_id);
        $this->assertEquals($level2User->id, $level3User->sponsor_id);

        // Verify downlines
        $adminDownlines = $admin->downlines;
        $this->assertCount(1, $adminDownlines);
        $this->assertEquals($level1User->id, $adminDownlines->first()->id);

        $level1Downlines = $level1User->downlines;
        $this->assertCount(1, $level1Downlines);
        $this->assertEquals($level2User->id, $level1Downlines->first()->id);

        $level2Downlines = $level2User->downlines;
        $this->assertCount(1, $level2Downlines);
        $this->assertEquals($level3User->id, $level2Downlines->first()->id);
    }

    public function test_referral_code_batch_generation_and_usage()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Generate first batch
        $firstBatch = $this->referralCodeService->generateCodes($admin, 3);
        $this->assertCount(3, $firstBatch);

        // Use all codes from first batch
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $this->referralCodeService->validateAndUseCode($firstBatch[$i]->code, $user);
        }

        // Verify all codes are used
        $stats = $this->referralCodeService->getCodeStatistics();
        $this->assertEquals(3, $stats['used']);
        $this->assertEquals(0, $stats['available']);

        // Generate second batch (should be allowed now)
        $secondBatch = $this->referralCodeService->generateCodes($admin, 2);
        $this->assertCount(2, $secondBatch);

        // Verify updated statistics
        $stats = $this->referralCodeService->getCodeStatistics();
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['used']);
        $this->assertEquals(2, $stats['available']);
    }

    public function test_referral_code_edge_cases()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Test with invalid code
        $user = User::factory()->create();
        $result = $this->referralCodeService->validateAndUseCode('INVALID_CODE', $user);
        $this->assertFalse($result);

        // Test using code without new user
        $codes = $this->referralCodeService->generateCodes($admin, 1);
        $result = $this->referralCodeService->validateAndUseCode($codes[0]->code);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($admin->id, $result->id);

        // Code should still be available for use with user
        $newUser = User::factory()->create();
        $result = $this->referralCodeService->validateAndUseCode($codes[0]->code, $newUser);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($admin->id, $result->id);

        $codes[0]->refresh();
        $this->assertEquals('used', $codes[0]->status);
    }
}