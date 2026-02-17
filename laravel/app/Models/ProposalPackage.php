<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalPackage extends Model
{
    protected $table = 'proposal_packages';

    public $timestamps = false;

    protected $fillable = [
        'proposal_id',
        'package_type',
        'package_title',
        'package_desc',
        'package_price',
        'package_delivery_time',
        'package_revisions',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }
}
