<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * الأعمدة التي يمكن إدخالها بشكل مباشر (Mass Assignment)
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * الأعمدة التي تُخفى عند تحويل النموذج إلى JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * أنواع التحويل (Casting)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
