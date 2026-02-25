<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'order'];
    protected $appends  = ['url'];

    // ── Accessors ───────────────────────────
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    // ── Relationships ────────────────────────
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
