# Phase 0.29 - Wire Page Store-view Tags

Recommended next phase after fetching latest dev files:

- load `PageSiteAssignmentRepository` in the page controller provider
- pass assigned site IDs to page create/edit view data
- replace the old single-site field with the tag selector component
- load tag selector CSS/JS through the admin layout asset system
- save `site_ids[]` on create/update
- keep backward compatibility by assigning the current single site ID when no tag data is submitted
- avoid storing payment or authentication secrets in page assignment fields or logs
