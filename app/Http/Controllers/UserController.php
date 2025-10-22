<?php

namespace App\Http\Controllers;

use App\Events\ChangeUserRoleEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    public function update( Request $request ) {
        $user = $request->user();

        $validated = $request->validate( [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6|max:255',
            'current_password' => 'sometimes|required_with:password|string|min:6|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'role' => 'sometimes|string|in:merchant,customer',
            'theme' => 'sometimes|string|in:light,dark',
            'currency' => 'sometimes|string|in:USD,ILS',
            'recive_notification' => 'sometimes|in:0,1',
            'city_id' => 'sometimes|exists:cities,id',
            'share_location' => 'sometimes|in:0,1'
        ] );

        if ( isset( $validated[ 'password' ] ) ) {
            if ( !isset( $validated[ 'current_password' ] ) || !Hash::check( $validated[ 'current_password' ], $user->password ) ) {
                return response()->json( [ 'message' => 'Current password is incorrect' ], 400 );
            }
            unset( $validated[ 'current_password' ] );
        }

        $user->update( $validated );

        info( 'no role' );
        info( $user->isDirty() == true ? 'true' : 'fasel' );
        if ( $user->isDirty( 'role' ) ) {
            event( new ChangeUserRoleEvent( $user ) );
        }

        return response()->json( [
            'message' => 'User updated successfully',
            'user' => $user->load(['store.city', 'city'])
        ] );
    }
}
