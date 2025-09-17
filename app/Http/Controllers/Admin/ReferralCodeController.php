<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\ReferralCodeService;

class ReferralCodeController extends Controller
{
    public function index()
    {
        $codes = ReferralCode::with(['assignedTo', 'usedBy', 'generatedBy'])->paginate(20);
        return view('admin.referral_codes.index', compact('codes'));
    }

    public function generate(Request $request)
    {
        $request->validate(['count' => 'required|integer|min:1|max:1000']);

        $service = new ReferralCodeService();
        $codes = $service->generateCodes(auth()->user(), $request->count);

        return back()->with('success', "{$request->count} referral codes generated successfully.");
    }

    public function assign(Request $request, ReferralCode $code)
    {
        $request->validate(['distributor_id' => 'required|exists:users,id']);

        $distributor = User::find($request->distributor_id);
        $service = new ReferralCodeService();
        $service->assignCodeToDistributor($code, $distributor);

        return back()->with('success', 'Referral code assigned successfully.');
    }

    public function show(ReferralCode $code)
    {
        $code->load(['assignedTo', 'usedBy', 'generatedBy']);
        return view('admin.referral_codes.show', compact('code'));
    }
}