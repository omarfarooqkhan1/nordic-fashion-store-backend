<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user is authenticated and update their newsletter preference
            $user = Auth::user();
            if ($user && $user->email === $request->email) {
                $user->update(['newsletter_subscription' => true]);
            }

            $result = Newsletter::subscribe(
                $request->email,
                $request->name,
                $request->source ?? 'website'
            );

            $statusCode = $result['status'] === 'already_subscribed' ? 200 : 201;
return response()->json([
                'message' => $result['message'],
                'status' => $result['status'],
                'subscription' => $result['subscription']
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to subscribe to newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user is authenticated and update their newsletter preference
            $user = Auth::user();
            if ($user && $user->email === $request->email) {
                $user->update(['newsletter_subscription' => false]);
            }

            Newsletter::unsubscribe($request->email);
return response()->json([
                'message' => 'Successfully unsubscribed from newsletter'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to unsubscribe from newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from newsletter via GET link (for email links)
     */
    public function unsubscribeGet($email)
    {
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'message' => 'Invalid email format'
                ], 400);
            }

            // Check if subscription exists
            $subscription = Newsletter::where('email', $email)->first();
            if (!$subscription) {
                return response()->json([
                    'message' => 'Email not found in our newsletter list',
                    'email' => $email
                ], 404);
            }

            if (!$subscription->is_active) {
                return response()->json([
                    'message' => 'You are already unsubscribed from our newsletter',
                    'email' => $email
                ]);
            }

            // Update user preference if they're registered
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['newsletter_subscription' => false]);
            }

            // Unsubscribe from newsletter
            Newsletter::unsubscribe($email);
return response()->json([
                'message' => 'You have been successfully unsubscribed from our newsletter',
                'email' => $email,
                'unsubscribed_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to unsubscribe from newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check subscription status
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $subscription = Newsletter::where('email', $request->email)->first();
return response()->json([
            'subscribed' => $subscription ? $subscription->is_active : false,
            'subscription' => $subscription
        ]);
    }

    /**
     * Update user's newsletter preference (authenticated users only)
     */
    public function updateUserPreference(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'newsletter_subscription' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update user preference
            $user->update(['newsletter_subscription' => $request->newsletter_subscription]);

            // Sync with newsletter table
            if ($request->newsletter_subscription) {
                Newsletter::subscribe($user->email, $user->name, 'user_profile');
            } else {
                Newsletter::unsubscribe($user->email);
            }
return response()->json([
                'message' => 'Newsletter preference updated successfully',
                'newsletter_subscription' => $user->newsletter_subscription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update newsletter preference',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}