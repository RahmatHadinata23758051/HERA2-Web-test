<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $table = 'app_versions';

    protected $fillable = [
        'version',
        'build_number',
        'download_url',
        'changelog',
        'file_size',
        'is_force_update',
        'is_active',
    ];

    protected $casts = [
        'build_number' => 'integer',
        'is_force_update' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the latest active release for mobile app clients.
     */
    public static function getLatestActive(): ?self
    {
        return self::where('is_active', true)
            ->orderBy('build_number', 'desc')
            ->first();
    }
}
