# Grid CSV security

`zoosper/grid` owns CSV serialisation and therefore owns spreadsheet-formula
neutralisation as a package-level invariant.

Every exported heading and cell is converted to a scalar and checked before it
is passed to `fputcsv()`. Values beginning with `=`, `+`, `-`, `@`, tab,
carriage return or newline receive a leading apostrophe so spreadsheet programs
interpret the content as literal text rather than a formula.

The exporter continues to honour selected/default-visible columns and excludes
HTML action columns. HTTP controllers remain responsible for authentication,
authorisation, row limits, response headers and audit logging when CSV download
routes are introduced.
