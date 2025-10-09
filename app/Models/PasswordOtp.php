<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model {
    use HasFactory;
    protected $fillable = [ 'identifier', 'otp', 'expires_at' ];
    protected $dates = [ 'expires_at' ];
}
