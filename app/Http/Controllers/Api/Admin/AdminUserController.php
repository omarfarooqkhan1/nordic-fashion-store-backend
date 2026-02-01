<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    /**
     * Get all users with pagination and filtering
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Exclude super admin (support@nordflex.store)
            $query->where('email', '!=', 'support@nordflex.store');

            // Search by name or email
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            // Filter by status (we'll use email_verified_at for now as status)
            if ($request->has('status') && $request->status) {
                if ($request->status === 'active') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($request->status === 'inactive') {
                    $query->whereNull('email_verified_at');
                }
            }

            // Get pagination parameters
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            // Execute query with pagination
            $users = $query->orderBy('created_at', 'desc')
                          ->paginate($limit, ['*'], 'page', $page);

            // Transform users data
            $transformedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                    'status' => $user->email_verified_at ? 'active' : 'inactive',
                    'orders_count' => $user->orders()->count(),
                    'total_spent' => $user->orders()->sum('total') ?? 0,
                    'user_type' => 'registered',
                ];
            });

            // Get guest users (from orders without user_id)
            $guestUsersQuery = \App\Models\Order::whereNull('user_id')
                ->select('shipping_name as name', 'shipping_email as email', 'session_id')
                ->selectRaw('MIN(created_at) as created_at') // Use MIN to avoid GROUP BY issues
                ->groupBy('shipping_email', 'shipping_name', 'session_id');

            // Apply search to guest users if provided
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $guestUsersQuery->where(function($q) use ($search) {
                    $q->where('shipping_name', 'LIKE', "%{$search}%")
                      ->orWhere('shipping_email', 'LIKE', "%{$search}%");
                });
            }

            // Filter guest users by role and status
            $includeGuestUsers = true;
            
            // Only exclude guest users if specifically filtering for admin role
            if ($request->has('role') && $request->role === 'admin') {
                $includeGuestUsers = false;
            }
            
            // Only exclude guest users if specifically filtering for active/inactive status
            // (since guests have their own 'guest' status)
            if ($request->has('status') && $request->status && 
                in_array($request->status, ['active', 'inactive', 'banned'])) {
                $includeGuestUsers = false;
            }

            if ($includeGuestUsers) {
                $guestUsers = $guestUsersQuery->get();
                
                $transformedGuestUsers = $guestUsers->map(function ($guestOrder) {
                    $guestOrdersCount = \App\Models\Order::where('shipping_email', $guestOrder->email)
                        ->whereNull('user_id')
                        ->count();
                    $guestTotalSpent = \App\Models\Order::where('shipping_email', $guestOrder->email)
                        ->whereNull('user_id')
                        ->sum('total');

                    return [
                        'id' => 'guest_' . $guestOrder->session_id,
                        'name' => $guestOrder->name,
                        'email' => $guestOrder->email,
                        'role' => 'customer',
                        'email_verified_at' => null,
                        'created_at' => $guestOrder->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $guestOrder->created_at->format('Y-m-d H:i:s'),
                        'status' => 'guest',
                        'orders_count' => $guestOrdersCount,
                        'total_spent' => $guestTotalSpent ?? 0,
                        'user_type' => 'guest',
                        'session_id' => $guestOrder->session_id,
                    ];
                });

                // Merge registered users and guest users
                $allUsers = $transformedUsers->concat($transformedGuestUsers);
            } else {
                $allUsers = $transformedUsers;
            }

            // Sort all users by created_at desc
            $allUsers = $allUsers->sortByDesc('created_at')->values();

            // Manual pagination for combined results
            $total = $allUsers->count();
            $currentPage = $page;
            $perPage = $limit;
            $offset = ($currentPage - 1) * $perPage;
            $paginatedUsers = $allUsers->slice($offset, $perPage)->values();
return response()->json([
                'users' => $paginatedUsers,
                'total' => $total,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user by ID
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent viewing the super admin details
            if ($user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Cannot view the super admin account'
                ], 403);
            }
return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                'status' => $user->email_verified_at ? 'active' : 'inactive',
                'orders_count' => $user->orders()->count(),
                'total_spent' => $user->orders()->sum('total') ?? 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:customer,admin',
                'password' => ['required', 'string', Password::defaults()],
                'status' => 'in:active,inactive,banned'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prevent creation of another super admin account
            if ($request->email === 'support@nordflex.store') {
                return response()->json([
                    'message' => 'Cannot create another super admin account'
                ], 403);
            }

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ];

            // Set email verification based on status
            if ($request->status === 'active' || !$request->has('status')) {
                $userData['email_verified_at'] = now();
            }

            $user = User::create($userData);
return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->email_verified_at ? 'active' : 'inactive',
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent modification of the super admin
            if ($user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Cannot modify the super admin account'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'role' => 'sometimes|required|in:customer,admin',
                'password' => ['sometimes', 'nullable', 'string', Password::defaults()],
                'status' => 'sometimes|in:active,inactive,banned'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];

            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->has('role')) {
                $updateData['role'] = $request->role;
            }

            if ($request->has('password') && $request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $updateData['email_verified_at'] = $user->email_verified_at ?: now();
                } elseif ($request->status === 'inactive') {
                    $updateData['email_verified_at'] = null;
                }
            }

            $user->update($updateData);
return response()->json([
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->email_verified_at ? 'active' : 'inactive',
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of the super admin
            if ($user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Cannot delete the super admin account'
                ], 403);
            }

            // Prevent deletion of the currently authenticated admin
            $authUser = auth('sanctum')->user();
            if ($authUser && $authUser->id === $user->id) {
                return response()->json([
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            $user->delete();
return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent status modification of the super admin
            if ($user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Cannot modify the super admin status'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,banned'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];

            if ($request->status === 'active') {
                $updateData['email_verified_at'] = $user->email_verified_at ?: now();
            } elseif ($request->status === 'inactive') {
                $updateData['email_verified_at'] = null;
            } elseif ($request->status === 'banned') {
                $updateData['email_verified_at'] = null;
                // You could add a 'banned_at' column if needed
            }

            $user->update($updateData);
return response()->json([
                'message' => 'User status updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $request->status,
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent password reset of the super admin
            if ($user->isSuperAdmin()) {
                return response()->json([
                    'message' => 'Cannot reset the super admin password'
                ], 403);
            }

            // Generate a temporary password
            $temporaryPassword = Str::random(12);
            
            $user->update([
                'password' => Hash::make($temporaryPassword)
            ]);
return response()->json([
                'message' => 'Password reset successfully',
                'temporary_password' => $temporaryPassword
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user statistics for admin dashboard
     */
    public function getUserStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_customers' => User::where('role', 'customer')->count(),
                'total_admins' => User::where('role', 'admin')->count(),
                'active_users' => User::whereNotNull('email_verified_at')->count(),
                'inactive_users' => User::whereNull('email_verified_at')->count(),
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ];
return response()->json($stats);
        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to get user statistics'
            ], 500);
        }
    }
    
    /**
     * Bulk update user statuses
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer|exists:users,id',
                'status' => 'required|in:active,inactive,banned'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userIds = $request->user_ids;
            $status = $request->status;

            // Prevent updating super admin
            $userIds = User::whereIn('id', $userIds)
                ->where('email', '!=', 'support@nordflex.store')
                ->pluck('id');

            $updateData = [];
            if ($status === 'active') {
                $updateData['email_verified_at'] = now();
            } elseif ($status === 'inactive' || $status === 'banned') {
                $updateData['email_verified_at'] = null;
            }

            User::whereIn('id', $userIds)->update($updateData);
return response()->json([
                'message' => 'User statuses updated successfully',
                'updated_count' => count($userIds)
            ]);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to bulk update user statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userIds = $request->user_ids;
            
            // Prevent deleting super admin and current user
            $query = User::whereIn('id', $userIds)
                ->where('email', '!=', 'support@nordflex.store');
                
            $authUser = auth('sanctum')->user();
            if ($authUser) {
                $query->where('id', '!=', $authUser->id);
            }
            
            $userIds = $query->pluck('id');

            User::whereIn('id', $userIds)->delete();
return response()->json([
                'message' => 'Users deleted successfully',
                'deleted_count' => count($userIds)
            ]);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to bulk delete users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}