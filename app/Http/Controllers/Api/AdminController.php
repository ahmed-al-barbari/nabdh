<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Enums\ApiMessage;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller {

    public function store( Request $request ) {
        $request->validate( [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'required|in:customer,merchant,admin',
        ] );

        $user = User::create( [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'status' => 'pending',
        ] );

        return response()->json( [
            'message' => ApiMessage::USER_CREATED->value,
            'user' => $user
        ], 201 );
    }

    public function index( Request $request ) {
        $query = User::query();

        if ( $request->has( 'role' ) ) {
            $query->where( 'role', $request->role );
        }

        if ( $request->has( 'status' ) ) {
            $query->where( 'status', $request->status );
        }

        $users = $query->paginate( 10 );

        return response()->json( $users );
    }

    public function update( Request $request, $id ) {
        $user = User::findOrFail( $id );

        $request->validate( [
            'status' => 'sometimes|required|in:active,pending,inactive',
            'role' => 'sometimes|required|in:merchant,customer'
        ] );

        $user->update( $request->all() );

        return response()->json( [
            'message' => ApiMessage::USER_UPDATED->value,
            'user' => $user
        ] );
    }

    public function show( $id ) {
        $user = User::find( $id );

        if ( !$user ) {
            return response()->json( [
                'message' => ApiMessage::USER_NOT_FOUND->value
            ], 404 );
        }

        return response()->json( [
            'message' => ApiMessage::USER_FETCHED->value,
            'user' => $user
        ] );
    }

    public function changeStatus( $id, Request $request ) {
        $user = User::findOrFail( $id );

        $request->validate( [
            'status' => 'required|in:active,inactive'
        ] );

        $user->update( [ 'status' => $request->status ] );

        return response()->json( [
            'message' => ApiMessage::USER_STATUS_UPDATED->value,
            'user' => $user
        ] );
    }

    public function destroy( $id ) {
        $user = User::findOrFail( $id );
        $user->delete();

        return response()->json( [
            'message' => ApiMessage::USER_DELETED->value
        ] );
    }

}
