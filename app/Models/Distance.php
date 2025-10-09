<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distance extends Model {
    use HasFactory;
    protected $fillable = [
        'city_id_one',
        'city_id_two',
        'distance',
    ];
}
