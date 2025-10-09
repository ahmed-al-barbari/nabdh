<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Barter;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Models\BarterMessage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Nette\Schema\ValidationException;

class BarterMessageController extends Controller {
    public function index( $barter_id ) {
        $messages = BarterMessage::where( 'barter_id', $barter_id )
        ->orderBy( 'created_at' )
        ->get();

        return response()->json( [
            'messages' => $messages
        ] );
    }

    public function store( Request $request, $barter_id ) {
        $barter = Barter::findOrFail( $barter_id );

        // تأكد أن المرسل هو صاحب المقايضة أو المستخدم اللي يقايض
        if ( !in_array( Auth::id(), [ $barter->user_id ] ) ) {
            throw ValidationException::withMessages( [
                'user' =>  [ ApiMessage::UNAUTHORIZED_BARTER_MESSAGE->value ]
            ] );
        }

        $validated = $request->validate( [
            'message' => 'required|string',
            'receiver_id' => 'required|exists:users,id'
        ] );

        $message = BarterMessage::create( [
            'barter_id'  => $barter_id,
            'sender_id'  => Auth::id(),
            'receiver_id' => $validated[ 'receiver_id' ],
            'message'    => $validated[ 'message' ],
        ] );

        return response()->json( [
            'message' =>  ApiMessage::MESSAGE_SENT->value,
            'data' => $message
        ] );
    }
}
