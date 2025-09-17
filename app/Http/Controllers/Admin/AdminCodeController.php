<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCodeController extends Controller
{
    public function index()
    {
        $codes = AdminCode::with(['distributor', 'usedByUser'])->paginate(20);
        return view('admin.admin_codes.index', compact('codes'));
    }

    public function generate(Request $request)
    {
        $request->validate(['count' => 'required|integer|min:1|max:1000']);

        $codes = [];
        for ($i = 0; $i < $request->count; $i++) {
            $code = Str::upper(Str::random(8));
            $codes[] = AdminCode::create([
                'code' => $code,
                'status' => 'issued',
            ]);
        }

        return back()->with('success', "{$request->count} admin codes generated successfully.");
    }

    public function assign(Request $request, AdminCode $code)
    {
        $request->validate(['distributor_id' => 'required|exists:users,id']);

        $distributor = User::find($request->distributor_id);
        $code->update([
            'distributor_id' => $distributor->id,
            'status' => 'unused',
        ]);

        return back()->with('success', 'Admin code assigned successfully.');
    }

    public function show(AdminCode $code)
    {
        $code->load(['distributor', 'usedByUser']);
        return view('admin.admin_codes.show', compact('code'));
    }
}
