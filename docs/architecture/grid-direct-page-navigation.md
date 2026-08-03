# Direct Grid page navigation

The shared Admin Grid enhances the existing server-rendered `Page N of M` navigation with a numeric page input and Go button. The control preserves every current query parameter, including filters, sort, page size, visible columns, column order and `bookmark_id`, while replacing only `page`.

The existing server-generated Previous and Next links remain the no-JavaScript fallback. Values outside `1..M` are rejected before navigation; the server remains authoritative for the requested page.
