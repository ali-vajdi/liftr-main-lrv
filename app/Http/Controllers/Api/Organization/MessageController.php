<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class MessageController extends Controller
{
    /**
     * Get messages received by organization (from admin)
     */
    public function index(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Message::forOrganization($user->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_ORGANIZATION)
            ->with(['sender', 'service'])
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
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
     * Create a message to technician(s)
     */
    public function store(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'technician_id' => 'nullable|exists:technicians,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'technician_id.exists' => 'تکنسین انتخاب شده معتبر نیست',
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

        // If technician_id is provided, verify it belongs to the organization
        if ($request->technician_id) {
            $technician = Technician::where('organization_id', $user->organization_id)
                ->findOrFail($request->technician_id);
        }

        // If technician_id is provided, send to that technician
        // If null, send to all technicians in the organization
        $message = Message::create([
            'sender_type' => Message::SENDER_TYPE_ORGANIZATION,
            'sender_id' => $user->organization_id,
            'receiver_type' => Message::RECEIVER_TYPE_TECHNICIAN,
            'receiver_id' => $request->technician_id, // null = all technicians
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $message->load(['receiver']);
        $message->created_at_jalali = Jalalian::forge($message->created_at)->format('Y/m/d H:i:s');

        return response()->json([
            'success' => true,
            'message' => $request->technician_id ? 'پیام با موفقیت ارسال شد.' : 'پیام به همه تکنسین‌ها ارسال شد.',
            'data' => $message
        ], 201);
    }

    /**
     * Get a specific message
     */
    public function show($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::forOrganization($user->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_ORGANIZATION)
            ->with(['sender', 'service'])
            ->findOrFail($id);

        // Mark as read when viewing
        $message->markAsRead();

        $message->created_at_jalali = Jalalian::forge($message->created_at)->format('Y/m/d H:i:s');
        if ($message->read_at) {
            $message->read_at_jalali = Jalalian::forge($message->read_at)->format('Y/m/d H:i:s');
        }

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::forOrganization($user->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_ORGANIZATION)
            ->findOrFail($id);

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'پیام به عنوان خوانده شده علامت‌گذاری شد.'
        ]);
    }

    /**
     * Get messages sent by organization to technicians
     */
    public function sent(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Message::where('sender_type', Message::SENDER_TYPE_ORGANIZATION)
            ->where('sender_id', $user->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
            ->with(['receiver', 'service'])
            ->orderBy('created_at', 'desc');

        // Filter by technician
        if ($request->has('technician_id') && $request->technician_id) {
            $query->where(function ($q) use ($request) {
                $q->where('receiver_id', $request->technician_id)
                  ->orWhereNull('receiver_id'); // All technicians
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
     * Get a specific sent message
     */
    public function showSent($id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::where('sender_type', Message::SENDER_TYPE_ORGANIZATION)
            ->where('sender_id', $user->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
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
