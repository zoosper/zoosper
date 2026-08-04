# Publish selected UI activation

The Pages browser manifest now exposes `export.selected` and `page.publish`. The shared server-mutation browser controller reads checked `selected_ids[]` controls, enforces the declared maximum, requests explicit confirmation and submits `_csrf_token`, `bulk_action`, `confirmed_action` and selected identities to the protected Page endpoint. The server remains authoritative for every validation and permission boundary.
