<?php

namespace Tests\Feature;

use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCodeUniquenessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test case-insensitive uniqueness enforcement for admin codes
     */
    public function test_admin_codes_enforce_case_insensitive_uniqueness()
    {
        // Create first admin code
        $code1 = AdminCode::create([
            'code' => 'TESTCODE',
            'tracker' => 'TRACKER001',
            'batch_id' => 'BATCH001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => User::factory()->create()->id,
            'issued_at' => now(),
            'notes' => 'Test code 1',
        ]);

        $this->assertDatabaseHas('admin_codes', [
            'code' => 'TESTCODE',
        ]);

        // Try to create another code with different case - should fail
        try {
            $code2 = AdminCode::create([
                'code' => 'testcode', // lowercase
                'tracker' => 'TRACKER002',
                'batch_id' => 'BATCH002',
                'status' => 'issued',
                'issued_to_user_id' => null,
                'issued_by_admin_id' => User::factory()->create()->id,
                'issued_at' => now(),
                'notes' => 'Test code 2',
            ]);

            $this->fail('Expected exception for duplicate admin code (case-insensitive)');
        } catch (\Exception $e) {
            // Expected - should throw unique constraint violation
            $this->assertStringContains('Duplicate entry', $e->getMessage());
        }

        // Try to create another code with mixed case - should fail
        try {
            $code3 = AdminCode::create([
                'code' => 'TestCode', // mixed case
                'tracker' => 'TRACKER003',
                'batch_id' => 'BATCH003',
                'status' => 'issued',
                'issued_to_user_id' => null,
                'issued_by_admin_id' => User::factory()->create()->id,
                'issued_at' => now(),
                'notes' => 'Test code 3',
            ]);

            $this->fail('Expected exception for duplicate admin code (case-insensitive)');
        } catch (\Exception $e) {
            // Expected - should throw unique constraint violation
            $this->assertStringContains('Duplicate entry', $e->getMessage());
        }

        // Verify only one record exists
        $this->assertEquals(1, AdminCode::where('code', 'TESTCODE')->count());
    }

    /**
     * Test that model-level case conversion works
     */
    public function test_admin_code_model_converts_to_uppercase()
    {
        $code = AdminCode::create([
            'code' => 'lowercase',
            'tracker' => 'TRACKER001',
            'batch_id' => 'BATCH001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => User::factory()->create()->id,
            'issued_at' => now(),
            'notes' => 'Test lowercase conversion',
        ]);

        // Verify it's stored in uppercase
        $this->assertEquals('LOWERCASE', $code->code);
        $this->assertDatabaseHas('admin_codes', [
            'code' => 'LOWERCASE',
        ]);
    }

    /**
     * Test AdminCode::codeExists method
     */
    public function test_code_exists_method_is_case_insensitive()
    {
        AdminCode::create([
            'code' => 'UNIQUECODE',
            'tracker' => 'TRACKER001',
            'batch_id' => 'BATCH001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => User::factory()->create()->id,
            'issued_at' => now(),
            'notes' => 'Test uniqueness check',
        ]);

        // Test case-insensitive existence check
        $this->assertTrue(AdminCode::codeExists('uniquecode'));
        $this->assertTrue(AdminCode::codeExists('UNIQUeCODE'));
        $this->assertTrue(AdminCode::codeExists('UniqueCode'));
        $this->assertFalse(AdminCode::codeExists('nonexistent'));
    }

    /**
     * Test AdminCode::findByCode method
     */
    public function test_find_by_code_method_is_case_insensitive()
    {
        $originalCode = AdminCode::create([
            'code' => 'FINDTEST',
            'tracker' => 'TRACKER001',
            'batch_id' => 'BATCH001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => User::factory()->create()->id,
            'issued_at' => now(),
            'notes' => 'Test find by code',
        ]);

        // Test case-insensitive find
        $foundCode1 = AdminCode::findByCode('findtest');
        $foundCode2 = AdminCode::findByCode('FINDTEST');
        $foundCode3 = AdminCode::findByCode('FindTest');

        $this->assertNotNull($foundCode1);
        $this->assertNotNull($foundCode2);
        $this->assertNotNull($foundCode3);
        $this->assertEquals($originalCode->id, $foundCode1->id);
        $this->assertEquals($originalCode->id, $foundCode2->id);
        $this->assertEquals($originalCode->id, $foundCode3->id);
    }

    /**
     * Test AdminCode::generateUniqueCode method
     */
    public function test_generate_unique_code_creates_case_insensitive_unique_codes()
    {
        // Create some existing codes
        AdminCode::create([
            'code' => 'GEN001',
            'tracker' => 'TRACKER001',
            'batch_id' => 'BATCH001',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => User::factory()->create()->id,
            'issued_at' => now(),
            'notes' => 'Existing code 1',
        ]);

        AdminCode::create([
            'code' => 'GEN002',
            'tracker' => 'TRACKER002',
            'batch_id' => 'BATCH002',
            'status' => 'issued',
            'issued_to_user_id' => null,
            'issued_by_admin_id' => User::factory()->create()->id,
            'issued_at' => now(),
            'notes' => 'Existing code 2',
        ]);

        // Generate unique codes
        $uniqueCode1 = AdminCode::generateUniqueCode();
        $uniqueCode2 = AdminCode::generateUniqueCode();

        $this->assertNotEquals($uniqueCode1, $uniqueCode2);
        $this->assertFalse(AdminCode::codeExists($uniqueCode1));
        $this->assertFalse(AdminCode::codeExists($uniqueCode2));

        // Verify they are uppercase
        $this->assertEquals(strtoupper($uniqueCode1), $uniqueCode1);
        $this->assertEquals(strtoupper($uniqueCode2), $uniqueCode2);
    }
}