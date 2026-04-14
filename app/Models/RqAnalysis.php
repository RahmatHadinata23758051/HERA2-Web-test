<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RqAnalysis extends Model
{
    protected $table = 'rq_analyses';

    protected $fillable = [
        // Meta
        'pollutant_type',
        'source',
        'user_id',
        // Data responden
        'no_responden',
        'nama',
        'umur',
        'wb',
        // Variabel input pajanan
        'f',
        'c',
        'r',
        'rfd',
        'tavg',
        'dt_input',
        // Kalkulasi Intake
        'intake_realtime',
        'intake_5th',
        'intake_10th',
        'intake_15th',
        'intake_20th',
        'intake_25th',
        'intake_30th',
        // Kalkulasi RQ
        'rq_realtime',
        'rq_5th',
        'rq_10th',
        'rq_15th',
        'rq_20th',
        'rq_25th',
        'rq_30th',
    ];

    protected $casts = [
        'umur'           => 'float',
        'wb'             => 'float',
        'f'              => 'float',
        'c'              => 'float',
        'r'              => 'float',
        'rfd'            => 'float',
        'tavg'           => 'float',
        'dt_input'       => 'float',
        'intake_realtime' => 'float',
        'intake_5th'     => 'float',
        'intake_10th'    => 'float',
        'intake_15th'    => 'float',
        'intake_20th'    => 'float',
        'intake_25th'    => 'float',
        'intake_30th'    => 'float',
        'rq_realtime'    => 'float',
        'rq_5th'         => 'float',
        'rq_10th'        => 'float',
        'rq_15th'        => 'float',
        'rq_20th'        => 'float',
        'rq_25th'        => 'float',
        'rq_30th'        => 'float',
    ];

    // Label nama polutan untuk UI
    public static array $pollutantLabels = [
        'nitrat' => 'Nitrat (NO₃)',
        'pb'     => 'Timbal (Pb)',
        'cd'     => 'Kadmium (Cd)',
        'ph'     => 'Fosfor (Ph)',
        'f'      => 'Fluorida (F)',
    ];

    // RfD default per polutan (mg/kg/hari)
    public static array $rfdDefaults = [
        'nitrat' => 1.6,
        'pb'     => 0.0035,
        'cd'     => 0.0005,
        'ph'     => 20.0,
        'f'      => 0.06,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope filter by pollutant type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('pollutant_type', $type);
    }
}
