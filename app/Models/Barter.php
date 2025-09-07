<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'status',
    ];

    // 🔗 العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 لو بدك تضيف عروض تبادل متعددة في جدول وسيط
    public function offers()
    {
        return $this->hasMany(BarterOffer::class);
    }

    // 📌 Scope جاهز لجلب فقط العروض pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}
