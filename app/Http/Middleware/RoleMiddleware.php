<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware {
    /**
    * Handle an incoming request.
    *
    * @param  \Closure( \Illuminate\Http\Request ): ( \Symfony\Component\HttpFoundation\Response )  $next
    */

    public function handle( Request $request, \Closure $next, string $role ) {
        $user = $request->user();
        if ( !$user || $user->role !== $role ) {
            return response()->json( [
                'message' => ApiMessage::UNAUTHORIZED->value
            ], 403 );
        }

        return $next( $request );
    }
}
