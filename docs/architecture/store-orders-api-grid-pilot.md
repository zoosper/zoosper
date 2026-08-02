# Store Orders API Grid pilot

The Store Orders module is the first real feature adapter for `zoosper-api-grid`. This phase adds endpoint-specific request, response and row mapping plus the Grid definition, but deliberately does not add a concrete network transport or admin route wiring.

The request mapper translates numbered pagination and trusted context into `/v3/orders/store` parameters. `store_code` and `kiosk_website_id` come only from `ApiGridContext`, not browser query values. The response mapper accepts the `records` and `total` envelope and emits a minimal list-row shape. Nested `order_data`, contact details, per-record pagination metadata and addresses are not exposed to the Grid result.

The supplied API contract demonstrates page and page-size support only. Search, sorting, filtering and export are therefore disabled until the upstream service explicitly supports complete-collection operations.
