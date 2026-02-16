<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiSetting extends Model
{
    protected $table = 'api_settings';
    public $timestamps = false;

    protected $fillable = [
        'enable_s3',
        's3_key',
        's3_secret',
        's3_region',
        's3_bucket',
    ];
}
