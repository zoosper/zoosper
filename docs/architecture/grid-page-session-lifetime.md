# Canonical Grid page and session lifetime

`GridStateNormaliser` now preserves the requested positive page when producing `GridCriteria`; previously it discarded `page`, so remote Grids always queried page 1 even when the URL advanced.

Application session startup now reads `SESSION_LIFETIME_SECONDS`, defaults to 28800 seconds, and clamps the value to 300..604800 seconds. The same value is applied to `session.gc_maxlifetime` and the session cookie lifetime before `session_start()`. Production PHP session storage must honour at least this lifetime; external cleanup jobs can still remove session files independently.
