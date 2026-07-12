# Phase 0.87 - Admin Form Processors Foundation

Zoosper admin forms now have a processor foundation so modules can validate and prepare submitted values for their own fields without editing core controllers.

## New concepts

```text
AdminFormProcessorInterface
AdminFormProcessingResult
AdminFormProcessorRegistry
AdminFormProcessorConfigFactory
```

## Config shape

```php
return [
    'forms' => [
        'page.form' => [/* section providers */],
    ],
    'processors' => [
        'page.form' => [/* processor classes */],
    ],
];
```

## Important

This phase introduces the processor contract and config support. Wiring processors into page create/update persistence should happen in the next implementation phase.
