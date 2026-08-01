# Pages Site multiselect filter

A raw Site ID input is not an appropriate administrator-facing filter. Phase 2N
adds the reusable `multiselect` filter type, typed value/label options and a
Pages option provider backed by active sites.

Sites are presented by name while their IDs remain internal submitted values.
Selected values are normalised as a list, with whitespace, duplicates and empty
items removed. The Pages repository cutover should bind each selected ID and use
an `IN (...)` predicate rather than interpolating IDs into SQL.

This build establishes the typed option and value contracts. The HTML renderer
and Page repository/controller wiring should consume these contracts in the next
screen cutover, preserving the shared Grid package boundary.
