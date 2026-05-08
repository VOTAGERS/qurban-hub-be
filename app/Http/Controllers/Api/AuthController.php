<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Email not registered in our system.'
        ]);

        $email = $request->email;
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Save OTP
        UserOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(15),
                'status' => 'pending',
                'created_by' => 'system',
                'updated_by' => 'system'
            ]
        );

        // Send Email
        try {
            Mail::to($email)->send(new OtpMail($otpCode));
            return response()->json([
                'success' => true,
                'message' => 'OTP code sent to your email.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please check configuration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp_code' => 'required|string|size:6',
        ]);

        $otpRecord = UserOtp::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.'
            ], 422);
        }

        // Mark as used
        $otpRecord->update(['status' => 'used']);

        // Find user and login
        $user = User::where('email', $request->email)->first();
        
        // Generate Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get roles
        $roles = $user->roles()->get(['role_accesses.role_code', 'role_accesses.role_name']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'roles' => $roles
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // 1. Delete Sanctum Token
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        // 2. Clear Session & Cookie (for SPA stateful auth)
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ])->withCookie(cookie()->forget('laravel_session'))
          ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }

    public function loginWithPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Generate Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get roles
        $roles = $user->roles()->get(['role_accesses.role_code', 'role_accesses.role_name']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'roles' => $roles
            ]
        ]);
    }
}
