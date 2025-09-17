<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use Illuminate\Http\Request;

class ReferralCodeController extends Controller
{
    protected ReferralCodeService $referralCodeService;

    public function __construct(ReferralCodeService $referralCodeService)
    {
        $this->referralCodeService = $referralCodeService;
    }

    public function index()
    {
        $codes = ReferralCode::with(['assignedTo', 'usedBy', 'generatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = $this->referralCodeService->getCodeStatistics();

        return view('admin.referral_codes.index', compact('codes', 'stats'));
    }

    public function generate(Request $request)
    {
        try {
            $codes = $this->referralCodeService->generateCodes(auth()->user(), 50);

            return back()->with('success', '50 referral codes generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(ReferralCode $referralCode)
    {
        $referralCode->load(['assignedTo', 'usedBy', 'generatedBy']);

        return view('admin.referral_codes.show', compact('referralCode'));
    }

    public function assign(Request $request, ReferralCode $referralCode)
    {
        $request->validate([
            'distributor_id' => 'required|exists:users,id',
        ]);

        $distributor = \App\Models\User::find($request->distributor_id);

        $this->referralCodeService->assignCodeToDistributor($referralCode, $distributor);

        return back()->with('success', 'Referral code assigned successfully.');
    }
}