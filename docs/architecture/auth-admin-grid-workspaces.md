# Auth admin Grid workspaces

Phase 4P adds the Auth-owned workspace composition seam for Admin Users and Roles.
Both workspaces require the authenticated administrator ID and keep their Grid keys
and action URLs server-owned.

The resolver's visibility-filtered state remains authoritative for rows, navigation,
exports and persistence. Controls render from the complete ordered definition so a
hidden user or role column remains available as an unchecked recovery option.

This phase does not change live controllers, permissions, CSRF, user writes, role
writes, role assignments, permission assignments, passwords or two-factor flows.
