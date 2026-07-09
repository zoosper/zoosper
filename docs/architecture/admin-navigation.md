# Admin Navigation Architecture

This phase introduces a shared admin layout and menu system.

## Files

```text
app/zoosper-admin/src/Navigation/AdminMenuItem.php
app/zoosper-admin/src/Navigation/AdminMenu.php
app/zoosper-admin/src/Layout/AdminLayout.php
```

## Responsibilities

- `AdminMenuItem` describes one menu entry.
- `AdminMenu` returns menu entries allowed for the current admin user.
- `AdminLayout` renders the shared admin shell, sidebar, top bar and common CSS.

## Current menu entries

- Dashboard: `/admin`
- Pages: `/admin/pages`
- Sites: placeholder for next phase
- Settings: placeholder for later phase

Menu visibility is permission-aware. For example, `Pages` requires `page.manage`.
