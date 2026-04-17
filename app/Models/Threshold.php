<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Threshold extends Model
{
    protected $fillable = ['key', 'value', 'unit', 'label', 'updated_by'];

    // Default values (fallback jika DB kosong)
    public const DEFAULTS = [
        'cr_normal_max'  => ['value' => 0.050000, 'unit' => 'mg/L', 'label' => 'Batas Atas Kondisi Normal Chromium'],
        'cr_warning_max' => ['value' => 0.100000, 'unit' => 'mg/L', 'label' => 'Batas Atas Kondisi Warning Chromium'],
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ambil semua threshold Cr (dengan cache 10 menit).
     * Return: ['cr_normal_max' => 0.05, 'cr_warning_max' => 0.10]
     */
    public static function getCrThresholds(): array
    {
        return Cache::remember('cr_thresholds', 600, function () {
            $rows = static::whereIn('key', array_keys(static::DEFAULTS))->pluck('value', 'key');

            return [
                'cr_normal_max'  => (float) ($rows['cr_normal_max']  ?? static::DEFAULTS['cr_normal_max']['value']),
                'cr_warning_max' => (float) ($rows['cr_warning_max'] ?? static::DEFAULTS['cr_warning_max']['value']),
            ];
        });
    }

    /**
     * Klasifikasikan nilai Cr berdasarkan threshold saat ini.
     */
    public static function classifyCr(float $cr): string
    {
        $t = static::getCrThresholds();

        if ($cr >= $t['cr_warning_max']) return 'danger';
        if ($cr >= $t['cr_normal_max'])  return 'warning';
        return 'normal';
    }

    /**
     * Hapus cache setelah threshold diperbarui.
     */
    public static function clearCache(): void
    {
        Cache::forget('cr_thresholds');
    }
}
