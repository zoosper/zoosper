# Phase 0.23 - Admin Grid Pagination, Search and Filters

Future goal: add scalable admin grids for large datasets.

Needed pieces:

- `Pager`
- `PaginationResult`
- repository `paginate()` methods
- page size config
- search/filter query handling
- reusable admin pagination component

Example route shape:

```text
/admin/pages?page=2&page_size=20&q=home
```
