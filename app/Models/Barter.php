<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Barter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'image',
        'status',
        'offer_item',
        'request_item',
        'location',
        'contact_method',
        'availability',
        'offer_status',
        'quantity',
        'exchange_preferences',
        'accepted_by',
    ];

    // 🔗 العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(BarterResponse::class);
    }

    public function acceptedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
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
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset($value) : null,

            set: fn($value) => $value instanceof \Illuminate\Http\UploadedFile
            ? $value->store('products', 'public')
            : $value
        );
    }
}
