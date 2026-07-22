# Zoosper CMS Launch Readiness Arc

## Goal

Move Zoosper from strong architecture foundations toward a CMS that can be used from the admin UI as soon as possible.

## Immediate readiness checklist

Zoosper is ready for first internal CMS use when:

```text
- /admin has no dead links.
- At least one site exists.
- At least one domain maps to that site.
- A theme is assigned to that site.
- A homepage exists and renders.
- Pages can be edited with Editor.js.
- Images upload and render.
- Settings can be edited from admin.
- Admin users and roles work.
- Audit/login history work.
- Full bin/verify is green.
```

## Phase sequence

### 1.37u — Admin sidebar route integrity and launch readiness stubs

Remove dead links from admin navigation and add real protected routes for Sites, Site Domains and Settings.

### 1.37v — Sites and site domains admin CRUD

Allow admins to create, edit and list sites and domain mappings.

### 1.37w — Core settings storage and admin settings UI

Introduce a small settings store and admin settings screen.

### 1.37x — Site theme assignment and frontend theme validation

Make active theme assignment visible and editable from admin, then verify frontend resolution.

### 1.37y — Dashboard launch readiness checklist

Turn the dashboard into a practical readiness panel showing missing site/domain/theme/homepage/settings tasks.

## Deferred until after launch readiness

```text
- Optional GD processor package.
- Optional Imagick processor package.
- AI/RAG features.
- E-commerce modules.
- Bulk documentation migration.
- Advanced revisions/workflow/campaigns.
```

These are valuable but not needed before the first admin-usable CMS milestone.
