<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'description', 'size',
        'price', 'sale_price', 'is_on_sale',
        'cost_price', 'is_active', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'sale_price'   => 'decimal:2',
            'cost_price'   => 'decimal:2',
            'is_on_sale'   => 'boolean',
            'is_active'    => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    // ── Scopes ──────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    // ── Accessors ───────────────────────────
    /**
     * السعر الفعلي للزبون (بعد الخصم أو بدونه)
     */
    public function getEffectivePriceAttribute(): float
    {
        return ($this->is_on_sale && $this->sale_price)
            ? (float) $this->sale_price
            : (float) $this->price;
    }

    /**
     * الربح لكل وحدة
     */
    public function getProfitPerUnitAttribute(): float
    {
        return $this->effective_price - (float) $this->cost_price;
    }

    // ── Relationships ────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('order');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
