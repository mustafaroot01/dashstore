<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'quantity', 'price', 'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'cost_price' => 'decimal:2',
        ];
    }

    // ── Accessors ───────────────────────────
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getProfitAttribute(): float
    {
        return ($this->price - $this->cost_price) * $this->quantity;
    }

    // ── Relationships ────────────────────────
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
