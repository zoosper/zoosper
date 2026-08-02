# API Grid registration and page composition

Feature modules register immutable `ApiGridDefinition` objects. Definitions identify the admin route, permission, data-source service, generic Grid definition and allowed page sizes, but never contain API credentials or a browser-overridable base URL.

`ApiGridRegistry` rejects duplicate keys and routes. `ApiGridQueryFactory` converts untrusted request values into a neutral `GridQuery`, retaining only sorting, filtering and search operations declared by the data source capabilities. `ApiGridPageBuilder` resolves the registered data source and returns an `ApiGridPage` containing definition, constrained query, result and capabilities.

This is page composition data, not a universal controller. Route registration, permission enforcement, admin layout rendering and feature-specific context stay in the integrating module until the Store Orders pilot proves the final admin integration boundary.
