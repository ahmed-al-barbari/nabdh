<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Barter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;

class BarterController extends Controller
{
    public function publicIndex()
    {
        $barters = Barter::paginate(10);
        return response()->json([
            'message' => ApiMessage::BARTER_FETCHED->value,
            'barters' => $barters
        ]);
    }

    public function show($id)
    {
        $barter = Barter::findOrFail($id);
        return response()->json([
            'message' => ApiMessage::BARTER_FETCHED->value,
            'barter' => $barter
        ]);
    }

    // public function index()
    // {
    //     $barters = Barter::where('user_id', Auth::id())->paginate(10);

    //     return response()->json([
    //         'message' => ApiMessage::BARTER_FETCHED->value,
    //         'barters' => $barters
    //     ]);
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'offer_item'   => 'required|string|max:255',
            'request_item' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'location'     => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
            'status'       => 'in:pending,accepted,rejected'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('barters', 'public');
        }

        $barter = Barter::create(array_merge($validated, ['user_id' => Auth::id()]));

        return response()->json([
            'message' => ApiMessage::BARTER_CREATED->value,
            'barter'  => $barter
        ]);
    }

    public function update(Request $request, $id)
    {
        $barter = Barter::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'offer_item'   => 'string|max:255',
            'request_item' => 'string|max:255',
            'description'  => 'nullable|string',
            'location'     => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
            'status'       => 'in:pending,accepted,rejected'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('barters', 'public');
        }

        $barter->update($validated);

        return response()->json([
            'message' => ApiMessage::BARTER_UPDATED->value,
            'barter'  => $barter
        ]);
    }

    public function destroy($id)
    {
        $barter = Barter::where('user_id', Auth::id())->findOrFail($id);
        $barter->delete();

        return response()->json([
            'message' => ApiMessage::BARTER_DELETED->value
        ]);
    }
}
