<?php

namespace App\Http\Controllers;

use App\Models\PatientNotification;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona la lectura individual y masiva de las notificaciones del paciente.
 */
class PatientNotificationController extends Controller
{
    public function markRead(PatientNotification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->markRead();

        return back(303);
    }

    public function markAllRead()
    {
        PatientNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back(303);
    }

    public function destroy(PatientNotification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->delete();

        return back(303);
    }

    public function destroyAll()
    {
        PatientNotification::where('user_id', Auth::id())->delete();

        return back(303);
    }
}
