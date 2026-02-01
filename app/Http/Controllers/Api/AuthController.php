<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationCode;

class AuthController extends Controller
{

    /**
     * Send password reset code to user's email
     */
    public function sendResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            // For security, do not reveal if user exists
            return response()->json(['message' => 'If the email exists, a reset code has been sent.'], 200);
        }

        $code = random_int(100000, 999999);
        $user->password_reset_code = $code;
        $user->save();

        try {
            Mail::to($user->email)->send(new EmailVerificationCode($code));
        } catch (\Exception $e) {
            // Email sending failure shouldn't fail the request
        }
return response()->json(['message' => 'If the email exists, a reset code has been sent.'], 200);
    }

    /**
     * Reset password using code
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user || $user->password_reset_code !== $validated['code']) {
            return response()->json(['message' => 'Invalid code or email'], 422);
        }

        $user->password = $validated['password'];
        $user->password_reset_code = null;
        $user->save();
return response()->json(['message' => 'Password reset successful']);
    }

    /**
     * Send password reset code to admin's email
     */
    public function sendAdminResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])
                   ->where('role', 'admin')
                   ->first();
                   
        if (!$user) {
            // For security, do not reveal if admin exists
            return response()->json(['message' => 'If the admin email exists, a reset code has been sent.'], 200);
        }

        $code = random_int(100000, 999999);
        $user->password_reset_code = $code;
        $user->save();

        try {
            Mail::to($user->email)->send(new EmailVerificationCode($code));
        } catch (\Exception $e) {
            // Email sending failure shouldn't fail the request
        }
return response()->json(['message' => 'If the admin email exists, a reset code has been sent.'], 200);
    }

    /**
     * Reset admin password using code
     */
    public function resetAdminPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])
                   ->where('role', 'admin')
                   ->first();
                   
        if (!$user || $user->password_reset_code !== $validated['code']) {
            return response()->json(['message' => 'Invalid code or email'], 422);
        }

        $user->password = $validated['password'];
        $user->password_reset_code = null;
        $user->save();
return response()->json(['message' => 'Admin password reset successful']);
    }
    /**
     * Register customer with password (traditional signup)
     */
    public function registerCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Generate 6-digit code
        $code = random_int(100000, 999999);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'customer',
            'email_verification_code' => $code,
            'email_verification_code_created_at' => now(),
        ]);
        // Send code to email
        try {
            Mail::to($user->email)->send(new \App\Mail\EmailVerificationCode($code));
        } catch (\Exception $e) {
            // Email sending failure shouldn't fail the registration
        }
return response()->json([
            'message' => 'Verification code sent to email',
            'user_id' => $user->id,
        ], 201);
    }

    /**
     * Verify email code for customer
     */
    public function verifyEmailCode(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|size:6',
        ]);

        $user = User::find($validated['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$user->email_verification_code) {
            return response()->json(['message' => 'No verification code found. Please request a new one.'], 422);
        }

        if ($user->email_verification_code !== $validated['code']) {
            return response()->json(['message' => 'Invalid verification code'], 422);
        }

        // Check if code is expired (15 minutes)
        if ($user->email_verification_code_created_at && 
            $user->email_verification_code_created_at->diffInMinutes(now()) > 15) {
            return response()->json(['message' => 'Verification code has expired. Please request a new one.'], 422);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->save();

        $token = $user->createToken('customer-token')->plainTextToken;
return response()->json([
            'message' => 'Email verified successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token
        ]);
    }

    /**
     * Resend verification code for customer
     */
    public function resendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['user_id']);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if user is already verified
        if ($user->isEmailVerified()) {
            return response()->json(['message' => 'Email is already verified'], 422);
        }

        // Generate new 6-digit code
        $code = random_int(100000, 999999);
        $user->email_verification_code = $code;
        $user->email_verification_code_created_at = now();
        $user->save();

        // Send code to email
        try {
            Mail::to($user->email)->send(new EmailVerificationCode($code));
            } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send verification code'], 500);
        }
return response()->json([
            'message' => 'Verification code resent to your email',
            'user_id' => $user->id,
        ]);
    }


    /**
     * Auth0 callback for customers
     */
    public function auth0Callback(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'auth0_user_id' => 'required|string',
        ]);

        // Find or create customer user
        $user = User::updateOrCreate(
            ['auth0_user_id' => $validated['auth0_user_id']],
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        // Ensure user is customer (security check)
        if ($user->role !== 'customer') {
            return response()->json([
                'message' => 'Auth0 authentication is only available for customers'
            ], 403);
        }

        $token = $user->createToken('auth0-customer-token')->plainTextToken;
return response()->json([
            'message' => 'Customer authentication successful via Auth0',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => 'auth0'
            ],
            'token' => $token
        ]);
    }

    /**
     * Login customer (password or email check for Auth0 users)
     */
    public function loginCustomer(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])
                   ->where('role', 'customer')
                   ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid customer credentials'
            ], 401);
        }

        // Check if user is Auth0 user
        if ($user->isAuth0User()) {
            return response()->json([
                'message' => 'This account uses Auth0 authentication. Please login via Auth0.',
                'auth_type' => 'auth0'
            ], 422);
        }

        // Check password for traditional users
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid customer credentials'
            ], 401);
        }

        // Require email verification for password users
        if ($user->isPasswordUser() && !$user->isEmailVerified()) {
            return response()->json([
                'message' => 'Email not verified',
                'user_id' => $user->id,
                'require_verification' => true
            ], 403);
        }

        $token = $user->createToken('customer-session')->plainTextToken;
return response()->json([
            'message' => 'Customer login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => 'password'
            ],
            'token' => $token
        ]);
    }

    /**
     * Register admin with password (admins only)
     */
    public function registerAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('admin-token')->plainTextToken;
return response()->json([
            'message' => 'Admin registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => 'password'
            ],
            'token' => $token
        ], 201);
    }

    /**
     * Login admin with password (admins only)
     */
    public function loginAdmin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])
                   ->where('role', 'admin')
                   ->whereNotNull('password')
                   ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid admin credentials'
            ], 401);
        }

        // Revoke existing tokens (optional)
        $user->tokens()->delete();

        $token = $user->createToken('admin-session')->plainTextToken;
return response()->json([
            'message' => 'Admin login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => 'password'
            ],
            'token' => $token
        ]);
    }

    /**
     * Universal login (determines auth method based on email)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // If user is Auth0 customer
        if ($user->isCustomer() && $user->isAuth0User()) {
            return response()->json([
                'message' => 'This account uses Auth0 authentication. Please login via Auth0.',
                'auth_type' => 'auth0',
                'role' => 'customer'
            ], 422);
        }

        // Check password
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('user-session')->plainTextToken;
return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => 'password'
            ],
            'token' => $token
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => $user->isAuth0User() ? 'auth0' : 'password',
                'email_verified_at' => $user->email_verified_at,
            ]
        ]);
    }

    /**
     * Change password (for password-based users only)
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if ($user->isAuth0User()) {
            return response()->json([
                'message' => 'Password change not available for Auth0 users'
            ], 422);
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => $validated['password']
        ]);
return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Auth0 users cannot change email
        if ($user->isAuth0User() && $validated['email'] !== $user->email) {
            return response()->json([
                'message' => 'Email cannot be changed for Auth0 users'
            ], 422);
        }

        $user->update($validated);
return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => $user->isAuth0User() ? 'auth0' : 'password'
            ]
        ]);
    }

    /**
     * Check authentication method for email
     */
    public function checkAuthMethod(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'exists' => false
            ], 404);
        }
return response()->json([
            'exists' => true,
            'role' => $user->role,
            'auth_type' => $user->isAuth0User() ? 'auth0' : 'password',
            'message' => $user->isAuth0User() 
                ? 'Please login via Auth0' 
                : 'Please login with your password'
        ]);
    }
}