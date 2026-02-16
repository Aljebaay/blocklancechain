<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Language model - maps to legacy `languages` table.
 */
class Language extends Model
{
    protected $table = 'languages';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'direction',
        'template_folder',
        'default_lang',
    ];
}
