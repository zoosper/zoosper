# Phase 0.38 - CLI environment loader foundation

## Problem

CLI tools were using SQLite even though `.env` contained:

```text
DB_CONNECTION=mysql
```

The exported shell environment showed no `DB_CONNECTION`, which meant config files using `env('DB_CONNECTION', 'sqlite')` fell back to SQLite.

## Solution

Add a small dependency-free environment loader and shared CLI bootstrap:

```text
Zoosper\Core\Env\EnvFileLoader
tools/bootstrap.php
```

CLI tools can now include `tools/bootstrap.php` before reading config so `.env` values are available without manually exporting them.

## Security

The loader never prints or logs environment values. Diagnostics redact DB and SMTP passwords.
