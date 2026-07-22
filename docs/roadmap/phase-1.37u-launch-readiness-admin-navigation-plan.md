# Phase 1.37u — Launch Readiness Admin Navigation Plan

## Purpose

Zoosper CMS has strong architectural foundations, but the next priority is to make the admin usable as a real CMS as soon as possible.

The current admin sidebar still contains dead links for core configuration areas:

```html
<a href="#">Site Domains</a>
<a href="#">Sites</a>
<a href="#">Settings</a>
```

Before continuing deeper media-engine work, Zoosper should enter a short Launch Readiness Arc focused on removing admin dead ends and enabling site configuration from the UI.

## Product direction

The immediate goal is not advanced media engines, AI integration, e-commerce, or deep workflow automation. The immediate goal is:

```text
Can an admin log in, configure a site/domain/theme/settings, create pages, upload images, and publish a usable website without editing code?
```

## Planned Launch Readiness Arc

```text
1.37u — Admin sidebar route integrity and launch readiness stubs
1.37v — Sites and site domains admin CRUD
1.37w — Core settings storage and admin settings UI
1.37x — Site theme assignment and frontend theme validation
1.37y — Dashboard launch readiness checklist
```

## Phase 1.37u scope

Phase 1.37u should remove dead admin sidebar links and replace them with real protected admin routes.

Target links:

```text
/admin/sites
/admin/site-domains
/admin/settings
```

This phase can initially use safe readiness/stub pages rather than full CRUD. The important contract is that the admin navigation no longer points at `#` for core CMS configuration areas.

## Expected deliverables

```text
- Replace Site Domains, Sites and Settings href="#" links.
- Add real admin routes for /admin/sites, /admin/site-domains and /admin/settings.
- Add safe placeholder/readiness controllers or templates.
- Add permission coverage for the new routes.
- Add an admin sidebar route integrity test.
- Add docs describing the Launch Readiness Arc.
```

## Acceptance criteria

```text
- Admin sidebar has no dead links for core CMS areas.
- /admin/sites renders a protected admin page.
- /admin/site-domains renders a protected admin page.
- /admin/settings renders a protected admin page.
- New routes have permission/middleware coverage.
- Full verification remains green.
```

## Non-goals

```text
- Full Sites CRUD.
- Full Site Domains CRUD.
- Full Settings persistence.
- GD/Imagick derivative generation.
- AI/RAG features.
- E-commerce modules.
```

Those are future phases.

## Why this comes before more media work

The local media derivative groundwork is now strong enough for early usage. The highest launch-readiness gap is admin configuration UX, not optional image engines.

A CMS without GD thumbnails can still be evaluated and used. A CMS with dead admin links for Sites, Domains and Settings feels unfinished.
