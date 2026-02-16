<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Currency model - maps to legacy `currencies` table.
 */
class Currency extends Model
{
    protected $table = 'currencies';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'symbol',
        'code',
    ];
}
