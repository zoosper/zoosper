# Frontend page view noescape testing

Run:

```bash
php tools/verify-frontend-page-view-noescape.php
php tools/diagnose-page-view-template-selection.php
php tools/verify-frontend-sanitised-html-rendering.php
php tools/verify-html-sanitizer.php
php tools/verify-page-content-sanitization.php
php tools/verify-service-providers.php
```

Browser check:

```text
/
```

Expected:

```text
Heading renders as heading.
Paragraphs render as paragraphs.
Lists render as lists.
Raw HTML tags are not visible as text.
```
