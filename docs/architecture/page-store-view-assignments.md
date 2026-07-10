# Page Store-view Assignments

Phase 0.27 adds the schema and repository foundation for assigning one CMS page to multiple configured sites/store views.

## Table

```text
page_site_assignments
```

## Purpose

The current edit page can evolve from a single website selector to a multi-select field. This avoids duplicating pages when the same content should appear across multiple sites or store views.

## Important implementation rule

The repository is additive foundation only. Existing page controllers/forms should be updated in a later phase using the latest `dev` code so full replacement files can be generated safely.
