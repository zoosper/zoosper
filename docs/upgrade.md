# Alpha upgrades

Back up the database and uploaded files before changing code. Install the locked dependencies, run `php bin/zoosper migrate`, compile the module manifest, run `php bin/zoosper release:check`, then run the full test suite. Alpha releases may change extension contracts; review `CHANGELOG.md` before upgrading. Keep one rollback point containing the previous code, database and uploads.
