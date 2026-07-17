# Zoosper Media namespace autoload hotfix

## Symptom

Pest reports errors such as:

```text
Class "Zoosper\Media\Repository\MediaAssetRepository" not found
Class "Zoosper\Media\Service\MediaStorage" not found
Class "Zoosper\Media\Service\MediaUploadValidator" not found
```

## Cause

The new `app/zoosper-media/src` files exist, but Composer is not yet configured to autoload the `Zoosper\Media\` namespace.

This is the same class of issue previously documented for `Zoosper\Mail\`.

## Fix

Add these PSR-4 mappings to `composer.json`:

```json
"Zoosper\\Media\\": "app/zoosper-media/src/"
```

and for tests:

```json
"Zoosper\\Media\\Tests\\": "app/zoosper-media/tests/"
```

Then regenerate autoload files with PHP 8.5:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

## Helper script

Phase 1.37a includes:

```text
tools/fix-media-namespace-autoload.php
```

Run:

```bash
php8.5 tools/fix-media-namespace-autoload.php
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
