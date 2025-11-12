<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\ShipmentStatusHistory;


class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
    'tracking_number',
    'customer_name',
    'customer_location', // ✅ جديد
    'customer_whatsapp',
    'price_usd',
    'price_lyd',
    'quantity',
    'description',
    'user_id',
    'status_code',
];


    /**
     * 🔁 العلاقة مع جدول المستخدمين (الموظف المسؤول)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ⚙️ إنشاء رقم شحنة تلقائي مثل nov0001
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            // أول 3 حروف من الشهر الحالي (صغيرة)
            $prefix = strtolower(substr(Carbon::now()->format('M'), 0, 3)); // مثل: nov

            // البحث عن آخر رقم لنفس الشهر
            $lastShipment = self::where('tracking_number', 'like', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();

            // الرقم الجديد
            $nextNumber = 1;
            if ($lastShipment) {
                $lastNum = intval(substr($lastShipment->tracking_number, 3));
                $nextNumber = $lastNum + 1;
            }

            // رقم مكون من 4 خانات (مثال: nov0001)
            $formatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $shipment->tracking_number = $prefix . $formatted;
        });
    }
public function statusHistories()
{
    return $this->hasMany(ShipmentStatusHistory::class)->orderBy('created_at', 'asc');
}

}
