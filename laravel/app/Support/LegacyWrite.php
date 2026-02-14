<?php

namespace App\Support;

class LegacyWrite
{
    /**
     * Ensure LEGACY_WRITE_DB_* env vars are present before attempting a write.
     *
     * @throws \RuntimeException when any required var is missing.
     */
    public static function ensureConfigured(): void
    {
        $required = [
            'LEGACY_WRITE_DB_HOST',
            'LEGACY_WRITE_DB_PORT',
            'LEGACY_WRITE_DB_DATABASE',
            'LEGACY_WRITE_DB_USERNAME',
            'LEGACY_WRITE_DB_PASSWORD',
        ];

        $missing = [];
        foreach ($required as $key) {
            $value = env($key);
            if ($value === null || $value === '') {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException('Missing legacy write DB config: ' . implode(', ', $missing));
        }
    }
}
