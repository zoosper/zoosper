# Page Fallback Handler Signature Hotfix

## Issue

Phase 1.68a-l introduced `FallbackHandlerInterface::handle(mixed $request): mixed`, but existing decoupling adapter code already used `handle(object $request): mixed`.

That caused PHP to fail while loading `PageFallbackHandlerAdapter` because the implementation signature was incompatible with the interface.

## Fix

The fallback handler boundary now uses the object request contract consistently:

```php
public function handle(object $request): mixed;
```

Updated:

- `FallbackHandlerInterface`
- `NullFallbackHandler`
- `PageFallbackHandler`
- boundary foundation test

## Why this is safe

The current runtime request is object-based, and this matches the existing adapter/tests. The phase still does not cut over `ApplicationFactory`; it only fixes the foundation contract shape.
