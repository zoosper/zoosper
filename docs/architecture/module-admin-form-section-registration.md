# Phase 0.85 - Module-contributed Admin Form Section Registration

Admin form sections can now be registered through configuration instead of being hardcoded inside `PageAdminController`.

## Config file

```text
config/admin_forms.php
```

## Current page form handle

```text
page.form
```

## Current providers

```text
PageDetailsSectionProvider
PageContentSectionProvider
PageSeoSectionProvider
PagePublishingSectionProvider
```

## Future third-party model

A module can contribute its own section provider class for `page.form`, such as:

```text
Vendor\Analytics\Admin\Form\PageAnalyticsSectionProvider
Vendor\Seo\Admin\Form\OpenGraphSectionProvider
Vendor\Scheduler\Admin\Form\PublishingScheduleSectionProvider
```

This continues the no-core-hacks direction. Controllers should orchestrate request flow, not own all admin UI markup.
