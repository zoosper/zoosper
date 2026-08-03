# Shared command-panel alignment

Filters and Columns are positioned by a shared `zoosper-admin-grid` asset. Each open panel is centred on its own toolbar trigger and clamped to the nearest Grid workspace boundary. Filters use a wider preferred desktop width; Columns use a narrower preferred width. Mobile behaviour remains owned by the existing bottom-drawer rules.

This is presentation infrastructure only. Feature modules do not own panel-position JavaScript or CSS.
