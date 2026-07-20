# Media package documentation policy

`zoosper/media` owns media-specific documentation.

## Move here over time

```text
- media upload validation and storage policy
- Editor.js media upload contract
- media schema notes
- media processing policy
- media package standalone readiness
- media upload cleanup service details
- future media-gd and media-imagick driver contracts
```

## Keep in root docs

```text
- project-wide roadmap summaries
- cross-package architecture overview
- website navigation indexes
- links to package-owned docs
```

## Token policy

Package-specific docs should live beside package code. This allows AI tools to analyse `packages/zoosper-media` without loading unrelated root documentation for auth, page, site, mail, theme or installer modules.
