# Pages Grid export data source

Phase 3C adds the concrete criteria and SQL-planning boundary for Page exports.
The screen's resolved `GridCriteria` is converted into Page-owned export criteria
without carrying the screen page number or page size.

Search, status and multiple Site IDs become separately bound parameters. Sorting
uses a fixed column allow-list and a two-value direction allow-list. Values are
never interpolated into SQL. The Page repository remains responsible for its
existing SELECT, joins and row projection, while the generic export service
retains the final row ceiling, formula safety and response policy.

This split keeps Page storage knowledge out of Admin Grid and prevents export
queries from silently exporting only the current pagination page.
