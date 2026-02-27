<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->get('period', 'monthly'); // daily | weekly | monthly

        // ── Date range based on period ─────────────────────
        $from = match($period) {
            'daily'   => today(),
            'weekly'  => now()->subWeek(),
            default   => now()->subMonth(),  // monthly
        };

        // ── Summary Stats (filtered by period) ─────────────
        $baseQuery = fn () => Order::where('created_at', '>=', $from);

        $totalOrders  = $baseQuery()->count();
        $totalRevenue = $baseQuery()->where('status', 'delivered')->sum('total_price');
        $totalProfit  = $this->calculateProfit($from);

        // Users
        $totalUsers    = User::count();  // total always
        $newUsersToday = User::whereDate('created_at', today())->count();

        // Pending (always counts all pending, not filtered by period)
        $pendingOrders = Order::where('status', 'pending')->count();

        // ── Orders by Status (filtered by period) ──────────
        $statusCounts = $baseQuery()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Chart Data (filtered by period) ────────────────
        $chartData = $this->getChartData($period, $from);

        // ── Top 5 Products (filtered by period) ────────────
        $topProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', $from)
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ── Top Districts (filtered by period) ─────────────
        $topDistricts = DB::table('orders')
            ->join('districts', 'districts.id', '=', 'orders.district_id')
            ->where('orders.created_at', '>=', $from)
            ->select('districts.name', DB::raw('count(orders.id) as total'))
            ->groupBy('districts.id', 'districts.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Latest 10 Orders ────────────────────────────────
        $latestOrders = Order::with(['user', 'district'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($o) => [
                'id'             => $o->id,
                'invoice_number' => $o->invoice_number,
                'user_name'      => $o->user?->full_name ?? '—',
                'district'       => $o->district?->name  ?? '—',
                'total_price'    => $o->total_price,
                'status'         => $o->status,
                'status_label'   => $o->status_label,
                'created_at'     => $o->created_at->format('Y-m-d H:i'),
            ]);

        $statuses = Order::$statuses;

        // Out of stock products
        $outOfStockProducts = Product::where('is_available', false)->where('is_active', true)->count();

        return Inertia::render('Dashboard/Index', compact(
            'totalOrders', 'totalRevenue', 'totalProfit',
            'totalUsers', 'newUsersToday', 'pendingOrders',
            'statusCounts', 'chartData', 'topProducts',
            'topDistricts', 'latestOrders', 'period', 'statuses',
            'outOfStockProducts',
        ));
    }

    private function calculateProfit(\DateTimeInterface|string $from): float
    {
        return (float) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered')
            ->where('orders.created_at', '>=', $from)
            ->sum(DB::raw('(order_items.price - order_items.cost_price) * order_items.quantity'));
    }

    private function getChartData(string $period, $from): array
    {
        $orders = Order::with('items')
            ->where('status', 'delivered')
            ->where('created_at', '>=', $from)
            ->get();

        $grouped = $orders->groupBy(function ($order) use ($period) {
            return $period === 'daily' 
                ? $order->created_at->format('H:00') 
                : $order->created_at->format('Y-m-d');
        });

        // Sort keys chronologically
        $grouped = $grouped->sortKeys();

        $labels = [];
        $revenue = [];
        $profit = [];

        foreach ($grouped as $key => $dailyOrders) {
            $labels[] = $key;
            $revenue[] = round($dailyOrders->sum('total_price'), 2);
            $profit[] = round($dailyOrders->sum(function ($order) {
                return $order->items->sum(function ($item) {
                    return ($item->price - $item->cost_price) * $item->quantity;
                });
            }), 2);
        }

        return [
            'labels'  => $labels,
            'revenue' => $revenue,
            'profit'  => $profit,
        ];
    }
}
