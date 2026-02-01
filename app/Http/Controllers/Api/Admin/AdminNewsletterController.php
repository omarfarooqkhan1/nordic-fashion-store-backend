<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Mail\NewsletterBroadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminNewsletterController extends Controller
{
    /**
     * Get all newsletter subscribers with pagination and filtering
     */
    public function index(Request $request)
    {
        try {
            $query = Newsletter::query();

            // Search by email or name
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('email', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Filter by subscription source
            if ($request->has('source') && $request->source) {
                $query->where('subscription_source', $request->source);
            }

            // Get pagination parameters
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            // Execute query with pagination
            $subscribers = $query->orderBy('subscribed_at', 'desc')
                                ->paginate($limit, ['*'], 'page', $page);
return response()->json([
                'subscribers' => $subscribers->items(),
                'total' => $subscribers->total(),
                'current_page' => $subscribers->currentPage(),
                'per_page' => $subscribers->perPage(),
                'last_page' => $subscribers->lastPage(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch newsletter subscribers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get newsletter statistics
     */
    public function stats()
    {
        try {
            $totalSubscribers = Newsletter::count();
            $activeSubscribers = Newsletter::active()->count();
            $inactiveSubscribers = Newsletter::where('is_active', false)->count();
            $recentSubscribers = Newsletter::where('subscribed_at', '>=', now()->subDays(30))->count();

            $subscriptionSources = Newsletter::selectRaw('subscription_source, COUNT(*) as count')
                ->groupBy('subscription_source')
                ->get()
                ->pluck('count', 'subscription_source');
return response()->json([
                'total_subscribers' => $totalSubscribers,
                'active_subscribers' => $activeSubscribers,
                'inactive_subscribers' => $inactiveSubscribers,
                'recent_subscribers' => $recentSubscribers,
                'subscription_sources' => $subscriptionSources,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch newsletter statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually add a subscriber
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscription = Newsletter::subscribe(
                $request->email,
                $request->name,
                'admin'
            );
return response()->json([
                'message' => 'Subscriber added successfully',
                'subscription' => $subscription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add subscriber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subscriber status
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscriber = Newsletter::findOrFail($id);
            
            $updateData = [
                'is_active' => $request->is_active,
            ];

            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if (!$request->is_active && $subscriber->is_active) {
                $updateData['unsubscribed_at'] = now();
            } elseif ($request->is_active && !$subscriber->is_active) {
                $updateData['subscribed_at'] = now();
                $updateData['unsubscribed_at'] = null;
            }

            $subscriber->update($updateData);
return response()->json([
                'message' => 'Subscriber updated successfully',
                'subscription' => $subscriber->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update subscriber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subscriber
     */
    public function destroy($id)
    {
        try {
            $subscriber = Newsletter::findOrFail($id);
            $subscriber->delete();
return response()->json([
                'message' => 'Subscriber deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete subscriber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export subscribers to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Newsletter::query();

            // Apply same filters as index
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('email', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            $subscribers = $query->orderBy('subscribed_at', 'desc')->get();

            $csvData = "Name,Email,Status,Subscribed At,Source\n";
            foreach ($subscribers as $subscriber) {
                $csvData .= sprintf(
                    "%s,%s,%s,%s,%s\n",
                    $subscriber->name ?? '',
                    $subscriber->email,
                    $subscriber->is_active ? 'Active' : 'Inactive',
                    $subscriber->subscribed_at->format('Y-m-d H:i:s'),
                    $subscriber->subscription_source
                );
            }
return response($csvData, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export subscribers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send newsletter broadcast to all active subscribers
     */
    public function sendBroadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'send_to' => 'sometimes|in:all,active,test', // all, active, or test (admin email only)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subject = $request->subject;
            $content = $request->content;
            $sendTo = $request->get('send_to', 'active');

            // Get subscribers based on send_to parameter
            if ($sendTo === 'test') {
                // Send test email to admin only
                $adminEmail = config('mail.admin_email', 'support@nordflex.store');
                $testSubscriber = new Newsletter([
                    'email' => $adminEmail,
                    'name' => 'Admin Test',
                    'is_active' => true,
                ]);
                
                Mail::to($adminEmail)->send(new NewsletterBroadcast($testSubscriber, $subject, $content));
return response()->json([
                    'message' => 'Test newsletter sent successfully to admin email',
                    'recipients_count' => 1,
                    'test_email' => $adminEmail
                ]);
            }

            // Get active subscribers for broadcast
            $query = Newsletter::active();
            
            if ($sendTo === 'all') {
                // Include inactive subscribers too
                $query = Newsletter::query();
            }

            $subscribers = $query->get();

            if ($subscribers->isEmpty()) {
                return response()->json([
                    'message' => 'No subscribers found to send newsletter to',
                    'recipients_count' => 0
                ], 400);
            }

            $successCount = 0;
            $failedCount = 0;
            $failedEmails = [];

            // Send emails in batches to avoid overwhelming the mail server
            $subscribers->chunk(50)->each(function ($batch) use ($subject, $content, &$successCount, &$failedCount, &$failedEmails) {
                foreach ($batch as $subscriber) {
                    try {
                        Mail::to($subscriber->email)->send(new NewsletterBroadcast($subscriber, $subject, $content));
                        $successCount++;
                        
                        // Small delay to prevent overwhelming mail server
                        usleep(100000); // 0.1 second delay
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        $failedEmails[] = $subscriber->email;}
                }
            });

            $response = [
                'message' => 'Newsletter broadcast completed',
                'recipients_count' => $subscribers->count(),
                'successful_sends' => $successCount,
                'failed_sends' => $failedCount,
            ];

            if ($failedCount > 0) {
                $response['failed_emails'] = $failedEmails;
                $response['message'] = "Newsletter broadcast completed with {$failedCount} failures";
            }
return response()->json($response);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to send newsletter broadcast',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}