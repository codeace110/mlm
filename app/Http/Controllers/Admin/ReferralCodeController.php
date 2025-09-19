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

            if ($request->ajax()) {
                $stats = $this->referralCodeService->getCodeStatistics();
                return response()->json([
                    'success' => true,
                    'message' => '50 referral codes generated successfully.',
                    'stats' => $stats
                ]);
            }

            return back()->with('success', '50 referral codes generated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Referral code assigned successfully.',
                'code' => $referralCode->fresh(['assignedTo'])
            ]);
        }

        return back()->with('success', 'Referral code assigned successfully.');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $users = \App\Models\User::where('is_admin', false)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function getStatistics()
    {
        $stats = $this->referralCodeService->getCodeStatistics();
        return response()->json($stats);
    }

    public function bulkExport(Request $request)
    {
        $request->validate([
            'codes' => 'required|array',
            'codes.*' => 'exists:referral_codes,id'
        ]);

        $codes = ReferralCode::with(['assignedTo', 'usedBy', 'generatedBy'])
            ->whereIn('id', $request->codes)
            ->get();

        $filename = 'referral_codes_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($codes) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Code',
                'Status',
                'Used By',
                'Assigned To',
                'Batch ID',
                'Generated At',
                'Used At'
            ]);

            // CSV data
            foreach ($codes as $code) {
                fputcsv($file, [
                    $code->code,
                    $code->status,
                    $code->usedBy ? $code->usedBy->name : 'N/A',
                    $code->assignedTo ? $code->assignedTo->name : 'N/A',
                    $code->batch_id ?: 'N/A',
                    $code->created_at->format('Y-m-d H:i:s'),
                    $code->used_at ? $code->used_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'codes' => 'required|array',
            'codes.*' => 'exists:referral_codes,id',
            'distributor_id' => 'required|exists:users,id'
        ]);

        $distributor = \App\Models\User::find($request->distributor_id);
        $assigned = 0;

        foreach ($request->codes as $codeId) {
            $code = ReferralCode::find($codeId);
            if ($code && $code->status === 'available') {
                $this->referralCodeService->assignCodeToDistributor($code, $distributor);
                $assigned++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully assigned {$assigned} codes to {$distributor->name}."
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'codes' => 'required|array',
            'codes.*' => 'exists:referral_codes,id'
        ]);

        $deleted = 0;

        foreach ($request->codes as $codeId) {
            $code = ReferralCode::find($codeId);
            if ($code && $code->status === 'available') {
                $code->delete();
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} codes."
        ]);
    }
}