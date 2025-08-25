<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\User;
use App\Models\Alert;
use App\Models\Barter;
use App\Models\Message;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{

      public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'language' => 'in:ar,en',
            'currency' => 'in:ILS,USD',
            'theme'    => 'in:light,dark',
        ]);

        $user = Auth::user();
        $user->update($validated);

        return response()->json([
            'message' => 'Preferences updated successfully',
            'preferences' => Arr::only($user->toArray(), ['language', 'currency', 'theme'])
        ]);
    }

    public function addAlert(Request $request) {
        $request->validate([
            'product_id'=>'required|exists:products,id',
            'price_condition'=>'required|in:less,greater',
            'price'=>'required|numeric',
            'method'=>'required|in:sms,email,whatsapp'
        ]);

        $alert = Alert::create([
            'user_id'=>$request->user()->id,
            'product_id'=>$request->product_id,
            'price_condition'=>$request->price_condition,
            'price'=>$request->price,
            'method'=>$request->method
        ]);
        return response()->json($alert);
    }

    public function addBarter(Request $request) {
        $request->validate([
            'offer_item'=>'required',
            'request_item'=>'required',
            'description'=>'nullable'
        ]);

        $barter = Barter::create([
            'user_id'=>$request->user()->id,
            'offer_item'=>$request->offer_item,
            'request_item'=>$request->request_item,
            'description'=>$request->description
        ]);

        return response()->json($barter);
    }

    public function sendMessage(Request $request,$barter_id) {
        $request->validate(['message'=>'required']);
        $message = Message::create([
            'barter_id'=>$barter_id,
          //  'barter_id'=>$barter_id,
            'sender_id'=>$request->user()->id,
            'message'=>$request->message
        ]);
        return response()->json($message);
    }

    public function deleteAccount(Request $request) {
        $user = $request->user();
        $user->delete();
        return response()->json(['message'=>'Account deleted successfully']);
    }
}

