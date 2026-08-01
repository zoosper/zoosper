# Pages Grid PDO export repository

Phase 3D provides the concrete PDO-backed implementation of
`PageGridExportRepositoryInterface`. It composes the Page-owned SELECT with the
safe SQL plan, binds every filter value with an explicit PDO type and yields rows
incrementally.

The projection uses stable Grid keys and includes `site_name` from the Site join,
so administrators receive readable Site names rather than only internal IDs.
There is no screen LIMIT/OFFSET. The generic Grid export policy remains the final
hard row ceiling and stops consuming the generator after the configured maximum.

The repository contains no request parsing, authentication, bookmark resolution,
CSV formatting or audit logic. Those remain in their existing layers.
