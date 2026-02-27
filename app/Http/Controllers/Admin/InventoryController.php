<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Default to last 30 days if no dates provided
        $startDate = $request->query('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $query = Order::with(['user', 'district'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc');

        // Statistics
        // delivered orders counting
        $deliveredOrders = (clone $query)->where('status', 'delivered');
        $cancelledOrders = (clone $query)->whereIn('status', ['cancelled', 'rejected']);

        $stats = [
            'total_delivered_count' => $deliveredOrders->count(),
            'total_rejected_count' => $cancelledOrders->count(),
            'total_revenue' => $deliveredOrders->sum('total_price'),
            'net_profit' => $deliveredOrders->sum('total_price'), 
        ];

        // Format for frontend
        $orders = $query->paginate(30)->withQueryString()->through(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->invoice_number,
                'customer_name' => $order->user ? $order->user->full_name : 'زائر',
                'customer_phone' => $order->user ? $order->user->phone : '-',
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'status' => $order->status,
                'status_label' => $order->status_label,
                'total_amount' => $order->total_price + $order->discount_amount,
                'discount' => $order->discount_amount,
                'final_total' => $order->total_price,
                'district' => $order->district ? $order->district->name : ($order->user ? $order->user->address : '-'),
            ];
        });

        return Inertia::render('Inventory/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $query = Order::with(['user', 'district'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc');

        $fileName = "inventory_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel to read Arabic properly
            fputs($file, "\xEF\xBB\xBF");
            
            // Header Row
            fputcsv($file, [
                '#', // sequence number
                'رقم الفاتورة',
                'تاريخ الطلب',
                'اسم الزبون',
                'رقم الهاتف',
                'عنوان التوصيل',
                'حالة الطلب',
                'إجمالي الفاتورة',
                'قيمة الخصم',
                'المبلغ النهائي'
            ]);

            $sequence = 1;

            // Use chunking to handle large datasets (e.g. 10,000+ rows) without memory exhaustion
            $query->chunk(500, function ($orders) use ($file, &$sequence) {
                foreach ($orders as $order) {
                    $customerName = $order->user ? $order->user->full_name : 'زائر';
                    $customerPhone = $order->user ? $order->user->phone : '-';
                    $address = $order->district ? $order->district->name : ($order->user ? $order->user->address : '-');

                    fputcsv($file, [
                        $sequence++,
                        // Force the order number to be treated as a string by Excel by prepending a tab
                        "\t" . $order->invoice_number,
                        $order->created_at->format('Y-m-d H:i'),
                        $customerName,
                        $customerPhone,
                        $address,
                        $order->status_label,
                        $order->total_price + $order->discount_amount,
                        $order->discount_amount,
                        $order->total_price
                    ]);
                }
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
    public function alerts(Request $request)
    {
        $lowStockThreshold = (int) \App\Models\Setting::get('low_stock_threshold', 3);
        $search = $request->query('search');

        $query = \App\Models\ProductVariant::with('product.category')
            ->whereHas('product', function($q) use ($search) {
                $q->where('is_active', true);
                if ($search) {
                    $q->where('name', 'like', "%{$search}%");
                }
            })
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock', 'asc')
            ->orderBy('product_id');

        $alerts = $query->paginate(50)->withQueryString()->through(function($v) {
            return [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'product_name' => $v->product->name,
                'sku' => $v->product->sku,
                'category_name' => $v->product->category?->name ?? '—',
                'color' => $v->color,
                'size' => $v->size,
                'stock' => $v->stock,
            ];
        });

        return Inertia::render('Inventory/Alerts', [
            'alerts' => $alerts,
            'filters' => ['search' => $search],
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    public function exportAlerts(Request $request)
    {
        $lowStockThreshold = (int) \App\Models\Setting::get('low_stock_threshold', 3);
        $search = $request->query('search');

        $query = \App\Models\ProductVariant::with('product.category')
            ->whereHas('product', function($q) use ($search) {
                $q->where('is_active', true);
                if ($search) {
                    $q->where('name', 'like', "%{$search}%");
                }
            })
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock', 'asc')
            ->orderBy('product_id');

        $fileName = "low_stock_alerts_" . Carbon::now()->format('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel to read Arabic properly
            fputs($file, "\xEF\xBB\xBF");
            
            // Header Row
            fputcsv($file, [
                '#',
                'القسم',
                'كود المنتج (SKU)',
                'اسم المنتج',
                'اللون',
                'القياس',
                'المتتبقي'
            ]);

            $sequence = 1;

            // Chunking for handling 10,000+ items without memory limit
            $query->chunk(500, function ($variants) use ($file, &$sequence) {
                foreach ($variants as $v) {
                    fputcsv($file, [
                        $sequence++,
                        $v->product->category?->name ?? '—',
                        "\t" . ($v->product->sku ?? '—'), // Force text in Excel
                        $v->product->name,
                        $v->color ?? '—',
                        $v->size ?? '—',
                        $v->stock
                    ]);
                }
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
