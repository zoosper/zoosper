# Admin Grid Pagination, Search and Filters

Phase 0.23 introduces reusable admin pagination concepts and starts with `/admin/pages`.

## New classes

```text
Zoosper\Core\Pagination\Pager
Zoosper\Core\Pagination\PaginationResult
Zoosper\Page\Admin\PageGridCriteria
Zoosper\Page\Admin\PageGridRepository
```

## Pages grid filters

Supported query parameters:

```text
q
status
site_id
page
page_size
```

Example:

```text
/admin/pages?page=2&page_size=20&q=home&status=published
```

## PCI note

Admin grid filters should never expose or search sensitive payment data. Zoosper should keep payment-related references out of generic admin grids unless a future PCI-scoped module is explicitly designed for that purpose.
