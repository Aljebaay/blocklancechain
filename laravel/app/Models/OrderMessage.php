<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMessage extends Model
{
    protected $table = 'order_messages';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'sender_id',
        'message',
        'file',
        'file_name',
        'file_size',
        'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'sender_id', 'seller_id');
    }
}
