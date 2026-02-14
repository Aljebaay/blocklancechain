<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyUser extends Model
{
    protected $connection = 'legacy';
    protected $table = 'sellers';
    protected $guarded = [];
    public $timestamps = false;
}
