<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InboxMessage model - maps to legacy `inbox_messages` table.
 */
class InboxMessage extends Model
{
    protected $table = 'inbox_messages';

    public $timestamps = false;

    protected $fillable = [
        'inbox_id',
        'sender_id',
        'message',
        'date',
        'file',
        'file_name',
        'file_size',
        'read_status',
        'offer_id',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'inbox_id', 'id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'sender_id', 'seller_id');
    }
}
