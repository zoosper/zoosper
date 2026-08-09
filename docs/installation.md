# Installation

## Requirements

PHP 8.5 or newer, Composer 2, PDO and a supported database driver. The web server must serve `public/` and permit writes to `var/cache` and `var/log`.

## Bootstrap

1. Copy `.env.example` to `.env` and configure the database and application URL.
2. Run `composer install`.
3. Run `php bin/zoosper migrate`.
4. Run `php bin/zoosper compile`.
5. Create the initial administrator and site with the available CLI commands shown by `php bin/zoosper list`.
6. Run `php bin/zoosper release:check`.

Do not expose development debug output in production.

## Fresh-install proof

Run `composer fresh-install:smoke` to create a disposable SQLite installation, apply and rerun all migrations, create an initial administrator and site, verify password hashing and core tables, then remove the temporary database. The command does not modify the configured project database.
