# Auth admin Grid PDO read models

Phase 4O implements narrow, read-only PDO adapters for the Admin Users and Roles
Grid boundaries. Both adapters use the parameterised SQL plans, bind pagination as
integers and return only the listing projection.

Admin Users exposes only ID, name, email and status. Roles exposes only ID, label
and code. Password hashes, two-factor data, recovery codes, permission payloads and
assignment joins remain outside the listing read models.
