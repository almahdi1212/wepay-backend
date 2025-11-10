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
        'name',        // ✅ الاسم الكامل
        'username',    // ✅ اسم المستخدم (لتسجيل الدخول)
        'password',    // ✅ كلمة المرور
    ];

    /**
     * الأعمدة التي تُخفى عند تحويل النموذج إلى JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
