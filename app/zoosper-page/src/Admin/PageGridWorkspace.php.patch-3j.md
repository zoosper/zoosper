# Phase 3J Page workspace renderer cutover

Update the current `PageGridWorkspace` constructor dependency from:

```php
GridWorkspaceRenderer $renderer
```

to:

```php
GridWorkspaceDecoratedRenderer $renderer
```

No method-body change is required because both expose:

```php
render(GridViewState $state, string $formAction): string
```

This additive cutover keeps Pages unaware of bookmark comparison and status HTML.
The shared Admin Grid package owns the complete workspace presentation contract.
