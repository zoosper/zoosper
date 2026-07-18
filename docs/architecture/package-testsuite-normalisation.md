# Package testsuite normalisation

After `zoosper-media` moved from `app/` to `packages/`, root test discovery was extended to include package tests. The first broad entry was:

```xml
<directory>packages/*/tests</directory>
```

That kept package tests covered, but Pest/PHPUnit warned that package Unit tests were being attempted in multiple suites. Phase 1.37h.4 narrows the package entry to:

```xml
<directory>packages/*/tests/Unit</directory>
```

This keeps extracted module Unit tests covered while avoiding duplicate-suite warnings.
