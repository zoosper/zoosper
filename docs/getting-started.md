# Getting started

## Requirements

Zoosper requires PHP 8.5 or newer, Composer 2, PDO and a supported database driver. The web server document root must be `public/`. Runtime processes require write access to `var/cache` and `var/log`.

## Install

1. Copy `.env.example` to `.env`.
2. Configure the application URL, database and environment secrets.
3. Run `composer install`.
4. Run `php bin/zoosper migrate`.
5. Run `php bin/zoosper compile`.
6. Use `php bin/zoosper list` to discover the Admin and Site bootstrap commands.
7. Run `php bin/zoosper release:check`.

For a disposable verification installation, run `composer fresh-install:smoke`. This command uses a temporary SQLite database and does not modify the configured project database.

## Starter content
After migrations and Site setup, run `php bin/zoosper starter:install`. The command creates only a missing Site and missing published Home/About Pages. Existing records are retained, so rerunning it is safe.
