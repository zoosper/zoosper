# Page revisions

Page revisions are immutable, Page-scoped snapshots. A snapshot carries identity, HTML, Editor.js JSON, SEO metadata, publication state, actor and timestamp. `PageRevisionService` validates capture, enforces bounded retention and prevents cross-Page lookup. Restoration UI and audit integration are deliberately the next adoption slice; the domain does not destroy existing history when restoring.
