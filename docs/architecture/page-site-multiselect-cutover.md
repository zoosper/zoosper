# Pages Site multiselect cutover

The Page Site filter presents active Site names while submitting internal IDs as
`site_id[]`. `GridMultiselectRenderer` provides escaped accessible markup and
`PageSiteFilterSql` produces a bound `IN` predicate for multiple selections.

The final controller/repository integration must compose these primitives with
`PageSiteFilterOptions`, preserve selected values across sort and pagination,
and bind every generated integer parameter through PDO. The raw Site ID text
field should be removed only in that coordinated cutover.
