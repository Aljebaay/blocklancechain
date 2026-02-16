<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ChildCategoryMeta model - maps to legacy `child_cats_meta` table.
 */
class ChildCategoryMeta extends Model
{
    protected $table = 'child_cats_meta';

    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'language_id',
        'child_title',
        'child_desc',
    ];

    public function childCategory(): BelongsTo
    {
        return $this->belongsTo(ChildCategory::class, 'child_id', 'child_id');
    }
}
