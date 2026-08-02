# Phase 6C: Rendered asset wiring

The rendered role page proved that module asset declarations from `zoosper-auth` were not entering the admin layout. Phase 6C therefore wires the two module-owned assets from the permission-tree partial itself through the existing `/asset/zoosper-auth/...` route. The runtime also discovers the existing role form directly, so the current fieldset markup needs no wrapper rewrite.
