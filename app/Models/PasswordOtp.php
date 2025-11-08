<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordOtp extends Model
{
    use HasFactory;

    protected $table = 'password_otps';
    
    protected $fillable = ['identifier', 'otp', 'expires_at'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
