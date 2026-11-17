<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppVersionWebController extends Controller
{
    /**
     * Show form to manage app versions and history.
     */
    public function index(): View
    {
        $versions = AppVersion::orderBy('build_number', 'desc')->get();
        $latest = AppVersion::getLatestActive();

        return view('settings.app-version', compact('versions', 'latest'));
    }

    /**
     * Store a new app version release.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version' => ['required', 'string', 'max:30'],
            'build_number' => ['required', 'integer', 'min:1'],
            'download_url' => ['required', 'url'],
            'changelog' => ['nullable', 'string', 'max:5000'],
            'file_size' => ['nullable', 'string', 'max:50'],
            'is_force_update' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_force_update'] = $request->has('is_force_update');
        $validated['is_active'] = $request->has('is_active') ? true : true;

        AppVersion::updateOrCreate(
            ['build_number' => $validated['build_number']],
            $validated
        );

        return redirect()->route('settings.app-version')->with('success', 'Versi aplikasi berhasil disimpan dan siap disajikan ke mobile app!');
    }
}
