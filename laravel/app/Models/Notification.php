<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification model - maps to legacy `notifications` table.
 */
class Notification extends Model
{
    protected $table = 'notifications';

    public $timestamps = false;

    protected $fillable = [
        'notification_seller_id',
        'notification_type',
        'notification_text',
        'notification_url',
        'notification_date',
        'notification_read',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'notification_seller_id', 'seller_id');
    }
}
