<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\BelongsToTenant;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'name',
        'email',
        'telefone',
        'password',
        'tenant_id',
        'role',
        'oab',          
        'avatar',        
        'preferencias',
        'role',
    ];

    protected $hidden = [
        'tenant_id',
        'password',      
        'remember_token', 
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferencias' => 'array', // 👈 A mágica: O Laravel transforma JSON em Array automaticamente
    ];
}