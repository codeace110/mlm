<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCode;
use App\Models\User;
use App\Services\AdminCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class AdminCodeController extends Controller
{
    protected $adminCodeService;

    public function __construct(AdminCodeService $adminCodeService)
    {
        $this->adminCodeService = $adminCodeService;
    }

    public function index()
    {
        $codes = AdminCode::with(['distributor', 'usedByUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get statistics using the service
        $stats = [
            'total' => AdminCode::count(),
            'available' => AdminCode::where('status', 'available')->count(),
            'assigned' => AdminCode::where('status', 'assigned')->count(),
            'used' => AdminCode::where('status', 'used')->count(),
        ];

        return view('admin.admin_codes.index', compact('codes', 'stats'));
    }

    public function create()
    {
        return view('admin.admin_codes.create');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:15|max:1000',
            'batch_name' => 'required|string|max:255'
        ]);

        try {
            // Get the current admin user (distributor)
            $distributor = auth()->user();

            // Use the service to generate codes
            $codes = $this->adminCodeService->generateBatch(
                $distributor,
                $request->batch_name,
                $request->count
            );

            return redirect()->route('admin.admin_codes.index')
                ->with('success', "Generated " . count($codes) . " unique admin codes in batch '{$request->batch_name}' successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate admin codes: ' . $e->getMessage());
        }
    }

    public function assign(Request $request, AdminCode $code)
    {
        $request->validate(['distributor_id' => 'required|exists:users,id']);

        try {
            $distributor = User::find($request->distributor_id);

            // Use the service to assign the code
            $success = $this->adminCodeService->issueCode($code->code, $distributor);

            if ($success) {
                return back()->with('success', "Admin code '{$code->code}' assigned to {$distributor->name} successfully.");
            } else {
                return back()->with('error', 'Failed to assign the admin code. It may already be in use.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign admin code: ' . $e->getMessage());
        }
    }

    public function issue(Request $request, AdminCode $code)
    {
        $request->validate(['distributor_id' => 'required|exists:users,id']);

        if ($code->status !== 'available') {
            return back()->with('error', 'Only available codes can be issued to distributors.');
        }

        $distributor = User::find($request->distributor_id);

        DB::transaction(function () use ($code, $distributor) {
            $code->update([
                'distributor_id' => $distributor->id,
                'status' => 'available',
            ]);
        });

        return back()->with('success', "Admin code '{$code->code}' issued to {$distributor->name} successfully.");
    }

    public function revoke(AdminCode $code)
    {
        try {
            // Get the distributor who currently owns the code
            $distributor = $code->distributor;

            if (!$distributor) {
                return back()->with('error', 'Code is not assigned to any distributor.');
            }

            // Use the service to revoke the code
            $success = $this->adminCodeService->revokeCode($code->code, $distributor);

            if ($success) {
                return back()->with('success', "Admin code '{$code->code}' revoked successfully.");
            } else {
                return back()->with('error', 'Failed to revoke the admin code. It may already be used.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to revoke admin code: ' . $e->getMessage());
        }
    }

    public function show(AdminCode $code)
    {
        $code->load(['distributor', 'usedByUser']);
        return view('admin.admin_codes.show', compact('code'));
    }

    public function download(Request $request)
    {
        $batchId = $request->query('batch_id');

        if ($batchId) {
            $codes = AdminCode::where('batch_id', $batchId)->get();
            $batch = AdminCode::where('batch_id', $batchId)->first();
            $filename = "admin_codes_batch_{$batch->batch_name}_{$batchId}.csv";
        } else {
            $codes = AdminCode::all();
            $filename = "all_admin_codes_" . date('Y-m-d_H-i-s') . ".csv";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($codes) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Code', 'Status', 'Batch ID', 'Batch Name', 'Distributor', 'Used By', 'Created At', 'Used At']);

            // CSV data
            foreach ($codes as $code) {
                fputcsv($file, [
                    $code->code,
                    ucfirst($code->status),
                    $code->batch_id ?? '',
                    $code->batch_name ?? '',
                    $code->distributor ? $code->distributor->name : '',
                    $code->usedByUser ? $code->usedByUser->name : '',
                    $code->created_at->format('Y-m-d H:i:s'),
                    $code->used_at ? $code->used_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function batches()
    {
        $batches = AdminCode::select('batch_id', 'batch_name')
            ->whereNotNull('batch_id')
            ->distinct()
            ->orderBy('batch_name')
            ->get();

        return view('admin.admin_codes.batches', compact('batches'));
    }
}
