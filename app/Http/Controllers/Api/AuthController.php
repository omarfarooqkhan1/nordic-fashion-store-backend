<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully',
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
     * Register customer with Auth0
     */
    public function registerCustomerAuth0(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'auth0_user_id' => 'required|string|unique:users',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'auth0_user_id' => $validated['auth0_user_id'],
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('customer-auth0-token')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully via Auth0',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'auth_type' => 'auth0'
            ],
            'token' => $token
        ], 201);
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