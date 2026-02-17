<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalFaq extends Model
{
    protected $table = 'proposal_faqs';

    public $timestamps = false;

    protected $fillable = [
        'proposal_id',
        'question',
        'answer',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }
}
