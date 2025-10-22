<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Distance;

class CityController extends Controller {
    public function index() {
        return response()->json(
            [ 'cities' => City::all() ]
        );
    }

    public function getDistances() {
        return response()->json( [
            'distances' => Distance::all()
        ] );
    }
}
