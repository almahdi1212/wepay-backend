<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
    ];

    /**
     * 🔄 ترتيب التحديثات من الأحدث إلى الأقدم بشكل تلقائي
     */
    protected static function booted()
    {
        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('date', 'desc');
        });
    }
}
