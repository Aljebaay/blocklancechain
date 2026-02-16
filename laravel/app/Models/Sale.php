<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sale model - maps to legacy `sales` table.
 */
class Sale extends Model
{
    protected $table = 'sales';

    public $timestamps = false;

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'order_id',
        'proposal_id',
        'amount',
        'fee',
        'net',
        'date',
        'type',
    ];
}
