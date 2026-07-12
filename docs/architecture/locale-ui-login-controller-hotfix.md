# Phase 1.06.1 - Locale UI Login Controller Hotfix

Phase 1.06 patched the wrong target: `LoginController.php`. The inserted raw HTML/PHP block caused a PHP parse error during controller factory loading.

This hotfix removes the misplaced locale UI block from `LoginController.php` and updates verification so the login controller must not contain a raw `name="locale"` field.

The locale preference UI renderer remains valid. A future phase should integrate it into `UserAdminController` or an explicit profile/preferences form using the controller's real rendering pattern.
