# Page content sanitisation testing

Run:

```bash
php tools/verify-page-content-sanitization.php
php tools/demo-page-content-sanitization.php
```

Manual browser test:

1. Edit a CMS page.
2. Add content such as:

```html
<p onclick="alert(1)">Text</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a>
```

3. Save the page.
4. Reload edit form and preview.
5. Confirm script/event handlers/javascript URLs have been removed.

Expected result:

```text
Scripts and unsafe attributes are removed before storage.
Frontend still renders allowed HTML.
```
