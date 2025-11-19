<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        $dateFilter = $request->get('date_filter', '30'); // default last 30 days
        $startDate = Carbon::now()->subDays((int)$dateFilter);

        // Basic counts
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_variants' => ProductVariant::count(),
            'total_customers' => User::customers()->count(),
            'total_admins' => User::admins()->count(),
            'low_stock_variants' => ProductVariant::where('stock', '<', 10)->count(),
        ];

        // Time-based statistics
        $stats['new_orders'] = Order::where('created_at', '>=', $startDate)->count();
        $stats['new_registrations'] = User::where('created_at', '>=', $startDate)->count();
        $stats['pending_orders'] = Order::where('status', 'pending')->count();
        $stats['shipped_orders'] = Order::where('status', 'shipped')->count();
        
        // Contact forms (using database table directly)
        try {
            $stats['new_contact_forms'] = DB::table('contact_forms')->where('created_at', '>=', $startDate)->count();
            $stats['unread_contact_forms'] = DB::table('contact_forms')->where('status', 'new')->count();
        } catch (\Exception $e) {
            $stats['new_contact_forms'] = 0;
            $stats['unread_contact_forms'] = 0;
        }

        // Revenue statistics
        $stats['recent_revenue'] = Order::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('total');
        
        $stats['total_revenue'] = Order::where('status', 'completed')->sum('total');

        return response()->json($stats);
    }

    /**
     * Get recent registrations
     */
    public function getRecentRegistrations(Request $request)
    {
        $limit = $request->get('limit', 10);
        $days = $request->get('days', 7);
        
        $registrations = User::where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'email', 'role', 'created_at', 'is_admin_notified'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'registered_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $user->created_at->diffForHumans(),
                    'is_notified' => $user->is_admin_notified
                ];
            });

        return response()->json([
            'registrations' => $registrations,
            'total_count' => User::where('created_at', '>=', Carbon::now()->subDays($days))->count()
        ]);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(Request $request)
    {
        $limit = $request->get('limit', 10);
        $days = $request->get('days', 7);
        
        $orders = Order::with(['user'])
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->id,
                    'customer_name' => $order->user ? $order->user->name : 'Guest',
                    'customer_email' => $order->user ? $order->user->email : $order->email,
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $order->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'orders' => $orders,
            'total_count' => Order::where('created_at', '>=', Carbon::now()->subDays($days))->count()
        ]);
    }

    /**
     * Mark user registration as notified
     */
    public function markRegistrationAsNotified(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_admin_notified' => true]);
        
        return response()->json(['message' => 'Registration marked as notified']);
    }
}
