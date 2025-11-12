<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status_code',
        'note',
        'user_id',
    ];

    // 🔗 كل سجل مرتبط بشحنة
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    // 🔗 والمستخدم الذي غيّر الحالة
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
