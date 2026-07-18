# Composer-installed module discovery testing

Run:

```bash
php8.5 tools/verify-composer-module-discovery.php
vendor/bin/pest app/zoosper-core/tests/Unit/Module/ModuleRegistryComposerDiscoveryTest.php
PHP=php8.5 bin/verify
```

The verifier lists discovered module names, source type and path.

Expected after the media path repository pilot:

```text
Zoosper_Media is discovered
full verification remains green
```

The current phase does not remove `app/zoosper-media` yet. That is a later cleanup after Composer-installed discovery has verified in the real repository.
