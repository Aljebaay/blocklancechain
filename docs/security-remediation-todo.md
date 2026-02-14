# Security Remediation TODO (2026-02-14)

## Scope
- [x] Replace unsafe remote `unserialize()` usage in registration flow.
- [x] Harden plugin deletion against path traversal and unsafe includes/deletes.
- [x] Enforce seller authentication on conversation AJAX endpoints.
- [x] Add CSRF protection for critical admin actions (`delete_user`, `approve_payout`, `decline_payout`, `delete_plugin`).
- [x] Remove dynamic SQL filter interpolation in `admin_logs`.
- [x] Remove committed runtime session files and keep the directory ignored.
- [x] Add a regression security check script and wire it to Composer/CI.

## Notes
- This pass focuses on the highest-risk findings identified in the review.
- Additional admin GET actions should be migrated to POST + CSRF in a follow-up hardening pass.
