# Runtime path safety testing

Clean accidental public runtime folders:

```bash
php tools/clean-public-runtime-directories.php --yes
```

Verify:

```bash
php tools/verify-runtime-path-safety.php
php tools/verify-html-sanitizer.php
php tools/verify-page-content-sanitization.php
php tools/verify-project-structure.php
php tools/audit-public-webroot.php
php tools/verify-service-providers.php
```

Browser test:

```text
/admin/pages/edit?id=1
Save page
```

Then run:

```bash
test ! -d public/var && echo "public/var absent"
test -d var/cache/htmlpurifier && echo "HTMLPurifier cache under project var"
```
