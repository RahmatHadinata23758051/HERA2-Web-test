<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Threshold;
use Illuminate\Http\Request;

class ThresholdController extends Controller
{
    public function __construct()
    {
        // Hanya direksi yang boleh akses
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->role !== 'direksi') {
                abort(403, 'Halaman ini hanya dapat diakses oleh Direksi.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $thresholds = Threshold::whereIn('key', array_keys(Threshold::DEFAULTS))
            ->with('updatedBy')
            ->get()
            ->keyBy('key');

        // Pastikan semua key ada (fallback ke default jika belum di-seed)
        $current = [];
        foreach (Threshold::DEFAULTS as $key => $default) {
            $row = $thresholds->get($key);
            $current[$key] = [
                'value'      => $row ? (float) $row->value : $default['value'],
                'unit'       => $row ? $row->unit : $default['unit'],
                'label'      => $row ? $row->label : $default['label'],
                'updated_by' => $row?->updatedBy?->name,
                'updated_at' => $row?->updated_at,
            ];
        }

        return view('settings.threshold', compact('current'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cr_normal_max'  => 'required|numeric|min:0.000001|max:999',
            'cr_warning_max' => 'required|numeric|min:0.000001|max:999',
        ]);

        // Pastikan warning_max > normal_max
        if ($validated['cr_warning_max'] <= $validated['cr_normal_max']) {
            return back()
                ->withErrors(['cr_warning_max' => 'Batas Warning harus lebih besar dari batas Normal.'])
                ->withInput();
        }

        foreach (['cr_normal_max', 'cr_warning_max'] as $key) {
            Threshold::updateOrCreate(
                ['key' => $key],
                [
                    'value'      => $validated[$key],
                    'unit'       => Threshold::DEFAULTS[$key]['unit'],
                    'label'      => Threshold::DEFAULTS[$key]['label'],
                    'updated_by' => auth()->id(),
                ]
            );
        }

        // Hapus cache agar threshold baru langsung aktif
        Threshold::clearCache();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action'  => 'Update Threshold',
            'details' => "Threshold Cr diperbarui: Normal ≤ {$validated['cr_normal_max']} mg/L, Warning ≤ {$validated['cr_warning_max']} mg/L",
        ]);

        return redirect()
            ->route('settings.threshold')
            ->with('success', 'Threshold Chromium berhasil diperbarui! Klasifikasi status sensor baru langsung aktif.');
    }
}
