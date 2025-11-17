<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class MessageController extends Controller
{
    /**
     * Get all messages sent by admin
     */
    public function index(Request $request)
    {
        $moderator = auth('api')->user();
        if (!$moderator) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Message::where('sender_type', Message::SENDER_TYPE_ADMIN)
            ->where('sender_id', $moderator->id)
            ->with(['receiver', 'service'])
            ->orderBy('created_at', 'desc');

        // Filter by organization
        if ($request->has('organization_id') && $request->organization_id) {
            $query->where(function ($q) use ($request) {
                $q->where('receiver_id', $request->organization_id)
                  ->orWhereNull('receiver_id'); // All organizations
            });
        }

        $messages = $query->paginate(20);

        $items = collect($messages->items())->map(function ($message) {
            $message->created_at_jalali = Jalalian::forge($message->created_at)->format('Y/m/d H:i:s');
            if ($message->read_at) {
                $message->read_at_jalali = Jalalian::forge($message->read_at)->format('Y/m/d H:i:s');
            }
            return $message;
        });

        return response()->json([
            'success' => true,
            'data' => $items->all(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    /**
     * Create a message to organization(s)
     */
    public function store(Request $request)
    {
        $moderator = auth('api')->user();
        if (!$moderator) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'organization_id' => 'nullable|exists:organizations,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'organization_id.exists' => 'سازمان انتخاب شده معتبر نیست',
            'subject.required' => 'عنوان پیام الزامی است',
            'subject.max' => 'عنوان پیام نباید بیشتر از 255 کاراکتر باشد',
            'message.required' => 'متن پیام الزامی است',
            'message.max' => 'متن پیام نباید بیشتر از 5000 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If organization_id is provided, send to that organization
        // If null, send to all organizations
        $message = Message::create([
            'sender_type' => Message::SENDER_TYPE_ADMIN,
            'sender_id' => $moderator->id,
            'receiver_type' => Message::RECEIVER_TYPE_ORGANIZATION,
            'receiver_id' => $request->organization_id, // null = all organizations
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $message->load(['receiver']);
        $message->created_at_jalali = Jalalian::forge($message->created_at)->format('Y/m/d H:i:s');

        return response()->json([
            'success' => true,
            'message' => $request->organization_id ? 'پیام با موفقیت ارسال شد.' : 'پیام به همه سازمان‌ها ارسال شد.',
            'data' => $message
        ], 201);
    }

    /**
     * Get a specific message
     */
    public function show($id)
    {
        $moderator = auth('api')->user();
        if (!$moderator) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::where('sender_type', Message::SENDER_TYPE_ADMIN)
            ->where('sender_id', $moderator->id)
            ->with(['receiver', 'service'])
            ->findOrFail($id);

        $message->created_at_jalali = Jalalian::forge($message->created_at)->format('Y/m/d H:i:s');
        if ($message->read_at) {
            $message->read_at_jalali = Jalalian::forge($message->read_at)->format('Y/m/d H:i:s');
        }

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }
}
