<?php

namespace Tests\Unit;

use App\Models\AdminCode;
use App\Models\User;
use App\Services\AdminCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AdminCodeService $adminCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminCodeService = new AdminCodeService();
    }

    public function test_validate_and_use_code_successfully()
    {
        $distributor = User::factory()->create();
        $newUser = User::factory()->create();

        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'issued',
        ]);

        $result = $this->adminCodeService->validateAndUseCode($adminCode->code, $newUser);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
        $this->assertEquals($newUser->id, $adminCode->used_by_user_id);
        $this->assertNotNull($adminCode->used_at);
    }

    public function test_validate_and_use_code_without_new_user()
    {
        $distributor = User::factory()->create();

        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'issued',
        ]);

        $result = $this->adminCodeService->validateAndUseCode($adminCode->code);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        $adminCode->refresh();
        $this->assertEquals('issued', $adminCode->status); // Status unchanged
        $this->assertNull($adminCode->used_by_user_id);
        $this->assertNull($adminCode->used_at);
    }

    public function test_validate_and_use_code_fails_for_invalid_code()
    {
        $result = $this->adminCodeService->validateAndUseCode('INVALID_CODE');

        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_fails_for_used_code()
    {
        $distributor = User::factory()->create();
        $newUser1 = User::factory()->create();
        $newUser2 = User::factory()->create();

        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'issued',
        ]);

        // Use the code once
        $this->adminCodeService->validateAndUseCode($adminCode->code, $newUser1);

        // Try to use it again
        $result = $this->adminCodeService->validateAndUseCode($adminCode->code, $newUser2);

        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_fails_for_code_without_distributor()
    {
        $adminCode = AdminCode::factory()->create([
            'distributor_id' => null,
            'status' => 'issued',
        ]);

        $result = $this->adminCodeService->validateAndUseCode($adminCode->code);

        $this->assertFalse($result);
    }

    public function test_validate_and_use_code_works_for_unused_status()
    {
        $distributor = User::factory()->create();
        $newUser = User::factory()->create();

        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'unused',
        ]);

        $result = $this->adminCodeService->validateAndUseCode($adminCode->code, $newUser);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($distributor->id, $result->id);

        $adminCode->refresh();
        $this->assertEquals('used', $adminCode->status);
    }

    public function test_validate_and_use_code_fails_for_used_status()
    {
        $distributor = User::factory()->create();
        $newUser = User::factory()->create();

        $adminCode = AdminCode::factory()->create([
            'distributor_id' => $distributor->id,
            'status' => 'issued',
        ]);

        // Use the code first
        $this->adminCodeService->validateAndUseCode($adminCode->code, $newUser);

        // Try to use it again
        $result = $this->adminCodeService->validateAndUseCode($adminCode->code);

        $this->assertFalse($result, "Used code should not be usable");
    }
}