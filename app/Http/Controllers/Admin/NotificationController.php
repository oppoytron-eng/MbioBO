<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiNotification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $notifications = ApiNotification::with(['course'])
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('admin.notifications.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target' => ['required', 'in:global,chauffeurs,clients'],
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string'],
        ]);

        // Créer la notification principale
        $notification = ApiNotification::create([
            'course_id' => null,
            'type' => 'admin_broadcast',
            'message' => $data['message'],
            'payload' => [
                'target' => $data['target'],
                'title' => $data['title'] ?? 'Notification Admin'
            ],
        ]);

        // Envoyer aux utilisateurs cibles
        $targetUsers = match($data['target']) {
            'chauffeurs' => User::where('role', 'chauffeur')->get(),
            'clients' => User::where('role', 'client')->get(),
            'global' => User::all(),
            default => collect(),
        };

        foreach ($targetUsers as $user) {
            ApiNotification::create([
                'course_id' => null,
                'type' => 'admin_notification',
                'message' => $data['message'],
                'payload' => [
                    'source_notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'title' => $data['title'] ?? 'Notification Admin'
                ],
            ]);
        }

        return back()->with('status', 'Notification envoyée à ' . $targetUsers->count() . ' utilisateurs.');
    }

    public function destroy(ApiNotification $notification)
    {
        $notification->delete();
        return back()->with('status', 'Notification supprimée.');
    }
}
