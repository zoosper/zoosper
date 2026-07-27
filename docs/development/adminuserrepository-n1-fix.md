# AdminUserRepository N+1 Fix

`all()` and `search()` now batch-load permissions for the full result set in one `WHERE user_id IN (...)` query. `allForAssignment()` skips permission loading because the assignment checklist only needs id/name/email. A CountingPdo regression test asserts exact statement counts.
