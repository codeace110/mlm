<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;

class ReferralCodesTableSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('is_admin', true)->first();
        if (!$admin) {
            $admin = User::factory()->create(['is_admin' => true]);
        }

        $distributors = User::where('is_admin', false)->take(2)->get();
        if ($distributors->count() < 2) {
            $distributors = collect();
            for ($i = 0; $i < 2; $i++) {
                $distributors->push(User::factory()->create());
            }
        }

        $service = new ReferralCodeService();
        $codes = $service->generateCodes($admin, 10);

        foreach ($codes as $index => $code) {
            $distributor = $distributors->get($index % 2);
            $service->assignCodeToDistributor($code, $distributor);
        }
    }
}