<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementBar extends Model
{
    protected $table = 'announcement_bar';

    public $timestamps = false;

    protected $fillable = [
        'language_id',
        'enable_bar',
        'bg_color',
        'text_color',
        'bar_text',
        'last_updated',
    ];
}
