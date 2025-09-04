<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use App\Services\BinaryTreeService;

class RecruitsTableSeeder extends Seeder
{
    public function run()
    {
        $codes = ReferralCode::where('status', 'assigned')->take(6)->get();

        foreach ($codes as $code) {
            $recruit = User::factory()->create();

            $service = new ReferralCodeService();
            $sponsor = $service->validateAndUseCode($code->code, $recruit);

            if ($sponsor) {
                $binaryService = new BinaryTreeService();
                $binaryService->placeUserInTree($recruit, $sponsor);
            }
        }
    }
}