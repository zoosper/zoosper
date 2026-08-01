# Compact Admin Grid workspace

The compact layout keeps the table above the fold. Filters and Columns are closed by default, active filters are represented by removable chips, view status and page size share one compact sticky row, and save-view naming is opened only when needed.

The implementation is shared in Admin Grid. Page owns the actual fields, permission, CSRF, row query and export route. JavaScript only toggles panels, removes filter values through the existing GET form, and resets pagination when page size changes.
