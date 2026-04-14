<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldTest extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'suhu_air',
        'suhu_lingkungan',
        'kelembapan',
        'ec',
        'tds',
        'ph',
        'tegangan',
        'cr_estimated',
    ];

    /**
     * Get the user that created the field test.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
