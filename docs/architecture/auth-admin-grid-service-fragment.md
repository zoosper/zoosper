# Auth admin Grid service fragment

Phase 4S adds a standalone service-definition fragment for the complete Admin Users
and Roles Grid read side. It declares the object-graph factory and the two page
builders, while leaving the existing Auth service manifest and live controllers
unchanged.

Keeping the fragment separate makes the next runtime merge reviewable and prevents a
blind rewrite of the established Auth service registrations. The fragment depends on
the host-owned PDO and `GridViewStateResolver`; Grid column registry and orderer are
optional enhancements.
