# Phase 1.06 - Admin User Locale Preference UI

This phase introduces the rendering foundation for the admin-user locale preference field.

## Design

- Locale options come from `SupportedLocaleProvider`.
- The field supports a blank value, meaning “use configured admin locale”.
- Locale values remain strictly validated by the resolver/provider layer.
- A conservative apply tool can patch the current admin-user form when the target can be safely detected.
