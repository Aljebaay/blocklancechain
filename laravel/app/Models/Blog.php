<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blog';

    public $timestamps = false;

    protected $fillable = [
        'blog_title',
        'blog_content',
        'blog_image',
        'blog_author',
        'blog_date',
        'blog_slug',
        'blog_status',
    ];
}
