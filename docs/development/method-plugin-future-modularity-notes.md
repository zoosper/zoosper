# Method Plugin Future Modularity Notes

## Reviewer-confirmed direction

The review confirms that the method plugin/interceptor system is a strong step towards true modularity. It should be developed carefully, with report-only safety and extensive diagnostics before live interception.

## Future extension areas

Potential future use cases include:

- Theme/block rendering extension.
- Page renderer extension.
- Caching hooks.
- Admin form/grid customisation.
- API response decoration.
- Entity save enrichment.
- Media derivative processing extension.

## Required maturity before live interception

Before live interception is enabled for any production path, the system should have:

1. Config-layered runtime settings.
2. Disabled defaults.
3. Explicit report-only allow-listing.
4. Fixture-based proof for the exact method.
5. Rollback checklist.
6. Bootstrap/config drift guard.
7. Full test coverage.
8. Documentation for third-party module authors.

## Key caution

The plugin system adds power and complexity. The architecture should continue to prefer explicit contracts, descriptive diagnostics, and audit tools over hidden magic.
