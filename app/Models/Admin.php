<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function hasPermission($permission): bool
    {
        if (!$this->role_id) {
            return true; // Super Admin (no role meaning full access)
        }
        
        return $this->role->hasPermission($permission);
    }

    public function logs()
    {
        return $this->hasMany(AdminLog::class);
    }
}
