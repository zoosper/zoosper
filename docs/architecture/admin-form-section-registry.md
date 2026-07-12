# Phase 0.84 - Admin Form Section Registry / Page Form Extensibility Foundation

Zoosper admin forms now have a section registry foundation. The aim is to stop controllers from owning every field and allow modules to contribute sections without touching core code.

## Core concepts

```text
AdminFormSection
AdminFormSectionProviderInterface
AdminFormProviderRegistry
AdminFormRenderer
```

## Page form sections

```text
page.details       sort 100
page.content       sort 200
page.seo           sort 300
page.publishing    sort 900
```

Future modules can add their own providers for the same form handle, for example:

```text
page.open_graph    sort 320
page.analytics     sort 350
page.scheduling    sort 800
```
