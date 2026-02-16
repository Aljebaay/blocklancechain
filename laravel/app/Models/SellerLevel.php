<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerLevel extends Model
{
    protected $table = 'seller_levels';
    public $timestamps = false;

    protected $fillable = [
        'level_title',
        'level_order',
    ];

    public function meta(): HasMany
    {
        return $this->hasMany(SellerLevelMeta::class, 'level_id', 'id');
    }
}
