<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name', 'last_name', 'phone',
        'gender', 'address', 'password', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Accessors ───────────────────────────
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ── Scopes ──────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Relationships ────────────────────────
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
