<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyUser extends Model
{
    protected $connection = 'legacy';
    protected $table = 'sellers';
    public $timestamps = false;

    /**
     * Disallow mass assignment and all write operations; legacy data is read-only.
     *
     * @var array<int, string>
     */
    protected $guarded = ['*'];

    public function save(array $options = []): bool
    {
        throw new \LogicException('LegacyUser is read-only');
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('LegacyUser is read-only');
    }

    public function delete(): bool
    {
        throw new \LogicException('LegacyUser is read-only');
    }

    public function forceDelete(): bool
    {
        throw new \LogicException('LegacyUser is read-only');
    }
}
