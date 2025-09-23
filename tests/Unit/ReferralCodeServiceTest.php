<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReferralCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralCodeService $service;
    private User $admin;
    private User $distributor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReferralCodeService();

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'status' => 'active'
        ]);

        $this->distributor = User::factory()->create([
            'is_admin' => false,
            'status' => 'active'
        ]);
    }

    public function test_generate_codes_creates_specified_number_of_codes()
    {
        $codes = $this->service->generateCodes($this->admin, 5);

        $this->assertCount(5, $codes);
        $this->assertDatabaseCount('referral_codes', 5);

        foreach ($codes as $code) {
            $this->assertEquals($this->admin->id, $code->generated_by);
            $this->assertEquals('available', $code->status);
        }
    }

    public function test_generate_codes_with_distributor_assignment()
    {
        $codes = $this->service->generateCodes($this->admin, 3, $this->distributor);

        $this->assertCount(3, $codes);
        $this->assertDatabaseCount('referral_codes', 3);

        foreach ($codes as $code) {
            $this->assertEquals($this->admin->id, $code->generated_by);
            $this->assertEquals($this->distributor->id, $code->assigned_to);
            $this->assertEquals('assigned', $code->status);
        }
    }

    public function test_generate_bulk_codes()
    {
        $options = [
            'count' => 10,
            'distributor_id' => $this->distributor->id,
            'prefix' => 'TEST'
        ];

        $codes = $this->service->generateBulkCodes($this->admin, $options);

        $this->assertCount(10, $codes);
        $this->assertDatabaseCount('referral_codes', 10);
    }

    public function test_assign_code_to_distributor()
    {
        $code = ReferralCode::factory()->create([
            'generated_by' => $this->admin->id,
            'status' => 'available'
        ]);

        $this->service->assignCodeToDistributor($code, $this->distributor);

        $code->refresh();
        $this->assertEquals($this->distributor->id, $code->assigned_to);
        $this->assertEquals('assigned', $code->status);
    }

    public function test_validate_and_use_code_with_available_code()
    {
        $code = ReferralCode::factory()->create([
            'code' => 'TEST123',
            'generated_by' => $this->admin->id,
            'status' => 'available'
        ]);

        $result = $this->service->validateAndUseCode('TEST123');

        $this->assertEquals($this->admin->id, $result->id);
        $this->assertEquals('used', $code->fresh()->status);
    }

    public function test_validate_and_use_code_with_assigned_code()
    {
        $code = ReferralCode::factory()->create([
            'code' => 'ASSIGNED123',
            'generated_by' => $this->admin->id,
            'assigned_to' => $this->distributor->id,
            'status' => 'assigned'
        ]);

        $result = $this->service->validateAndUseCode('ASSIGNED123');

        $this->assertEquals($this->distributor->id, $result->id);
        $this->assertEquals('used', $code->fresh()->status);
    }

    public function test_validate_and_use_code_with_invalid_code()
    {
        $result = $this->service->validateAndUseCode('INVALID123');

        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_with_used_code()
    {
        $code = ReferralCode::factory()->create([
            'code' => 'USED123',
            'generated_by' => $this->admin->id,
            'status' => 'used'
        ]);

        $result = $this->service->validateAndUseCode('USED123');

        $this->assertFalse($result);
    }

    public function test_get_code_statistics()
    {
        // Create test data
        ReferralCode::factory()->count(5)->create(['status' => 'available']);
        ReferralCode::factory()->count(3)->create(['status' => 'assigned']);
        ReferralCode::factory()->count(2)->create(['status' => 'used']);

        $stats = $this->service->getCodeStatistics();

        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(2, $stats['used']);
        $this->assertEquals(3, $stats['assigned']);
        $this->assertEquals(5, $stats['available']);
        $this->assertEquals(20.0, $stats['usage_rate']);
    }

    public function test_get_codes_by_batch()
    {
        $batchId = '20231201-120000-' . $this->admin->id;

        ReferralCode::factory()->count(3)->create([
            'batch_id' => $batchId,
            'generated_by' => $this->admin->id
        ]);

        $codes = $this->service->getCodesByBatch($batchId);

        $this->assertCount(3, $codes);
        foreach ($codes as $code) {
            $this->assertEquals($batchId, $code->batch_id);
        }
    }

    public function test_get_distributor_codes()
    {
        ReferralCode::factory()->count(3)->create([
            'assigned_to' => $this->distributor->id,
            'generated_by' => $this->admin->id
        ]);

        $codes = $this->service->getDistributorCodes($this->distributor);

        $this->assertCount(3, $codes);
        foreach ($codes as $code) {
            $this->assertEquals($this->distributor->id, $code->assigned_to);
        }
    }

    public function test_get_used_codes_by_user()
    {
        $user = User::factory()->create();

        ReferralCode::factory()->count(2)->create([
            'used_by' => $user->id,
            'status' => 'used'
        ]);

        $codes = $this->service->getUsedCodesByUser($user);

        $this->assertCount(2, $codes);
        foreach ($codes as $code) {
            $this->assertEquals($user->id, $code->used_by);
        }
    }

    public function test_get_available_codes_for_assignment()
    {
        ReferralCode::factory()->count(3)->create(['status' => 'available']);
        ReferralCode::factory()->count(2)->create(['status' => 'assigned']);

        $codes = $this->service->getAvailableCodesForAssignment();

        $this->assertCount(3, $codes);
        foreach ($codes as $code) {
            $this->assertEquals('available', $code->status);
        }
    }

    public function test_expire_unused_codes()
    {
        $expiredDate = now()->subDays(30);

        ReferralCode::factory()->count(2)->create([
            'status' => 'available',
            'expires_at' => $expiredDate
        ]);

        ReferralCode::factory()->count(3)->create([
            'status' => 'available',
            'expires_at' => now()->addDays(30)
        ]);

        $expired = $this->service->expireUnusedCodes();

        $this->assertEquals(2, $expired);
        $this->assertDatabaseCount('referral_codes', 5);

        $this->assertEquals(2, ReferralCode::where('status', 'expired')->count());
    }
}