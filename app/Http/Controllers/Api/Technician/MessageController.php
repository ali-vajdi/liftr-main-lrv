<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class MessageController extends Controller
{
    /**
     * Get messages received by technician (from organization)
     */
    public function index(Request $request)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Message::forTechnician($technician->id, $technician->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
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
     * Get a specific message
     */
    public function show($id)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::forTechnician($technician->id, $technician->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
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
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = Message::forTechnician($technician->id, $technician->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
            ->findOrFail($id);

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'پیام به عنوان خوانده شده علامت‌گذاری شد.'
        ]);
    }
}
