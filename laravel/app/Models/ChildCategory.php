<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ChildCategory model - maps to legacy `categories_children` table.
 * Primary key: child_id (NOT id).
 */
class ChildCategory extends Model
{
    protected $table = 'categories_children';

    protected $primaryKey = 'child_id';

    public $timestamps = false;

    protected $fillable = [
        'child_parent_id',
        'child_title',
        'child_url',
        'child_desc',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'child_parent_id', 'cat_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'proposal_sub_category', 'child_id');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(ChildCategoryMeta::class, 'child_id', 'child_id');
    }
}
