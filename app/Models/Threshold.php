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
        'ni_normal_max'  => ['value' => 0.020000, 'unit' => 'mg/L', 'label' => 'Batas Atas Kondisi Normal Nickel'],
        'ni_warning_max' => ['value' => 0.040000, 'unit' => 'mg/L', 'label' => 'Batas Atas Kondisi Warning Nickel'],
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
            $rows = static::whereIn('key', ['cr_normal_max', 'cr_warning_max'])->pluck('value', 'key');

            return [
                'cr_normal_max'  => (float) ($rows['cr_normal_max']  ?? static::DEFAULTS['cr_normal_max']['value']),
                'cr_warning_max' => (float) ($rows['cr_warning_max'] ?? static::DEFAULTS['cr_warning_max']['value']),
            ];
        });
    }

    /**
     * Ambil semua threshold Ni (dengan cache 10 menit).
     * Return: ['ni_normal_max' => 0.02, 'ni_warning_max' => 0.04]
     */
    public static function getNiThresholds(): array
    {
        return Cache::remember('ni_thresholds', 600, function () {
            $rows = static::whereIn('key', ['ni_normal_max', 'ni_warning_max'])->pluck('value', 'key');

            return [
                'ni_normal_max'  => (float) ($rows['ni_normal_max']  ?? static::DEFAULTS['ni_normal_max']['value']),
                'ni_warning_max' => (float) ($rows['ni_warning_max'] ?? static::DEFAULTS['ni_warning_max']['value']),
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
     * Klasifikasikan nilai Ni berdasarkan threshold saat ini.
     */
    public static function classifyNi(float $ni): string
    {
        $t = static::getNiThresholds();

        if ($ni >= $t['ni_warning_max']) return 'danger';
        if ($ni >= $t['ni_normal_max'])  return 'warning';
        return 'normal';
    }

    /**
     * Hapus cache setelah threshold diperbarui.
     */
    public static function clearCache(): void
    {
        Cache::forget('cr_thresholds');
        Cache::forget('ni_thresholds');
    }
}

