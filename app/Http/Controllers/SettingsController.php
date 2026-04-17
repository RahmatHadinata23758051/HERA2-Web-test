<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the settings form.
     */
    public function index(Request $request)
    {
        $settings = AppSetting::pluck('value', 'key')->toArray();

        $defaultSettings = [
            'app_name'        => 'HERA',
            'app_version'     => '2.0',
            'app_institution' => 'Universitas Hasanuddin',
            'app_description' => 'Real-time Hexavalent Chromium Monitoring System',
            'app_copyright'   => 'Universitas Hasanuddin',
            'app_year'        => date('Y'),
            'app_logo'        => ''
        ];

        $settings = array_merge($defaultSettings, $settings);

        return view('settings.index', compact('settings'));
    }

    /**
     * Update the application settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_version' => 'required|string|max:50',
            'app_institution' => 'required|string|max:255',
            'app_description' => 'nullable|string',
            'app_copyright' => 'required|string|max:255',
            'app_year' => 'required|string|max:4',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048'
        ]);

        $settingsToUpdate = $request->except(['_token', '_method', 'app_logo']);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store the file directly in storage/app/public/logo
            $path = $file->storeAs('logo', $filename, 'public');
            
            // Save the asset path to the settings
            $settingsToUpdate['app_logo'] = 'storage/' . $path;
            
            // If replacing, we could theoretically delete the old one here to save space,
            // but keeping it simple for now to avoid accidental deletions of default assets.
        }

        // Update each setting in the database
        foreach ($settingsToUpdate as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Update Settings',
            'details' => 'User updated global application settings'
        ]);

        return redirect()->route('settings.index')->with('success', 'Pengaturan aplikasi berhasil diperbarui!');
    }
}
