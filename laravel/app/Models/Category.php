<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Category model - maps to legacy `categories` table.
 * Primary key: cat_id (NOT id).
 */
class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_title',
        'cat_url',
        'cat_image',
        'cat_icon',
        'cat_desc',
        'cat_featured',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(ChildCategory::class, 'child_parent_id', 'cat_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'proposal_category', 'cat_id');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(CategoryMeta::class, 'cat_id', 'cat_id');
    }

    public function scopeFeatured($query)
    {
        return $query->where('cat_featured', 'yes');
    }
}
