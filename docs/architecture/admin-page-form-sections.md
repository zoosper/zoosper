# Phase 0.83 - Admin Page Form Section Organisation Foundation

The page edit form is now grouped into semantic sections/cards:

```text
Page details
Content
Search engine optimisation
Publishing
```

This keeps the page editor readable as new capabilities are added, including structured content, SEO features, publishing controls, templates, revisions and future advanced settings.

## Design decision

Use server-rendered sections first. Avoid tabs for now because tabs add JavaScript complexity and can hide validation errors. Collapsible sections can be progressive enhancement later.
