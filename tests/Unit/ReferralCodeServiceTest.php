<?php

namespace Tests\Unit;

use App\Models\ReferralCode;
use App\Models\User;
use App\Services\ReferralCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReferralCodeService $referralCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->referralCodeService = new ReferralCodeService();
    }

    public function test_generate_codes_creates_correct_number_of_codes()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $codes = $this->referralCodeService->generateCodes($admin, 10);

        $this->assertCount(10, $codes);
        $this->assertDatabaseCount('referral_codes', 10);
    }

    public function test_generated_codes_are_at_least_15_characters()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $codes = $this->referralCodeService->generateCodes($admin, 5);

        foreach ($codes as $code) {
            $this->assertGreaterThanOrEqual(15, strlen($code->code));
        }
    }

    public function test_generated_codes_are_unique()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $codes = $this->referralCodeService->generateCodes($admin, 100);

        $codeValues = collect($codes)->pluck('code')->toArray();
        $this->assertCount(100, array_unique($codeValues));
    }

    public function test_cannot_generate_new_batch_if_unused_codes_exist()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Generate first batch
        $this->referralCodeService->generateCodes($admin, 5);

        // Try to generate second batch - should fail
        $this->expectException(\Exception::class);
        $this->referralCodeService->generateCodes($admin, 5);
    }

    public function test_can_generate_new_batch_after_all_codes_used()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Generate first batch
        $codes = $this->referralCodeService->generateCodes($admin, 2);

        // Assign and use both codes
        $this->referralCodeService->assignCodeToDistributor($codes[0], $user1);
        $this->referralCodeService->assignCodeToDistributor($codes[1], $user2);

        $newUser1 = User::factory()->create();
        $newUser2 = User::factory()->create();

        $this->referralCodeService->validateAndUseCode($codes[0]->code, $newUser1);
        $this->referralCodeService->validateAndUseCode($codes[1]->code, $newUser2);

        // Now should be able to generate new batch
        $newCodes = $this->referralCodeService->generateCodes($admin, 2);
        $this->assertCount(2, $newCodes);
    }

    public function test_assign_code_to_distributor()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $distributor = User::factory()->create();

        $codes = $this->referralCodeService->generateCodes($admin, 1);
        $code = $codes[0];

        $this->referralCodeService->assignCodeToDistributor($code, $distributor);

        $code->refresh();
        $this->assertEquals($distributor->id, $code->assigned_to);
        $this->assertEquals('assigned', $code->status);
    }

    public function test_validate_and_use_code_successfully()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $distributor = User::factory()->create();
        $newUser = User::factory()->create();

        $codes = $this->referralCodeService->generateCodes($admin, 1);
        $code = $codes[0];

        $this->referralCodeService->assignCodeToDistributor($code, $distributor);

        $result = $this->referralCodeService->validateAndUseCode($code->code, $newUser);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        $code->refresh();
        $this->assertEquals($newUser->id, $code->used_by);
        $this->assertEquals('used', $code->status);
    }

    public function test_validate_and_use_code_works_for_available_code()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $codes = $this->referralCodeService->generateCodes($admin, 1);
        $code = $codes[0];

        $newUser = User::factory()->create();

        $result = $this->referralCodeService->validateAndUseCode($code->code, $newUser);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($admin->id, $result->id); // For available codes, sponsor is the generator
    }

    public function test_validate_and_use_code_fails_for_used_code()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $distributor = User::factory()->create();
        $newUser1 = User::factory()->create();
        $newUser2 = User::factory()->create();

        $codes = $this->referralCodeService->generateCodes($admin, 1);
        $code = $codes[0];

        $this->referralCodeService->assignCodeToDistributor($code, $distributor);
        $this->referralCodeService->validateAndUseCode($code->code, $newUser1);

        $result = $this->referralCodeService->validateAndUseCode($code->code, $newUser2);

        $this->assertFalse($result);
    }

    public function test_get_code_statistics()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $distributor1 = User::factory()->create();
        $distributor2 = User::factory()->create();
        $newUser1 = User::factory()->create();
        $newUser2 = User::factory()->create();

        // Generate codes
        $codes = $this->referralCodeService->generateCodes($admin, 3);

        // Assign two codes
        $this->referralCodeService->assignCodeToDistributor($codes[0], $distributor1);
        $this->referralCodeService->assignCodeToDistributor($codes[1], $distributor2);

        // Use one
        $this->referralCodeService->validateAndUseCode($codes[0]->code, $newUser1);

        $stats = $this->referralCodeService->getCodeStatistics();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['used']);
        $this->assertEquals(1, $stats['assigned']);
        $this->assertEquals(1, $stats['available']);
    }
}