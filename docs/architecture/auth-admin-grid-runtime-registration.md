# Auth admin Grid runtime registration

Phase 4T activates the previously tested Auth Grid service fragment from the existing
Auth service manifest. The fragment is unpacked at the beginning of the returned
service map, so any pre-existing explicit Auth registration later in the manifest
retains precedence.

The guarded installer is idempotent, requires exactly one top-level `return [` marker,
syntax-checks a temporary file with PHP 8.5 and activates it atomically. No controller,
route, permission, CSRF, password, two-factor or write behaviour changes in this phase.
