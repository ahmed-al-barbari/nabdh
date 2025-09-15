<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarterResponse extends Model
{
    use HasFactory;
    protected $fillable = ['barter_id', 'user_id', 'status'];

    public function barter(): BelongsTo
    {
        return $this->belongsTo(Barter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
