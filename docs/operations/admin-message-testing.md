# Admin message testing

Run:

```bash
php tools/verify-admin-flash-messages.php
php tools/verify-service-providers.php
```

Manual browser test:

1. Open `/admin/pages/edit?id=1`.
2. Save the page.
3. Confirm one success message appears.
4. Save again and confirm messages do not pile up.
5. Try publish/unpublish and confirm a clear success message appears.
