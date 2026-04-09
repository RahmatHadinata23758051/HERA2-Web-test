<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'ec', 'tds', 'ph', 'suhu_air', 'suhu_lingkungan',
        'kelembapan', 'tegangan', 'cr_estimated', 'status'
    ];
}
