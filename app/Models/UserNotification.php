<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'method',
        'status',
        'is_triggered',
        'type',
    ];

    public function user() {
        return $this->belongsTo( User::class );
    }

    public function product() {
        return $this->belongsTo( MainProduct::class, 'product_id' );
    }
}
