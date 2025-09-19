<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\ChangeUserRoleEvent;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected static function booted()
    {
        static::updated(function (User $user) {
            info('no role');
            info($user->isDirty() == true ? 'true' : 'fasel');
            if ($user->isDirty('role')) {
                event(new ChangeUserRoleEvent($user));
            }

        });
    }

    protected $appends = ['barters_count', 'is_trusty'];

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

    public function bartersCount(): Attribute
    {
        return Attribute::make(get: fn() => Barter::where('status', 'completed')->where(function ($q) {
            $q->where('user_id', $this->id)
                ->orWhere('accepted_by', $this->id);
        })->count());
    }
    public function isTrusty(): Attribute
    {
        return Attribute::make(function () {
            if ($this->role == 'admin')
                return true;
            if ($this->email && $this->phone && $this->share_location && $this->city_id) {
                if ($this->role == 'customer')
                    return true;
                if ($this->role == 'merchant')
                    if ($this->store->name && $this->store->address && $this->store->city_id && $this->store->image)
                        return true;
                return false;
            }

        });
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
