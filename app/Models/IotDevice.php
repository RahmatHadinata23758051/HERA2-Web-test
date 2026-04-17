<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'uid', 'location_name', 'latitude', 'longitude',
        'ip_address', 'firmware_version', 'status', 'last_seen_at',
        'installed_at', 'notes'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_seen_at' => 'datetime',
        'installed_at' => 'date',
    ];

    /**
     * Tentukan apakah device sedang Online (nge-ping dalam 15 menit terakhir).
     */
    public function getIsOnlineAttribute()
    {
        if (!$this->last_seen_at || $this->status === 'inactive') {
            return false;
        }

        // Kalau ada data masuk 15 menit terakhir, anggap online = hijau
        return $this->last_seen_at->diffInMinutes(now()) <= 15;
    }
}
