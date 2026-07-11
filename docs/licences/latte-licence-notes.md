# Latte licence notes

Latte is integrated as a Composer dependency, not copied into Zoosper source.

The installed Composer metadata should be used as the source of truth for the exact package version and licences. Preserve upstream licence metadata and review compatibility with Zoosper's selected project licence before a public stable release.

Current engineering packaging approach:

```text
composer require latte/latte:^3.1
```

Do not manually vendor Latte source into Zoosper core.
