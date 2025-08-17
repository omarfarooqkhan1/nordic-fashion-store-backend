<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index(): JsonResponse
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', Rule::in(['home', 'work', 'other'])],
            'label' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
        ]);

        $address = Auth::user()->addresses()->create($request->only([
            'type', 'label', 'street', 'city', 'state', 'postal_code', 'country'
        ]));

        // If this is the user's first address, make it the default
        $userAddressCount = Auth::user()->addresses()->count();
        if ($userAddressCount === 1) {
            $address->update(['is_default' => true]);
            $address->refresh(); // Refresh to get updated data
        }

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Display the specified address.
     */
    public function show(Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address,
        ]);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        $request->validate([
            'type' => ['nullable', Rule::in(['home', 'work', 'other'])],
            'label' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'is_default' => 'sometimes|boolean',
        ]);

        $address->update($request->only([
            'type', 'label', 'street', 'city', 'state', 'postal_code', 'country', 'is_default'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address,
        ]);
    }

    /**
     * Set an address as default.
     */
    public function setDefault(Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        // First, unset all other default addresses for this user
        Auth::user()->addresses()->update(['is_default' => false]);

        // Then set this address as default
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => $address,
        ]);
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        // Don't allow deletion if it's the only address
        $userAddressCount = Auth::user()->addresses()->count();
        if ($userAddressCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'You must have at least one address',
            ], 422);
        }

        // If deleting the default address, set another as default
        if ($address->is_default) {
            $newDefault = Auth::user()->addresses()
                ->where('id', '!=', $address->id)
                ->first();
            
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }
}
