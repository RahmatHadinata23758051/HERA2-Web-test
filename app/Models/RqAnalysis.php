<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RqAnalysis extends Model
{
    protected $table = 'rq_analyses';

    protected $fillable = [
        // Meta
        'rq_batch_id',
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
        'latitude',
        'longitude',
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
        'latitude'       => 'float',
        'longitude'      => 'float',
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
        'chromium' => 'Kromium (Cr)',
        'pb'       => 'Timbal (Pb)',
        'nickel'   => 'Nikel (Ni)',
        'arsenic'  => 'Arsen (As)',
        'cd'       => 'Kadmium (Cd)',
    ];

    // RfD default per polutan (mg/kg/hari)
    public static array $rfdDefaults = [
        'chromium' => 0.003,
        'pb'       => 0.0014,
        'nickel'   => 0.02,
        'arsenic'  => 0.0003,
        'cd'       => 0.001,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(RqBatch::class, 'rq_batch_id');
    }

    /**
     * Scope filter by pollutant type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('pollutant_type', $type);
    }
}
