# Editor.js media upload permissions

Phase 1.37m.1 adjusts the async Editor.js image upload route permission model.

## Problem

The browser Image Tool can be available inside the page editor for a user who can edit pages but does not have full media-library management access. If the upload route requires only `media.manage`, the admin middleware can reject the upload and the Image Tool receives an HTML admin page instead of the expected JSON response.

## Route permission

The route now allows either permission:

```php
'permission' => ['media.manage', 'page.manage']
```

This uses the existing route-permission OR semantics.

## Security stance

The endpoint remains:

```text
- authenticated
- permission protected
- CSRF protected
- image validated
- stored through the media service
```

This change does not make the endpoint public. It only lets page managers upload images from the page editor without granting them the full media library menu/management permission.
