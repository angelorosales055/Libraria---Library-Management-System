<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::forUser(auth()->id())
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'icon' => $n->icon,
                'icon_color' => $n->icon_color,
                'read' => !is_null($n->read_at),
                'created_at' => $n->created_at->diffForHumans(),
                'created_at_iso' => $n->created_at->toIso8601String(),
            ]);

        $unreadCount = Notification::forUser(auth()->id())->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        $unreadCount = Notification::forUser(auth()->id())->unread()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        Notification::markAllAsRead(auth()->id());

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}

