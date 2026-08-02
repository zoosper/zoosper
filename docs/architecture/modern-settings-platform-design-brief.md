# Modern Settings platform design brief

## Product goal

After API Grid closure, build Settings as shared configuration infrastructure before unrelated features add more hard-coded behaviour. The admin page is one client of the platform, not the platform itself.

## Adopted design principles

- Module-owned registration of pages, sections and fields, with consistent rendering and central validation.
- Active runtime configuration separated from portable, reviewable configuration.
- Environment-specific and secret values remain outside editable portable settings.
- Central categories for application, integrations, sites, security, email, media, content and developer diagnostics.
- Explicit scope, inheritance and provenance for every resolved value.
- Safe import/export with preview, validation, conflict reporting and rollback.
- Strong capability checks, CSRF protection, sanitisation, audit history and redaction.

## Pain points Zoosper should avoid

- one untyped key-value table with unclear ownership;
- settings scattered across unrelated screens;
- plugins writing arbitrary values without schemas;
- plaintext secrets displayed back to administrators;
- hidden precedence between files, environment and database;
- production-only changes that cannot be reviewed or promoted;
- deployable configuration mixed with local state or personal preferences;
- hard-coded values that bypass the resolver;
- silent acceptance of unknown or retired keys;
- settings pages that require bespoke controllers and persistence logic per module.

## Initial information architecture

1. General: site identity, locale, time zone and defaults.
2. Sites and channels: scoped site/store values and inheritance.
3. Content: publishing, revisions, editor and URL behaviour.
4. Media: uploads, limits, derivatives and storage references.
5. Email and notifications: sender identity and transport references.
6. Integrations: API endpoints, authentication references, reliability and diagnostics.
7. Security: session and policy controls, with secrets excluded.
8. Performance: caches, limits and safe operational toggles.
9. Developer: environment, resolved-value provenance and configuration diagnostics.

The final taxonomy should be validated against Zoosper's real inventory during Phase S0 rather than frozen from this brief.
