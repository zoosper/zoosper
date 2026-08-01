# Admin Grid query-state links

Phase 3E introduces one query serialiser for pagination, sorting and export URLs.
It preserves resolved filters, Site multiselection, page size, active bookmark,
visible columns and column order using RFC 3986 encoding.

Page-owned links use fixed local endpoints. No user ID, Grid key or redirect
selector is emitted. Pagination and sort links therefore retain the same view the
administrator sees, and the export URL resolves the same state server-side.

The serialiser is generic and can later be reused unchanged by Audit Log and
Login History. Templates should consume `PageGridLinks` rather than manually
reconstructing query parameters.
