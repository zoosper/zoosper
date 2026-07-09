# Admin Components and Module UI

Phase 0.17 starts moving admin UI rendering towards module-owned views and reusable admin theme components.

## Added concepts

- `AdminViewRenderer`
- `AdminComponentRenderer`
- admin component templates under `themes/admin/default/templates/components`
- module admin views under `app/<module>/resources/views`
- theme overrides under `themes/admin/default/templates/modules/<module>`

## Goal

Controllers should become thinner over time. They should prepare data and delegate rendering to a view/template.
