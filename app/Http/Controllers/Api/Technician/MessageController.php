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

        $messages = $query->get();

        $items = $messages->map(function ($message) {
            $message->created_at_jalali = Jalalian::forge($message->created_at)->format('Y/m/d H:i:s');
            if ($message->read_at) {
                $message->read_at_jalali = Jalalian::forge($message->read_at)->format('Y/m/d H:i:s');
            }
            
            // Format sender information
            if ($message->sender) {
                if ($message->sender_type === Message::SENDER_TYPE_ADMIN) {
                    $message->sender_name = 'مدیریت سیستم';
                } elseif ($message->sender_type === Message::SENDER_TYPE_ORGANIZATION) {
                    $message->sender_name = $message->sender->name ?? 'سازمان';
                }
            } else {
                $message->sender_name = 'سیستم';
            }
            
            return $message;
        });

        return response()->json([
            'success' => true,
            'data' => $items->all()
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

    /**
     * Get unread messages count
     */
    public function unreadCount()
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $count = Message::forTechnician($technician->id, $technician->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark all messages as read
     */
    public function markAllAsRead()
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $updated = Message::forTechnician($technician->id, $technician->organization_id)
            ->where('receiver_type', Message::RECEIVER_TYPE_TECHNICIAN)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'تمام پیام‌ها به عنوان خوانده شده علامت‌گذاری شدند.',
            'data' => [
                'updated_count' => $updated
            ]
        ]);
    }
}
