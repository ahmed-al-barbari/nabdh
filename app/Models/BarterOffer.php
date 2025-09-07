<?php

// app/Models/BarterOffer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarterOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'barter_id',
        'user_id',
        'message',
        'status',
    ];

    public function barter()
    {
        return $this->belongsTo(Barter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
