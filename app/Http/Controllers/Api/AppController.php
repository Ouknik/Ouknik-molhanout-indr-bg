<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends Controller
{
    /**
     * Get app configuration (theme, settings) — called at startup.
     */
    public function config(): JsonResponse
    {
        $settings = AppSetting::getAllSettings();

        return response()->json([
            'success' => true,
            'data'    => $settings,
        ]);
    }

    /**
     * Get theme configuration for mobile app.
     */
    public function theme(): JsonResponse
    {
        $theme = AppSetting::getThemeConfig();

        return response()->json([
            'success' => true,
            'data'    => $theme,
        ]);
    }

    /**
     * Get user notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $notifications = NotificationLog::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $notifications->items(),
            'meta'    => [
                'unread_count' => NotificationLog::where('user_id', $request->user()->id)
                    ->where('is_read', false)->count(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(int $id, Request $request): JsonResponse
    {
        $notification = NotificationLog::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        NotificationLog::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
