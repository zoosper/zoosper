# Phase 0.23 - Admin Grid Pagination, Search and Filters

Recommended next phase:

- introduce `Pager`
- introduce `PaginationResult`
- add repository `paginate()` methods
- add reusable admin pagination component
- add search and filter query objects
- apply first to `/admin/pages`

Example route shape:

```text
/admin/pages?page=2&page_size=20&q=home
```
