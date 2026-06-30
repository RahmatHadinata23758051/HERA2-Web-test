<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RqBatch extends Model
{
    protected $table = 'rq_batches';

    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * Get the user who created the batch.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the analyses inside this batch.
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(RqAnalysis::class, 'rq_batch_id');
    }
}
