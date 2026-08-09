# Deployment

Deploy a clean tracked checkout, install locked Composer dependencies without development changes, provide production environment configuration, ensure runtime directories are writable, and run `php bin/zoosper deploy`. Follow with `php bin/zoosper release:check`. The deploy command regenerates autoloading, applies migrations, compiles the module manifest and verifies freshness. It does not replace the test suite.
