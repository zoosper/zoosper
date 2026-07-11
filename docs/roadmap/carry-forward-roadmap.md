# Carry-forward roadmap

## Completed in this phase

- CDN Integration foundation: separate base URLs for dynamic links, media assets and static assets.

## Remaining roadmap items

1. WYSIWYG Editor Integration for CMS pages.
2. Admin Role Page Refactor.
   - Improve organisation of large sections.
   - Add collapsible/toggle UI sections.
3. Cache Manager Design.
4. Index Manager.

## CDN follow-up work

- Wire `CdnUrlResolver` into renderers and asset path services.
- Add admin UI for CDN settings if configuration should move beyond `.env`.
- Add media-library integration once media module exists.
