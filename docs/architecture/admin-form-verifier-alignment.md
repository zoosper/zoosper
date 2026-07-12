# Admin form verifier alignment

Verifier tools should validate behaviour and rendered output after Phase 0.84.

The page form now comes from:

```text
AdminFormProviderRegistry
AdminFormRenderer
PageDetailsSectionProvider
PageContentSectionProvider
PageSeoSectionProvider
PagePublishingSectionProvider
```

Therefore verifiers should not require page form fields to live directly in `PageAdminController.php`.
