<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminCode;
use App\Models\BinaryTree;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\BinaryBalancerService;
use App\Services\EnhancedReferralCodeService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'registration_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Accept both UUID format (AKEN + 15 chars) and admin codes (8 chars)
                    if (!preg_match('/^AKEN[A-F0-9]{15}$/i', $value) && strlen($value) != 8) {
                        $fail('The referral code must be a valid UUID (AKEN + 15 characters) or an 8-character admin code.');
                    }
                },
            ],
            'preferred_side' => 'nullable|in:left,right',
        ]);

        try {
            DB::beginTransaction();

            // Create the user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'registration_code' => $request->registration_code,
            ]);

            // Use EnhancedReferralCodeService to validate and use the code
            $referralCodeService = new EnhancedReferralCodeService();

            // Check if it's a UUID-based code (AKEN + 15 chars) or a regular admin code
            if (preg_match('/^AKEN[A-F0-9]{15}$/i', $request->registration_code)) {
                // It's a UUID-based code, find the distributor who owns this code
                $distributor = User::where('referral_code', $request->registration_code)->first();
                if (!$distributor) {
                    $user->delete();
                    return back()->withErrors(['registration_code' => 'Invalid referral code. Please contact your sponsor for a valid code.'])->withInput();
                }
                $sponsor = $distributor;
            } else {
                // It's a regular admin code, use the existing service
                $sponsor = $referralCodeService->validateAndUseCode($request->registration_code, $user);

                if (!$sponsor) {
                    $user->delete(); // Clean up user if code validation fails
                    return back()->withErrors(['registration_code' => 'Invalid or expired registration code. Please contact your sponsor for a valid code.'])->withInput();
                }
            }

            // Update user with sponsor information and preferred side
            $user->update([
                'sponsor_id' => $sponsor->id,
                'placement_side' => $request->preferred_side ?: 'left',
            ]);

            // Create binary tree record for the user
            BinaryTree::create([
                'user_id' => $user->id,
                'total_left_volume' => 0,
                'total_right_volume' => 0,
                'left_consumed' => 0,
                'right_consumed' => 0,
                'level_index' => 1,
                'reward_count' => 0,
                'direct_pairs_paid' => 0,
                'spillover_pairs_paid' => 0,
                'left_spillover' => 0,
                'right_spillover' => 0,
            ]);

            // Place user in binary tree using BinaryBalancerService with preferred side
            $binaryBalancerService = new BinaryBalancerService();
            $binaryBalancerService->placeUser($user, $sponsor, $request->preferred_side);

            // Process downline quotas for uplines and calculate bonuses
            $binaryBalancerService->processDownlineQuotasForUplines($user);
            $binaryBalancerService->calculateDirectBonus($sponsor);
            $binaryBalancerService->calculateSpilloverBonus($sponsor);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect(RouteServiceProvider::HOME);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_email' => $request->email,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return back()->withErrors(['registration_code' => 'Registration failed. Please try again. Error: ' . $e->getMessage()])->withInput();
        }
    }
}

