# Upgrade

Back up the database and uploaded Media before changing code. Review `CHANGELOG.md`, install locked dependencies, run migrations, compile the module manifest, run release checks and execute the full test suite.

Alpha contracts may change between releases. Keep the previous code, database and Media state available as a rollback point. Do not assume every migration is automatically reversible.

## Composer package compatibility

Zoosper 0.3 first-party packages use one synchronised release train and bounded `^0.3.1@alpha` internal compatibility. Deploy from the root project with the committed `composer.lock`; do not replace first-party constraints with a floating development branch. Run Composer through PHP 8.5, then compile and verify the module manifest after dependency installation.
