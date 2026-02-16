<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BuyerRequest model - maps to legacy `buyer_requests` table.
 */
class BuyerRequest extends Model
{
    protected $table = 'buyer_requests';

    public $timestamps = false;

    protected $fillable = [
        'buyer_id',
        'request_title',
        'request_desc',
        'request_category',
        'request_sub_category',
        'request_budget',
        'request_delivery_time',
        'request_status',
        'request_date',
        'request_file',
        'request_file_name',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'buyer_id', 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'request_category', 'id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(RequestOffer::class, 'request_id', 'id');
    }
}
