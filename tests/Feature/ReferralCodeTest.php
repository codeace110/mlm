<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use App\Services\EnhancedReferralCodeService;

class ReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_referral_codes()
    {
        $admin = User::factory()->create();
        $service = new EnhancedReferralCodeService();

        $codes = $service->generateBatch($admin, 5);

        $this->assertCount(5, $codes);
        foreach ($codes as $code) {
            $this->assertEquals('available', AdminCode::where('code', $code)->first()->status);
            $this->assertEquals($admin->id, AdminCode::where('code', $code)->first()->generated_by);
        }
    }

    public function test_assign_code_to_distributor()
    {
        $admin = User::factory()->create();
        $distributor = User::factory()->create();
        $service = new EnhancedReferralCodeService();

        $codes = $service->generateBatch($admin, 1);
        $code = AdminCode::where('code', $codes[0])->first();

        $service->assignCodeToDistributor($code, $distributor);

        $code->refresh();
        $this->assertEquals('assigned', $code->status);
        $this->assertEquals($distributor->id, $code->distributor_id);
    }

    public function test_validate_and_use_code()
    {
        $admin = User::factory()->create();
        $distributor = User::factory()->create();
        $newUser = User::factory()->create();
        $service = new EnhancedReferralCodeService();

        $codes = $service->generateBatch($admin, 1);
        $code = AdminCode::where('code', $codes[0])->first();
        $service->assignCodeToDistributor($code, $distributor);

        $result = $service->validateAndUseCode($code->code, $newUser);

        $this->assertEquals($distributor->id, $result->id);
        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertEquals($newUser->id, $code->used_by_user_id);
    }

    public function test_invalid_code_validation()
    {
        $service = new EnhancedReferralCodeService();
        $result = $service->validateAndUseCode('INVALID', User::factory()->create());
        $this->assertNull($result);
    }

    public function test_uuid_based_registration_flow()
    {
        // Create a distributor with a UUID-based referral code
        $distributor = User::factory()->create();
        $distributorReferralCode = $distributor->referral_code;

        // Verify the referral code format (AKEN + 15 characters)
        $this->assertMatchesRegularExpression('/^AKEN[A-F0-9]{15}$/', $distributorReferralCode);

        // Create a new user who will register with the distributor's code
        $newUser = User::factory()->create();

        // Test the UUID-based code validation
        $service = new EnhancedReferralCodeService();
        $result = $service->validateAndUseCode($distributorReferralCode, $newUser);

        // Should return null because UUID codes are handled differently in registration
        $this->assertNull($result);
    }

    public function test_complete_uuid_registration_workflow()
    {
        // 1. Admin generates codes
        $admin = User::factory()->create(['is_admin' => true]);
        $service = new EnhancedReferralCodeService();

        $generatedCodes = $service->generateBatch($admin, 3, 'Test UUID Batch', 30);

        // Verify codes are in correct format (AKEN + 15 characters)
        foreach ($generatedCodes as $code) {
            $this->assertMatchesRegularExpression('/^AKEN[A-F0-9]{15}$/', $code);
        }

        // 2. Admin assigns a code to a distributor
        $distributor = User::factory()->create();
        $codeToAssign = AdminCode::where('code', $generatedCodes[0])->first();

        $service->assignCodeToDistributor($codeToAssign, $distributor);

        // Verify assignment
        $codeToAssign->refresh();
        $this->assertEquals('assigned', $codeToAssign->status);
        $this->assertEquals($distributor->id, $codeToAssign->distributor_id);

        // 3. New user registers with the distributor's UUID code
        $newUserData = [
            'name' => 'New Registrant',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => $distributor->referral_code, // Using distributor's UUID
            'preferred_side' => 'left'
        ];

        // Simulate registration process
        $response = $this->post(route('register'), $newUserData);

        // Should redirect to dashboard after successful registration
        $response->assertRedirect('/dashboard');

        // Verify user was created and linked to distributor
        $newUser = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals($distributor->id, $newUser->sponsor_id);

        // Verify binary tree was created
        $this->assertNotNull($newUser->binaryTree);

        // Verify the distributor's UUID code format
        $this->assertMatchesRegularExpression('/^AKEN[A-F0-9]{15}$/', $distributor->referral_code);
        $this->assertMatchesRegularExpression('/^AKEN[A-F0-9]{15}$/', $newUser->referral_code);
    }
}