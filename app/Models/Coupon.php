<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value',
        'usage_limit', 'used_count', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'      => 'decimal:2',
            'is_active'  => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    // ── Scopes ──────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->where(fn ($q) => $q
                ->whereNull('usage_limit')
                ->orWhereColumn('used_count', '<', 'usage_limit')
            )
            ->where(fn ($q) => $q
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now())
            );
    }

    // ── Business Logic ───────────────────────
    public function isValid(): bool
    {
        if (! $this->is_active) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            return round($subtotal * ($this->value / 100), 2);
        }
        return min((float) $this->value, $subtotal);
    }

    // ── Relationships ────────────────────────
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
