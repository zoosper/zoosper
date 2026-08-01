# Auth admin Grid SQL plans

Phase 4N adds parameterised SQL-plan builders for the Admin Users and Roles read
models. Filter values are returned separately from SQL, and sort identifiers are
selected only from server-owned allow-lists.

The builders intentionally stop before concrete table selection and hydration. The
existing Auth schema and repository projections must be used by the forthcoming PDO
adapters, avoiding assumptions about sensitive user columns or permission joins.
