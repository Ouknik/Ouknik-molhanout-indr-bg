<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Update text/select settings
        $settingKeys = [
            'primary_color', 'secondary_color', 'accent_color', 'font_family',
            'app_name', 'currency', 'default_language',
            'max_delivery_radius', 'offer_expiry_hours', 'admin_email',
        ];

        foreach ($settingKeys as $key) {
            if ($request->has($key)) {
                AppSetting::setValue($key, $request->input($key));
            }
        }

        // Handle boolean/checkbox values (unchecked = absent from request)
        $booleanKeys = ['require_verification', 'maintenance_mode', 'push_notifications', 'email_notifications'];
        foreach ($booleanKeys as $key) {
            AppSetting::setValue($key, $request->has($key) ? '1' : '0');
        }

        // Handle file uploads (logo)
        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('branding', 'public');
            AppSetting::setValue('app_logo', $path);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
