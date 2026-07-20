# Package-owned documentation policy

Zoosper's root `docs/` folder has grown large enough that AI tools on free or small-context plans may waste tokens loading unrelated material.

## Policy

Package-specific implementation documentation should move closer to package code:

```text
packages/<vendor-module>/docs/architecture/
packages/<vendor-module>/docs/operations/
packages/<vendor-module>/docs/README.md
```

The root `docs/` folder should become:

```text
- overview and website navigation
- project-wide roadmaps
- cross-package architecture decisions
- links to package-owned docs
- contributor and project governance material
```

## Why

```text
- Smaller AI context for package reviews
- Standalone package repositories take their docs with them
- Root docs become easier to browse
- Future documentation website can aggregate links from package docs
```

## First package

The first package prepared for this policy is:

```text
packages/zoosper-media/docs/
```
