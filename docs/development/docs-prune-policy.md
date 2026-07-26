# Docs Prune Policy (link-aware)

Documentation cleanup is decided by REACHABILITY, not by directory name.

Keep a doc when it is reachable by Markdown links from the canonical roots
(README, index, guide/, configuration/, architecture/, operations/,
troubleshooting/, contributor/, licences/, reference/), or referenced by code, or
under reports/. Delete only unreachable orphans.

Rationale: deleting a doc that a kept guide links to would introduce a broken
link - worse than keeping a stray file. Reachability analysis prevents that.

Tool: tools/prune-docs-safely.php (dry-run first; --apply to delete). It never
touches tools/.
