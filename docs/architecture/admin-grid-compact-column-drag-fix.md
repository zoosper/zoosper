# Compact Grid column drag runtime fix

The original progressive-enhancement script targets the full workspace markup using
`[data-grid-column-list]` and `[data-column-key]`. Live Pages, Admin Users and Roles
use the compact renderer, whose items are `.grid-compact-column[data-column-key]`.
The markup mismatch meant the documented ordering capability existed in contracts but
no drag listeners attached to the live compact controls.

The additive compact ordering asset binds only items explicitly marked
`draggable="true"`. Mandatory ID and Actions items remain immovable. Dropping before
or after another configurable item reorders the DOM and synchronises the existing
`column_order[]` inputs. Move up/down controls use the same ordering path. The user
then submits Apply columns or saves the resulting view through the existing server-
authoritative workflow.
