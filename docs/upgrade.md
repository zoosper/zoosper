# Upgrade

Back up the database and uploaded Media before changing code. Review `CHANGELOG.md`, install locked dependencies, run migrations, compile the module manifest, run release checks and execute the full test suite.

Alpha contracts may change between releases. Keep the previous code, database and Media state available as a rollback point. Do not assume every migration is automatically reversible.
