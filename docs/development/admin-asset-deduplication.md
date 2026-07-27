# Admin Asset De-duplication

## Problem

`AdminAssetRegistry::all()` concatenated every enabled module's declared
assets with no de-duplication. Two modules (zoosper-admin, zoosper-page) both
declared an entry for the same physical `zoosper-tag-selector.css`, producing
two identical `<link>` tags on every admin page.

## Fix

De-duplicate by physical path (query string stripped), keeping the first
occurrence in the established sort order. Not a hard error — a marketplace of
independently-built modules coincidentally sharing a vendor asset is legitimate
and should degrade gracefully, not crash the admin.
