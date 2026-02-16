<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RequestOffer model - maps to legacy `request_offers` table.
 */
class RequestOffer extends Model
{
    protected $table = 'request_offers';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'seller_id',
        'offer_desc',
        'offer_amount',
        'offer_delivery_time',
        'offer_status',
        'offer_date',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(BuyerRequest::class, 'request_id', 'id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'seller_id');
    }
}
