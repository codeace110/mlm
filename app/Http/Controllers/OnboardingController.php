<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class OnboardingController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // If user has completed onboarding, redirect to dashboard
        if ($user->phone && $user->address && $user->city && $user->province) {
            return redirect()->route('dashboard');
        }

        return view('auth.onboarding');
    }

    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'shipping_name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $data = $request->only([
            'phone',
            'address',
            'city',
            'province',
            'postal_code',
            'shipping_name'
        ]);

        if ($request->hasFile('profile_image')) {
            $imageName = time() . '.' . $request->profile_image->extension();
            $request->profile_image->move(public_path('images/profiles'), $imageName);
            $data['profile_image'] = 'images/profiles/' . $imageName;
        }

        // Ensure profile_image is included in the update even if no new image is uploaded
        if (!isset($data['profile_image']) && !$request->hasFile('profile_image')) {
            // Keep existing profile image if no new one is uploaded
            $data['profile_image'] = $user->profile_image;
        }

        $user->update($data);

        return redirect()->route('dashboard')->with('success', 'Profile completed successfully! Welcome to AKEN MLM.');
    }
}