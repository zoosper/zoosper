# Page Momentum Route Metadata Test Escape Hotfix

## Issue

The Page Momentum route metadata test used `json_encode()` and then asserted that the encoded string contained `/admin/page-momentum`.

PHP escapes slashes by default in JSON output, so the encoded string contains `\/admin\/page-momentum` even though the real route path value is correct.

## Fix

The test now reads the route array directly and checks:

- `name === admin.page_momentum.index`
- `path === /admin/page-momentum`
- `permission === page.manage`

This tests the actual metadata value instead of JSON escape formatting.
