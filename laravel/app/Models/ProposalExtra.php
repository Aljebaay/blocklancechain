<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalExtra extends Model
{
    protected $table = 'proposal_extras';

    public $timestamps = false;

    protected $fillable = [
        'proposal_id',
        'extra_title',
        'extra_desc',
        'extra_price',
        'extra_delivery_time',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }
}
