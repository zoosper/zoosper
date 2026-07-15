# Writing Entity Save Listeners

Hook into entity saves (pages, admin users, more) **without editing core
controllers or core services.php**. This guide is copy-paste oriented.

## Step 1 - Implement the listener

```php
<?php

declare(strict_types=1);

namespace Acme\Blog\Save;

use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveEventListenerInterface;

/**
 * Normalises the slug and rejects empty titles.
 */
final class PageNormaliserListener implements EntitySaveEventListenerInterface
{
    public function handle(EntitySaveContext $context): void
    {
        $data = $context->data();

        // Normalise.
        $slug = strtolower(trim((string) $data->getData('slug', '')));
        $data->setData('slug', $slug);

        // Validate.
        if (trim((string) $data->getData('title', '')) === '') {
            $context->addError('title', 'Title is required.');
        }
    }
}
```

## Step 2 - Register it via module discovery (Phase 1.28)

Create a `config/entity_save_listeners.php` in **your own module**. It is
discovered automatically by `ModuleEntitySaveListenerLoader` - no core file is
touched.

```php
<?php

declare(strict_types=1);

use Acme\Blog\Save\PageNormaliserListener;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;

return [
    EntitySaveLifecycle::DATA_COLLECT_AFTER => [
        PageNormaliserListener::class,
    ],
];
```

The loader resolves each entry as follows:

- an `EntitySaveEventListenerInterface` **instance** -> used as-is;
- a **callable** (e.g. a closure) -> used as-is;
- a **class-string** -> resolved from the service container if registered,
  otherwise constructed with `new`.

**Listeners with dependencies:** register the listener in your module
`config/services.php` and reference it by class-string here - the loader resolves
it from the container first, so its dependencies are injected.

## Step 3 - Choose the right stage

| Stage | Use it for |
|---|---|
| `DATA_COLLECT_BEFORE` | Seed defaults before the data bag is filled |
| `DATA_COLLECT_AFTER`  | Normalise / trim / derive values |
| `VALIDATE_BEFORE`     | Cross-field validation |
| `VALIDATE_AFTER`      | Final validation; add errors |
| `SAVE_BEFORE`         | Last guard right before persistence |
| `SAVE_AFTER`          | Side effects needing the saved row (extension data, search index) |
| `COMMIT_AFTER`        | Cache clear, notifications, audit logging |

## How to block a save

Call `$context->addError($field, $message)` at or before `SAVE_BEFORE`. The runner
returns without persisting if `hasErrors()` is true after the validate stages or
after `SAVE_BEFORE`.

## Storing extension-table data

```php
use Zoosper\Core\Entity\Save\FieldDefinition;

$registry->register(FieldDefinition::extension('acme_blog', 'reading_time', 'Reading time'));
// After persistence, EntityExtensionDataPersister::persist() writes only
// ExtensionTable fields to the generic extension store, keyed by module.
```

## Testing your listener with Pest (co-located)

```php
<?php

declare(strict_types=1);

namespace Acme\Blog\Tests\Unit\Save;

use Acme\Blog\Save\PageNormaliserListener;
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

test('rejects an empty title', function () {
    $context = new EntitySaveContext('page', new EntityDataObject(), new FieldDefinitionRegistry());
    (new PageNormaliserListener())->handle($context);

    expect($context->hasErrors())->toBeTrue();
});
```

## PCI reminder

Never read, store, or log OTPs, TOTP secrets, recovery-code plaintext, reset
tokens, session/CSRF tokens, SMTP passwords, or payment data from a listener.

---

## Controller integration recipe

Controllers delegate persistence to `EntitySaveLifecycleRunner` via a small
`runEntitySave()` helper (see `PageAdminController` / `UserAdminController`). When
a listener adds an error, the runner aborts before persistence and the controller
returns the existing 422 form. No controller change is needed to add a new
listener - just drop a `config/entity_save_listeners.php` into a module.
