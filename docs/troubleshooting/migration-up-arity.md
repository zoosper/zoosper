# Migration up() Argument Count Hotfix

## Symptom

An older migration object fails with:

```text
Too few arguments to function ...::up(), 1 passed and exactly 2 expected
```

## Cause

Existing Zoosper migration objects can define:

```php
up(PDO $pdo, string $driver): void
```

The clean modular migrator initially called object migrations with only `PDO`.

## Fix

The replacement migrator now uses reflection and calls migration objects/functions with either:

```php
$pdo
```

or:

```php
$pdo, $driver
```

based on the required parameter count.
