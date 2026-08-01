# Phase 3L protected mutation-form IDs

Add `id="..."` to each form emitted by `GridWorkspaceMutationFormsRenderer`, using:

```php
GridWorkspaceMutationFormIds::forAction($action)
```

Save View and Set Default retain one hidden canonical field:

```html
<input type="hidden" name="view_name" value="">
```

Delete retains the server-resolved bookmark ID. Every form continues to include the host CSRF field, stable action value and complete normalised state.
