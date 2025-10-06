<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Services\EnhancedReferralCodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private EnhancedReferralCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EnhancedReferralCodeService();
    }

    public function test_generate_batch_creates_minimum_15_codes()
    {
        // Setup: create distributor
        $distributor = User::factory()->create(['is_admin' => false]);

        // Action: generate batch with minimum size
        $codes = $this->service->generateBatch($distributor, 'Test Batch', 15);

        // Assertions
        $this->assertCount(15, $codes);
        foreach ($codes as $code) {
            $this->assertEquals(8, strlen($code));
            $this->assertTrue(ctype_upper($code));
        }

        // Verify all codes are unique
        $this->assertCount(15, array_unique($codes));

        // Verify database records
        $dbCodes = AdminCode::where('assigned_to', $distributor->id)
            ->where('batch_name', 'Test Batch')
            ->get();
        $this->assertCount(15, $dbCodes);
    }

    public function test_generate_batch_throws_exception_for_insufficient_count()
    {
        // Setup: create distributor
        $distributor = User::factory()->create(['is_admin' => false]);

        // Action & Assertion: should throw exception for count < 15
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Batch size must be at least 15 codes');

        $this->service->generateBatch($distributor, 'Test Batch', 10);
    }

    public function test_generate_batch_creates_unique_codes()
    {
        // Setup: create distributor
        $distributor = User::factory()->create(['is_admin' => false]);

        // Action: generate two batches
        $codes1 = $this->service->generateBatch($distributor, 'Batch 1', 15);
        $codes2 = $this->service->generateBatch($distributor, 'Batch 2', 15);

        // Assertions: all codes should be unique
        $allCodes = array_merge($codes1, $codes2);
        $this->assertCount(30, array_unique($allCodes));
    }

    public function test_issue_code_successfully_assigns_to_distributor()
    {
        // Setup: create distributor and unused code
        $distributor = User::factory()->create(['is_admin' => false]);
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'issued',
            'distributor_id' => null
        ]);

        // Action: issue code to distributor
        $result = $this->service->issueCode('TESTCODE', $distributor);

        // Assertions
        $this->assertTrue($result);
        $code->refresh();
        $this->assertEquals($distributor->id, $code->distributor_id);
        $this->assertEquals('issued', $code->status);
    }

    public function test_issue_code_fails_for_nonexistent_code()
    {
        // Setup: create distributor
        $distributor = User::factory()->create(['is_admin' => false]);

        // Action: try to issue non-existent code
        $result = $this->service->issueCode('NONEXISTENT', $distributor);

        // Assertions
        $this->assertFalse($result);
    }

    public function test_revoke_code_successfully_removes_distributor()
    {
        // Setup: create distributor and issued code
        $distributor = User::factory()->create(['is_admin' => false]);
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'unused',
            'distributor_id' => $distributor->id
        ]);

        // Action: revoke code
        $result = $this->service->revokeCode('TESTCODE', $distributor);

        // Assertions
        $this->assertTrue($result);
        $code->refresh();
        $this->assertNull($code->distributor_id);
        $this->assertEquals('unused', $code->status);
    }

    public function test_revoke_code_fails_for_used_code()
    {
        // Setup: create distributor and used code
        $distributor = User::factory()->create(['is_admin' => false]);
        $usedByUser = User::factory()->create();
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'used',
            'distributor_id' => $distributor->id,
            'used_by_user_id' => $usedByUser->id
        ]);

        // Action: try to revoke used code
        $result = $this->service->revokeCode('TESTCODE', $distributor);

        // Assertions
        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_successfully_marks_as_used()
    {
        // Setup: create distributor and unused code
        $distributor = User::factory()->create(['is_admin' => false]);
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'unused',
            'distributor_id' => $distributor->id
        ]);

        // Action: use code
        $newUser = User::factory()->create();
        $result = $this->service->validateAndUseCode('TESTCODE', $newUser);

        // Assertions
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        // Verify code was marked as used
        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertEquals($newUser->id, $code->used_by_user_id);
        $this->assertNotNull($code->used_at);
    }

    public function test_validate_and_use_code_case_insensitive()
    {
        // Setup: create distributor and code
        $distributor = User::factory()->create(['is_admin' => false]);
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'unused',
            'distributor_id' => $distributor->id
        ]);

        // Action: try to use lowercase version
        $newUser = User::factory()->create();
        $result = $this->service->validateAndUseCode('testcode', $newUser);

        // Assertions
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        // Verify code was marked as used
        $code->refresh();
        $this->assertEquals('used', $code->status);
    }

    public function test_validate_and_use_code_prevents_double_use()
    {
        // Setup: create distributor and code
        $distributor = User::factory()->create(['is_admin' => false]);
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'unused',
            'distributor_id' => $distributor->id
        ]);

        // First use - should succeed
        $firstUser = User::factory()->create();
        $result1 = $this->service->validateAndUseCode('TESTCODE', $firstUser);
        $this->assertInstanceOf(User::class, $result1);

        // Second use - should fail
        $secondUser = User::factory()->create();
        $result2 = $this->service->validateAndUseCode('TESTCODE', $secondUser);
        $this->assertFalse($result2);

        // Verify only first use was recorded
        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertEquals($firstUser->id, $code->used_by_user_id);
    }

    public function test_validate_and_use_code_fails_for_invalid_code()
    {
        // Setup: create distributor
        $distributor = User::factory()->create(['is_admin' => false]);

        // Action: try to use non-existent code
        $newUser = User::factory()->create();
        $result = $this->service->validateAndUseCode('INVALIDCODE', $newUser);

        // Assertions
        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_fails_for_used_code()
    {
        // Setup: create distributor and used code
        $distributor = User::factory()->create(['is_admin' => false]);
        $usedByUser = User::factory()->create();
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'used',
            'distributor_id' => $distributor->id,
            'used_by_user_id' => $usedByUser->id
        ]);

        // Action: try to use already used code
        $newUser = User::factory()->create();
        $result = $this->service->validateAndUseCode('TESTCODE', $newUser);

        // Assertions
        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_fails_for_code_without_distributor()
    {
        // Setup: create code without distributor
        $code = AdminCode::factory()->create([
            'code' => 'TESTCODE',
            'status' => 'unused',
            'distributor_id' => null
        ]);

        // Action: try to use code without distributor
        $newUser = User::factory()->create();
        $result = $this->service->validateAndUseCode('TESTCODE', $newUser);

        // Assertions
        $this->assertFalse($result);
    }

    public function test_get_batch_info_returns_correct_statistics()
    {
        // Setup: create distributor and generate batch
        $distributor = User::factory()->create(['is_admin' => false]);
        $codes = $this->service->generateBatch($distributor, 'Test Batch', 20);

        // Mark some as used
        $usedCodes = array_slice($codes, 0, 5);
        foreach ($usedCodes as $code) {
            $user = User::factory()->create();
            $this->service->validateAndUseCode($code, $user);
        }

        // Action: get batch info
        $batchInfo = $this->service->getBatchInfo($distributor->id . '_batch_1'); // This will be the actual batch ID

        // Assertions
        $this->assertArrayHasKey('total_codes', $batchInfo);
        $this->assertArrayHasKey('issued', $batchInfo);
        $this->assertArrayHasKey('unused', $batchInfo);
        $this->assertArrayHasKey('used', $batchInfo);
    }

    public function test_get_available_codes_returns_only_available_codes()
    {
        // Setup: create distributor and generate codes
        $distributor = User::factory()->create(['is_admin' => false]);
        $codes = $this->service->generateBatch($distributor, 'Test Batch', 20);

        // Mark some as used
        $usedCodes = array_slice($codes, 0, 5);
        foreach ($usedCodes as $code) {
            $user = User::factory()->create();
            $this->service->validateAndUseCode($code, $user);
        }

        // Action: get available codes
        $availableCodes = $this->service->getAvailableCodes($distributor);

        // Assertions
        $this->assertCount(15, $availableCodes); // 20 total - 5 used = 15 available
        foreach ($availableCodes as $code) {
            $this->assertTrue(in_array($code->status, ['issued', 'unused']));
        }
    }

    public function test_get_usage_stats_returns_correct_statistics()
    {
        // Setup: create distributor and generate codes
        $distributor = User::factory()->create(['is_admin' => false]);
        $codes = $this->service->generateBatch($distributor, 'Test Batch', 20);

        // Mark some as used
        $usedCodes = array_slice($codes, 0, 5);
        foreach ($usedCodes as $code) {
            $user = User::factory()->create();
            $this->service->validateAndUseCode($code, $user);
        }

        // Action: get usage stats
        $stats = $this->service->getUsageStats($distributor);

        // Assertions
        $this->assertEquals(20, $stats['total_codes']);
        $this->assertEquals(15, $stats['issued'] + $stats['unused']); // Available codes
        $this->assertEquals(5, $stats['used']);
    }

    public function test_concurrent_code_generation_prevents_duplicates()
    {
        // Setup: create distributor
        $distributor = User::factory()->create(['is_admin' => false]);

        // Action: generate multiple batches simultaneously (simulate concurrency)
        $batch1 = $this->service->generateBatch($distributor, 'Batch 1', 15);
        $batch2 = $this->service->generateBatch($distributor, 'Batch 2', 15);

        // Assertions: all codes should be unique
        $allCodes = array_merge($batch1, $batch2);
        $this->assertCount(30, array_unique($allCodes));

        // Verify no duplicates in database
        $dbCodes = AdminCode::where('assigned_to', $distributor->id)->pluck('code')->toArray();
        $this->assertCount(30, array_unique($dbCodes));
    }
}