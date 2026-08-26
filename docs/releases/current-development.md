# Current development line

## Version

`0.3.0-alpha.3-dev`

## Current source status

- Preserve the published `v0.3.0-alpha.2` API, security, and package-ownership baseline.
- Media list, detail, derivative, canonical upload, archive, restore, and guarded permanent-delete APIs are complete in the current development line.
- Module-discovery collision handling has been reconciled against current source and remains fail closed.
- The responsive Admin refinement is complete in source at `364414a4878cde36fd89de8583326e4d1ff1f625`: permission-aware Dashboard links, fluid light/dark presentation, package-owned responsive Grid workflows, a sidebar-owned collapse control, module-owned semantic destination identifiers, and text-only non-interactive navigation groups.
- Final accepted verification for that source was `1,550` tests with `11,157` assertions; the standard quality gate passed `3` checks with `0` errors and `0` warnings.
- The Admin refinement was browser-accepted, committed, and pushed. It was not deployed as part of that phase.

## Next engineering phase

Revalidate the historical production-security reviewer findings against current source before changing runtime behaviour. The review must independently verify deployment-environment detection, secure-session and rate-limiting enforcement, and HTTP/console boot parity. These are historical assertions, not confirmed current defects.

After that evidence-led security phase, planned Admin follow-ups include an Admin-owned, module-discovered contract for useful permission-filtered dynamic Dashboard widgets; extensible colour-theme registration; the separate Grid column-preference persistence defect; organised multi-layer navigation discovery; and Auth-owned assigned-user search, Grid, and pagination improvements.

Keep documentation, package READMEs, upgrade notes, and architecture decisions current in every phase.
