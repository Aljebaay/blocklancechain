<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SmtpSetting model - maps to legacy `smtp_settings` table.
 */
class SmtpSetting extends Model
{
    protected $table = 'smtp_settings';

    public $timestamps = false;

    protected $fillable = [
        'library',
        'enable_smtp',
        'host',
        'port',
        'secure',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
