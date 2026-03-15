<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function edit()
    {
        $keys = [
            'base_fare',
            'per_km_rate',
            'per_minute_rate',
            'system_notifications_enabled',
            'system_notification_template',
        ];

        $settings = AppSetting::whereIn('key', $keys)->get()->keyBy('key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'base_fare' => ['required', 'numeric', 'min:0'],
            'per_km_rate' => ['required', 'numeric', 'min:0'],
            'per_minute_rate' => ['required', 'numeric', 'min:0'],
            'system_notifications_enabled' => ['nullable', 'in:0,1'],
            'system_notification_template' => ['required', 'string'],
        ]);

        AppSetting::setValue('base_fare', $data['base_fare'], 'Base fare', 'pricing');
        AppSetting::setValue('per_km_rate', $data['per_km_rate'], 'Per km rate', 'pricing');
        AppSetting::setValue('per_minute_rate', $data['per_minute_rate'], 'Per minute rate', 'pricing');
        AppSetting::setValue('system_notifications_enabled', $data['system_notifications_enabled'] ?? '0', 'Enable notifications', 'notifications');
        AppSetting::setValue('system_notification_template', $data['system_notification_template'], 'Notification template', 'notifications');

        return back()->with('status', 'Paramètres sauvegardés.');
    }
}
