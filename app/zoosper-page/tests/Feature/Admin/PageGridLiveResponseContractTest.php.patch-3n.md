# Phase 3N live response feature test

Add a feature test using the project's existing authenticated admin request helper:

```php
$response = $this->actingAsAdminWithPermission('page.manage')
    ->get('/admin/pages');

$response->assertOk();
PageGridLiveMarkupContract::assertComplete($response->body());
```

Use the current helper and permission token from the repository. Do not introduce
a second auth fixture. This test must execute the actual route after service and
route compilation, so a rollback to the legacy controller fails CI immediately.
