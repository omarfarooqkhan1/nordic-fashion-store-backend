<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Get the authenticated user's saved addresses
     */
    public function getAddresses(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated',
                    'addresses' => []
                ], 200);
            }
            
            $addresses = $user->addresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'message' => 'Addresses retrieved successfully',
                'addresses' => $addresses
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching user addresses: ' . $e->getMessage(), [
                'user_id' => $user ? $user->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to fetch addresses',
                'error' => $e->getMessage(),
                'addresses' => []
            ], 500);
        }
    }
}
