<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $notifications = PushNotification::latest('created_at')->paginate(15);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target' => ['required', 'in:global,chauffeurs,clients'],
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string'],
        ]);

        PushNotification::create([
            'target' => $data['target'],
            'title' => $data['title'] ?? null,
            'message' => $data['message'],
            'sent_at' => now(),
        ]);

        return back()->with('status', 'Notification envoyée.');
    }

    public function destroy(PushNotification $notification)
    {
        $notification->delete();
        return back()->with('status', 'Notification supprimée.');
    }
}
