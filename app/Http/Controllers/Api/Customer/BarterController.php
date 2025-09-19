<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Barter;
use App\Models\BarterResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApiMessage;
use Illuminate\Support\Facades\Storage;

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

    public function index()
    {
        $user = auth()->user();

        $barters = Barter::with([
            'user' => function ($query) {
                $query->withCount([
                    'barters as batar_count' => function ($q) {
                        $q->where('status', 'completed');
                    }
                ]);
            },
            'responses' => function ($query) use ($user) {
                $query->when($user, function ($q) use ($user): void {
                    $q->where('user_id', $user->id);
                });
            }
            ,
            'acceptedUser'
        ])
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->whereNull('accepted_by')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereNotNull('accepted_by')
                            ->where(function ($q3) use ($user) {
                                $q3->where('user_id', $user->id)
                                    ->orWhere('accepted_by', $user->id);
                            });
                    });
            })
            ->paginate();

        $bartersTransformed = $barters->getCollection()->map(function ($barter) use ($user) {
            if ($barter->user_id === $user->id) {
                $barter->sentResponses = $barter->responses()->with('user')->get();
                $barter->myResponse = null;
            } else {
                $barter->myResponse = $barter->responses()->where('user_id', $user->id)->first();
                $barter->sentResponses = null;
            }

            return $barter;
        });

        $barters->setCollection($bartersTransformed);

        return response()->json([
            'message' => ApiMessage::BARTER_FETCHED->value,
            'barters' => $barters
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'offer_item' => 'required|string|max:255',
            'request_item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image',
            'quantity' => 'nullable|string|max:255',
            'contact_method' => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'exchange_preferences' => 'nullable|string|max:255',
            'offer_status' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('barters', 'public');
            $validated['image'] = Storage::url($path);
        }

        $barter = Barter::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'active' // ثابت عند الإنشاء
        ]));

        return response()->json([
            'message' => ApiMessage::BARTER_CREATED->value,
            'barter' => $barter
        ]);
    }

    public function update(Request $request, $id)
    {
        $barter = Barter::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'offer_item' => 'required|string|max:255',
            'request_item' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image',
            'quantity' => 'nullable|string|max:255',
            'contact_method' => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'exchange_preferences' => 'nullable|string|max:255',
            'offer_status' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة لو موجودة
            if ($barter->image) {
                $oldPath = str_replace('/storage/', '', $barter->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('barters', 'public');
            $validated['image'] = Storage::url($path);
        }

        $barter->update($validated);

        return response()->json([
            'message' => ApiMessage::BARTER_UPDATED->value,
            'barter' => $barter
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

    public function acceptResponse(Barter $barter, BarterResponse $response)
    {
        if ($barter->accepted_user_id) {
            return response()->json([
                'message' => 'تم قبول مستخدم بالفعل لهذه المبادلة.'
            ], 400);
        }

        $barter->update([
            'accepted_by' => $response->user_id
        ]);

        $response->update(['status' => 'accepted']);

        $barter->responses()->where('id', '!=', $response->id)
            ->update(['status' => 'rejected']);

        return response()->json(['message' => 'تم قبول المستخدم بنجاح.']);
    }

    public function respond(Request $request, Barter $barter)
    {
        $user = Auth::user();

        if ($barter->user_id === $user->id) {
            return response()->json([
                'message' => 'لا يمكنك الرد على مبادلتك الخاصة.'
            ], 403);
        }

        $existingResponse = BarterResponse::where('barter_id', $barter->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingResponse) {
            return response()->json([
                'message' => 'لقد قدمت طلبًا بالفعل.'
            ], 400);
        }

        $response = BarterResponse::create([
            'barter_id' => $barter->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'تم إرسال طلبك بنجاح.',
            'response' => $response
        ]);
    }

    public function markAsCompeleted(Barter $barter)
    {

        $barter->update([
            'status' => 'completed'
        ]);
        return response()->json([
            'message' => 'completed',
        ]);
    }

}
