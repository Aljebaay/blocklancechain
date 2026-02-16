<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AdminLog model - maps to legacy `admin_logs` table.
 */
class AdminLog extends Model
{
    protected $table = 'admin_logs';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'work',
        'work_id',
        'date',
        'status',
    ];
}
