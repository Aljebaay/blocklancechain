<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Conversation model - maps to legacy `inbox` table.
 */
class Conversation extends Model
{
    protected $table = 'inbox';

    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'status',
        'date',
        'last_activity',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'sender_id', 'seller_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'receiver_id', 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'inbox_id', 'id');
    }
}
