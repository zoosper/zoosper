# Pages shared-grid cutover

The Pages admin listing now uses `GridDefinition`, `GridCriteria`,
`GridColumnRegistry`, `GridDataSourceInterface` and `GridHtmlRenderer`.

The controller builds the contributed definition, parses only declared filter
and sortable keys, delegates to the existing page query through
`PageGridDataSource`, and passes `gridHtml` to the view. The manual table,
page-filter partial and pagination partial are no longer used by the page index.

`PageGridRepository` now honours an explicit allow-list for id, title, slug and
status ordering. Unknown sort values fall back to updated-at descending and are
never inserted into SQL. Existing search, status, site and page-size behaviour
remains provided through the adapter.

Per-admin visible columns, named bookmarks and CSV controls remain the next UI
integration layer. Their repositories and exporter already exist, but routes
and buttons require authenticated admin identity, permissions and CSRF review.
