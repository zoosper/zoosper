# Frontend HTML rendering testing

Run:

```bash
php tools/verify-frontend-sanitised-html-rendering.php
php tools/diagnose-frontend-content-escaping.php
php tools/verify-html-sanitizer.php
php tools/verify-page-content-sanitization.php
php tools/verify-block-json-content-model.php
php tools/verify-service-providers.php
```

Browser test:

```text
/
```

Expected frontend output should render tags as HTML:

```text
Welcome heading appears as a heading.
Paragraphs appear as paragraphs.
Lists appear as lists.
```

It should not display visible literal tags such as:

```text
<h2>Welcome</h2>
```
