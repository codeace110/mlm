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

    public function payment(Request $request, Package $package)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $quantity = $request->quantity;
        $totalAmount = $package->price * $quantity;

        return view('packages.payment', compact('package', 'quantity', 'totalAmount'));
    }

    public function purchase(Request $request, Package $package)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'method' => 'required|in:cebuana_lhuillier,mlhuillier,palawan_pawnshop,gcash,paymaya',
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_province' => 'nullable|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:500',
            'terms' => 'required|accepted',
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

        // Update user's shipping information
        $user->update([
            'shipping_name' => $request->shipping_name,
            'phone' => $request->shipping_phone,
            'address' => $request->shipping_address,
            'city' => $request->shipping_city,
            'province' => $request->shipping_province,
            'postal_code' => $request->shipping_postal_code,
        ]);

        // Prepare shipping information (use updated user data)
        $shippingInfo = [
            'name' => $user->shipping_name,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'province' => $user->province,
            'postal_code' => $user->postal_code,
        ];

        // Prepare account details with shipping and payment info
        $accountDetails = [
            'shipping' => $shippingInfo,
            'payment_method' => $request->method,
            'notes' => $request->notes ?? '',
            'special_instructions' => $request->notes ?? '',
        ];

        // Create package purchase request (balance will be deducted on approval)
        \App\Models\PackagePurchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'method' => $request->method,
            'account_details' => $accountDetails,
            'status' => 'pending',
        ]);

        return redirect()->route('packages.index')->with('success',
            "Package purchase request submitted successfully! Your request for ₱" . number_format($totalAmount, 2) . " will be reviewed by an admin. You will receive payment instructions via email/phone.");
    }
}