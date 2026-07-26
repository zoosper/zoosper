# Entity save lifecycle

Admin entities (pages, admin users, and future types) save through a **lifecycle pipeline** instead of controllers writing POST data directly to SQL. Modules hook stages to validate, normalise, or **block** saves without editing core controllers.

## Why it exists

- **Clean controllers** orchestrate HTTP; they do not own every field rule.
- **Safe writes** — only declared fields reach core SQL or extension storage.
- **Extension** — third-party modules register listeners via config.

## Components

| Component | Role |
|-----------|------|
| `EntitySaveLifecycleRunner` | Runs stages; calls repository save callback when allowed |
| `EntitySaveEventDispatcher` | Dispatches stage events to listeners |
| `EntitySaveContext` | Entity type/id, data bag, field registry, errors |
| `EntityDataObject` | Mutable submitted values |
| `FieldDefinitionRegistry` | Maps fields to storage types |
| `EntityExtensionDataPersister` | Writes `ExtensionTable` fields to generic extension storage |

## Seven stages (in order)

| Stage | Typical use |
|-------|-------------|
| `DATA_COLLECT_BEFORE` | Seed defaults |
| `DATA_COLLECT_AFTER` | Normalise slug, trim strings |
| `VALIDATE_BEFORE` / `VALIDATE_AFTER` | Validation; add errors |
| `SAVE_BEFORE` | Last guard before SQL |
| `SAVE_AFTER` | Work that needs a persisted row ID |
| `COMMIT_AFTER` | Cache, notifications, audit |

If `EntitySaveContext::hasErrors()` is true after validation (and again after `SAVE_BEFORE`), **nothing is persisted**.

## Field storage types

| Type | Behaviour |
|------|-----------|
| `CoreColumn` | Written to the entity’s main table via a safe column map |
| `ExtensionTable` | Stored in extension-value storage keyed by module + field |
| `Virtual` | Never persisted (CSRF tokens, UI-only fields) |

Undeclared POST keys are ignored.

## Register listeners (module config)

Create `config/entity_save_listeners.php`:

```php
<?php

declare(strict_types=1);

use Acme\Blog\Save\TitleLengthListener;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;

return [
    EntitySaveLifecycle::VALIDATE_AFTER => [
        TitleLengthListener::class,
    ],
];
```

Class names resolve from the service container when registered in `services.php`; otherwise `new` is used.

## Example listener

```php
final class TitleLengthListener implements EntitySaveEventListenerInterface
{
    public function handle(EntitySaveContext $context): void
    {
        $title = trim((string) $context->data()->getData('title', ''));
        if ($title !== '' && mb_strlen($title) < 3) {
            $context->addError('title', 'Title must be at least 3 characters.');
        }
    }
}
```

## Extension field declaration

Register extension fields in your module’s field definition provider, then persist via `ExtensionTable` and `EntityExtensionDataPersister` after the core row exists.

Core columns stay in core tables; module fields use extension storage unless explicitly mapped as core columns.

## Controller integration

Controllers delegate to `EntitySaveLifecycleRunner` with a small save callback (repository create/update). When listeners add errors, the controller returns the existing validation response (typically HTTP 422) without persisting.

Adding a listener requires **no controller change** — only module config.

## PCI reminder

Never place OTPs, TOTP secrets, recovery codes, reset tokens, session/CSRF tokens, SMTP passwords, or payment data in the data bag or listener logs.

## Deep dive

Step-by-step recipes and Pest examples: [Writing save listeners](../contributor/writing-save-listeners.md).

## Related guides

- [Events & observers](events-and-observers.md) — side effects **after** a save completes (cannot abort)
- [Admin interface](admin-interface.md) — form processors and sections
