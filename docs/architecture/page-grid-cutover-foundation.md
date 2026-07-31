# Page grid cutover foundation

The Pages admin currently has a bespoke criteria class, repository query and PHP
table template. Phase 2E introduces the shared-grid boundary without pretending
the visual cutover is already complete.

`PageGridDefinition` declares the canonical columns, filters, sort defaults and
a stable grid key (`admin.pages`). `PageGridDataSource` adapts the existing,
proven `PageGridRepository` to `GridDataSourceInterface`. Module contributions
can target the stable key through `config/grid_columns.php` and
`GridColumnRegistry`.

The next cutover phase will wire `GridCriteria`, `GridHtmlRenderer`, definition
and data source into `PageAdminController`, replace the manual table/filter/
pagination markup with `gridHtml`, and then retire `PageGridCriteria` after
behavioural parity is proven. Keeping that final wiring separate avoids mixing a
large controller/template change with the new extension boundary.
