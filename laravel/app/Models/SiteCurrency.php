<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SiteCurrency model - maps to legacy `site_currencies` table.
 */
class SiteCurrency extends Model
{
    protected $table = 'site_currencies';

    public $timestamps = false;

    protected $fillable = [
        'currency_id',
        'position',
        'format',
        'rate',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
