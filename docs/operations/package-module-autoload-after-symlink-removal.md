# Package module autoload after symlink removal

After `app/zoosper-media` is removed, the root autoload synchroniser must still discover the media module from:

```text
packages/zoosper-media/module.php
```

and write root Composer mappings like:

```json
"Zoosper\\Media\\": "packages/zoosper-media/src/"
```

Run:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Expected Composer sync output should include media again, typically increasing the mapping count from 10/4 back to 11/5 while the media package remains in `packages/`.
