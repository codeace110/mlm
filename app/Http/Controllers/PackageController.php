<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::where('is_active', true)->get();

        return view('packages', compact('packages'));
    }

    public function show(Package $package)
    {
        // Get related packages (similar price range)
        $relatedPackages = Package::where('is_active', true)
            ->where('id', '!=', $package->id)
            ->whereBetween('price', [$package->price * 0.8, $package->price * 1.2])
            ->take(3)
            ->get();

        return view('packages.show', compact('package', 'relatedPackages'));
    }

    public function purchase(Request $request, Package $package)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $user = Auth::user();
        $quantity = $request->quantity;
        $totalAmount = $package->price * $quantity;

        // Check if user has sufficient balance
        if ($user->account_balance < $totalAmount) {
            return redirect()->back()->with('error',
                'Insufficient balance. You need ₱' . number_format($totalAmount, 2) .
                ' but only have ₱' . number_format($user->account_balance, 2));
        }

        // Process purchase (in a real app, you'd create an order record)
        $user->decrement('account_balance', $totalAmount);

        // You could create a purchase/order record here
        // Purchase::create([...]);

        return redirect()->route('packages.index')->with('success',
            "Successfully purchased {$quantity}x {$package->name} for ₱" . number_format($totalAmount, 2));
    }
}