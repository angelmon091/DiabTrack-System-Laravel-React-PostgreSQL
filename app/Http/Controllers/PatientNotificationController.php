<?php

namespace App\Http\Controllers;

use App\Models\PatientNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientNotificationController extends Controller
{
    public function markRead(PatientNotification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->markRead();
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        PatientNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function destroy(PatientNotification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->delete();
        return response()->json(['ok' => true]);
    }

    public function destroyAll()
    {
        PatientNotification::where('user_id', Auth::id())->delete();
        return response()->json(['ok' => true]);
    }
}
