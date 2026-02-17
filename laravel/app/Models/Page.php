<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    public $timestamps = false;

    protected $fillable = [
        'page_title',
        'page_slug',
        'page_content',
        'page_status',
        'language_id',
    ];
}
