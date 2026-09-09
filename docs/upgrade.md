# Upgrade

Back up the database and uploaded Media before changing code. Review `CHANGELOG.md`, install locked dependencies, run migrations, compile the module manifest, run release checks and execute the full test suite.

Alpha contracts may change between releases. Keep the previous code, database and Media state available as a rollback point. Do not assume every migration is automatically reversible.

## Composer package compatibility

Zoosper 0.3 first-party packages use one synchronised release train and bounded `^0.3.1@alpha` internal compatibility. Deploy from the root project with the committed `composer.lock`; do not replace first-party constraints with a floating development branch. Run Composer through PHP 8.5, then compile and verify the module manifest after dependency installation.

## Upgrade rehearsal

`migrate` is a write command and deliberately has no dry-run option. Rehearse upgrades with a disposable database and isolated Git worktree before changing a production installation. Run `schema:foreign-keys:status --format=json` separately for read-only foreign-key inspection. Require globally unique migration basenames because the current migration history stores basenames.

The supported beta-readiness proof starts from the latest immutable release `v0.3.1-alpha.1`. Direct upgrade proof from `v0.3.0-alpha.5` is a separate compatibility gate. Preserve representative Admin, Site, Page, Menu, Media, permission, audit, and Grid data across the rehearsal, run migration twice for idempotency, then require zero foreign-key additions, mismatches, and SQLite rebuild requirements.

## Latest-release preservation proof

Run `php8.5 tools/verify-release-upgrade.php v0.3.1-alpha.1` as `vagrant` to rehearse the immutable release against current source. The tool uses detached temporary worktrees, a private SQLite database and Media root, verifies a connected Admin/Site/Page/Menu/Media fixture graph, runs current migration twice, checks foreign-key integrity, and removes all temporary state. Composer installs are isolated to the temporary worktrees.

### Disposable MySQL upgrade database

BR-2D never rehearses against the configured application database. Supply dedicated administrative credentials through `BR2D_MYSQL_HOST`, `BR2D_MYSQL_PORT`, `BR2D_MYSQL_USERNAME`, and `BR2D_MYSQL_PASSWORD`, then run `php8.5 tools/verify-mysql-upgrade-capability.php`. The account must be restricted to creating and dropping the uniquely named rehearsal database. Credential values are never printed.

The MySQL capability result reports `database_created: true` and `database_dropped: true`. Success is emitted only after `INFORMATION_SCHEMA.SCHEMATA` confirms that the generated rehearsal database no longer exists.
