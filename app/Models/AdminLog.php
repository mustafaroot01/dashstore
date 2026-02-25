<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    public $timestamps = false; // only created_at

    protected $fillable = [
        'admin_id', 'action', 'ip_address',
        'location', 'device', 'browser',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    // ── Relationships ────────────────────────
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
