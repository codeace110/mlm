<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCode;
use App\Services\EnhancedReferralCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReferralCodeController extends Controller
{
    protected EnhancedReferralCodeService $referralCodeService;

    public function __construct()
    {
        $this->referralCodeService = new EnhancedReferralCodeService();
    }

    public function index()
    {
        $codes = AdminCode::with(['assignedTo', 'generatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = $this->referralCodeService->getCodeStatistics();

        return view('admin.referral_codes.index', compact('codes', 'stats'));
    }

    public function generate(Request $request)
    {
        // Debug logging
        Log::info('Generate codes request received', [
            'user_id' => auth()->id(),
            'is_admin' => auth()->user()->is_admin ?? false,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:50',
            'expiry_days' => 'nullable|integer|min:1|max:365',
            'batch_name' => 'nullable|string|max:100'
        ]);

        try {
            $quantity = $request->quantity;
            $expiryDays = $request->expiry_days;
            $batchName = $request->batch_name ?: 'Custom Batch ' . date('Y-m-d H:i:s');

            Log::info('Generating codes', [
                'quantity' => $quantity,
                'expiry_days' => $expiryDays,
                'batch_name' => $batchName
            ]);

            $codes = $this->referralCodeService->generateBatch(
                auth()->user(),
                $quantity,
                $batchName,
                $expiryDays
            );

            $message = "{$quantity} UUID-based referral codes (AKEN + 15 characters) generated successfully.";

            Log::info('Codes generated successfully', ['count' => count($codes)]);

            if ($request->ajax()) {
                $stats = $this->referralCodeService->getCodeStatistics();
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'stats' => $stats
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error generating codes', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        return view('admin.referral_codes.create');
    }

    public function store(Request $request)
    {
        // Implementation for storing individual codes if needed
        return redirect()->route('admin.referral_codes.index');
    }

    public function edit(AdminCode $referralCode)
    {
        return view('admin.referral_codes.edit', compact('referralCode'));
    }

    public function update(Request $request, AdminCode $referralCode)
    {
        // Implementation for updating codes if needed
        return redirect()->route('admin.referral_codes.index');
    }

    public function destroy(AdminCode $referralCode)
    {
        if ($referralCode->status === 'available') {
            $referralCode->delete();
            return response()->json(['success' => true, 'message' => 'Code deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Cannot delete used or assigned codes.'], 422);
    }

    public function show(AdminCode $referralCode)
    {
        $referralCode->load(['assignedTo', 'generatedBy']);

        return view('admin.referral_codes.show', compact('referralCode'));
    }

    public function assign(Request $request, AdminCode $referralCode)
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
                'code' => $referralCode->fresh()
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
            'codes.*' => 'exists:admin_codes,id'
        ]);

        $codes = AdminCode::with(['assignedTo', 'generatedBy'])
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
                'Assigned To',
                'Generated By',
                'Batch ID',
                'Generated At',
                'Expires At'
            ]);

            // CSV data
            foreach ($codes as $code) {
                fputcsv($file, [
                    $code->code,
                    $code->status,
                    $code->assignedTo ? $code->assignedTo->name : 'N/A',
                    $code->generatedBy ? $code->generatedBy->name : 'N/A',
                    $code->batch_id ?: 'N/A',
                    $code->created_at->format('Y-m-d H:i:s'),
                    $code->expires_at ? $code->expires_at->format('Y-m-d H:i:s') : 'N/A'
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
            'codes.*' => 'exists:admin_codes,id',
            'distributor_id' => 'required|exists:users,id'
        ]);

        $distributor = \App\Models\User::find($request->distributor_id);
        $codeIds = $request->codes;

        $result = $this->referralCodeService->assignCodesToDistributor($distributor, $codeIds);

        return response()->json([
            'success' => true,
            'message' => "Successfully assigned {$result['assigned']} codes to {$distributor->name}."
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'codes' => 'required|array',
            'codes.*' => 'exists:admin_codes,id'
        ]);

        $deleted = 0;

        foreach ($request->codes as $codeId) {
            $code = AdminCode::find($codeId);
            if ($code && in_array($code->status, ['available', 'expired'])) {
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