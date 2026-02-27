<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'user_id', 'governorate_id', 'district_id',
        'delivery_point', 'phone', 'coupon_id',
        'discount_amount', 'status', 'total_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'total_price'     => 'decimal:2',
        ];
    }

    // ── Status Labels ────────────────────────
    public static array $statuses = [
        'pending'    => 'قيد الانتظار',
        'received'   => 'تم استلام الطلب',
        'preparing'  => 'جاري التجهيز',
        'delivering' => 'جاري التوصيل',
        'delivered'  => 'تم التسليم',
        'rejected'   => 'تم رفض الطلب',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    // ── Scopes ──────────────────────────────
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Auto Invoice Number ──────────────────
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->invoice_number)) {
                $last = static::max('id') ?? 0;
                $prefix = \App\Models\Setting::get('invoice_prefix', 'AW-');
                $order->invoice_number = $prefix . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Profit Calculation ───────────────────
    public function getTotalProfitAttribute(): float
    {
        return $this->items->sum(function (OrderItem $item) {
            return ($item->price - $item->cost_price) * $item->quantity;
        });
    }

    // ── Relationships ────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
