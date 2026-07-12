# Phase 1.07 - Explicit UserAdminController Locale UI Integration

This phase targets the exact admin-user management controller instead of broad text-matching.

## Safety rules

- Never patch `LoginController`.
- Only patch `UserAdminController`.
- If a safe email-field insertion point cannot be found, stop and use diagnostics.
- Keep locale options backed by `SupportedLocaleProvider`/renderer foundation.
