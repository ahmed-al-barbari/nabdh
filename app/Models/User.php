<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'status',
        'theme',
        'currency',
        'recive_notification',
        // 'notification_method',
        'notification_methods',
        'city_id',
        'share_location'
    ];
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_methods' => 'json',
    ];

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function barters()
    {
        return $this->hasMany(Barter::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }
}
