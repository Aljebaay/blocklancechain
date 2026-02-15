<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class LegacyWriteConnection
{
    /**
     * Get the legacy_write connection after ensuring required config is present.
     *
     * @throws \RuntimeException when required LEGACY_WRITE_DB_* env vars are missing.
     */
    public static function connection(): Connection
    {
        LegacyWrite::ensureConfigured();

        return DB::connection('legacy_write');
    }
}
