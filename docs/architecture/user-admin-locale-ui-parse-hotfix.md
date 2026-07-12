# Phase 1.07.1 - UserAdminController Locale UI Parse Hotfix

Phase 1.07 inserted raw HTML/PHP into `UserAdminController.php`, which is a PHP controller containing heredoc/string-rendered HTML. The inserted `<?= ... ?>` expression caused a PHP parse error.

This hotfix removes the raw locale UI field from `UserAdminController.php` and updates verification to reject raw `name="locale"` blocks in that controller until a proper controller/template-aware integration is implemented.

The standalone `AdminUserLocalePreferenceFieldRenderer` remains valid and can be integrated in a future phase using the controller's real rendering pattern.
