# Compact column drag runtime v2

The first compact handler depended on server markup already containing
`draggable="true"` and assumed each order input lived inside its column item. The live
compact renderer does not guarantee either assumption. Runtime v2 derives movable
items from their declared column keys, locks `id` and `actions`, sets the DOM draggable
property itself, and synchronises or creates canonical `column_order[]` inputs in the
owning form. An additive stylesheet exposes grab/grabbing and active drag states.
