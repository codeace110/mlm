<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;

class ReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_referral_codes()
    {
        $admin = User::factory()->create();
        $service = new ReferralCodeService();

        $codes = $service->generateCodes($admin, 5);

        $this->assertCount(5, $codes);
        foreach ($codes as $code) {
            $this->assertEquals('available', $code->status);
            $this->assertEquals($admin->id, $code->generated_by);
        }
    }

    public function test_assign_code_to_distributor()
    {
        $admin = User::factory()->create();
        $distributor = User::factory()->create();
        $service = new ReferralCodeService();

        $codes = $service->generateCodes($admin, 1);
        $code = $codes[0];

        $service->assignCodeToDistributor($code, $distributor);

        $code->refresh();
        $this->assertEquals('assigned', $code->status);
        $this->assertEquals($distributor->id, $code->assigned_to);
    }

    public function test_validate_and_use_code()
    {
        $admin = User::factory()->create();
        $distributor = User::factory()->create();
        $newUser = User::factory()->create();
        $service = new ReferralCodeService();

        $codes = $service->generateCodes($admin, 1);
        $code = $codes[0];
        $service->assignCodeToDistributor($code, $distributor);

        $result = $service->validateAndUseCode($code->code, $newUser);

        $this->assertEquals($distributor->id, $result->id);
        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertEquals($newUser->id, $code->used_by);
    }

    public function test_invalid_code_validation()
    {
        $service = new ReferralCodeService();
        $result = $service->validateAndUseCode('INVALID', null);
        $this->assertFalse($result);
    }
}