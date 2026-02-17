<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerLevelMeta extends Model
{
    protected $table = 'seller_levels_meta';

    public $timestamps = false;

    protected $fillable = [
        'level_id',
        'language_id',
        'title',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(SellerLevel::class, 'level_id', 'id');
    }
}
