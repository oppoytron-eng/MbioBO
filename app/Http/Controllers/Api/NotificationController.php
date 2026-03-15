<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
use App\Models\ApiNotification;
use App\Models\ApiNotificationChauffeur;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notifierChauffeurs($course_id)
    {
        $course = ApiCourse::findOrFail($course_id);

        $chauffeurs = User::where('role', 'chauffeur')
            ->where('est_actif', true)
            ->get();

        $notification = ApiNotification::create([
            'course_id' => $course->id,
            'type' => 'nouvelle_course',
            'message' => 'Nouvelle course disponible',
        ]);

        foreach ($chauffeurs as $chauffeur) {
            ApiNotificationChauffeur::create([
                'notification_id' => $notification->id,
                'chauffeur_id' => $chauffeur->id,
                'statut' => 'en_attente',
                'expire_le' => now()->addMinutes(2),
            ]);
        }

        return response()->json([
            'message' => 'Chauffeurs notifiés',
            'notification' => $notification,
            'nb_chauffeurs' => $chauffeurs->count(),
        ]);
    }

    public function mesNotifications(Request $request)
    {
        $notifications = ApiNotificationChauffeur::where('chauffeur_id', $request->user()->id)
            ->where('statut', 'en_attente')
            ->where('expire_le', '>', now())
            ->get();

        return response()->json($notifications);
    }

    public function marquerVue(Request $request, $id)
    {
        $notif = ApiNotificationChauffeur::where('id', $id)
            ->where('chauffeur_id', $request->user()->id)
            ->firstOrFail();

        $notif->update(['statut' => 'vue']);

        return response()->json(['message' => 'Notification marquée comme vue']);
    }
}
