<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CategoryMeta model - maps to legacy `cats_meta` table.
 */
class CategoryMeta extends Model
{
    protected $table = 'cats_meta';

    public $timestamps = false;

    protected $fillable = [
        'cat_id',
        'language_id',
        'cat_title',
        'cat_desc',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }
}
