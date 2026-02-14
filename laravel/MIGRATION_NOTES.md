# Laravel Bridge Notes

## Legacy write DB guard (2026-02-14)
- Health/info and other read-only endpoints must boot without any `LEGACY_WRITE_DB_*` env vars.
- Any write flow that touches the legacy write database must call `App\Support\LegacyWriteConnection::connection()` (or `LegacyWrite::ensureConfigured()` before using the connection) so missing credentials fail fast at the point of use.
