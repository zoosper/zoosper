# Writing Entity Save Listeners

Hook into entity saves (pages, admin users, more) **without editing core
controllers**. This guide is copy-paste oriented.

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

## Step 2 - Register it (module config/services.php)

```php
<?php

use Acme\Blog\Save\PageNormaliserListener;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcherInterface;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;

return [
    EntitySaveEventDispatcherInterface::class => static function (): EntitySaveEventDispatcherInterface {
        $dispatcher = new EntitySaveEventDispatcher();
        $dispatcher->listen(EntitySaveLifecycle::DATA_COLLECT_AFTER, new PageNormaliserListener());

        return $dispatcher;
    },
];
```

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

Declare a field as an extension field and let the persister store it:

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

This is the exact, minimal way to wire an admin controller to the runner. It is
**backward compatible**: the runner is injected as an optional last parameter, and
if it is absent the controller falls back to the current direct save.

### (a) Imports

```php
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
```

### (b) Optional constructor parameter (appended LAST)

```php
public function __construct(
    // ... all existing parameters unchanged ...
    private ?EntitySaveLifecycleRunner $saveLifecycle = null,
) {
}
```

Appending last keeps every existing call site valid - no DI breakage.

### (c) A shared helper

```php
/**
 * Run a persistence closure through the entity save lifecycle when available,
 * falling back to a direct save when the runner is not wired.
 *
 * @param array<string, mixed>            $form
 * @param callable(EntitySaveContext): void $save
 */
private function runEntitySave(string $entityType, array $form, int|string|null $entityId, callable $save): EntitySaveContext
{
    $data = (new EntityDataObject())->addData($form);
    $context = new EntitySaveContext($entityType, $data, new FieldDefinitionRegistry(), $entityId);

    if ($this->saveLifecycle !== null) {
        return $this->saveLifecycle->run($context, $save);
    }

    // Legacy fallback: preserve existing behaviour when the runner is absent.
    $save($context);

    return $context;
}
```

### (d) PageAdminController::create() - wrap the existing save

```php
$createdId = null;
$context = $this->runEntitySave('page', $form, null, function (EntitySaveContext $c) use ($form, $user, &$createdId): void {
    $createdId = $this->pages->create(
        siteId: (int) ($form['site_id'] ?? 0),
        title: trim((string) ($form['title'] ?? '')),
        slug: $this->normaliseSlug((string) ($form['slug'] ?? '')),
        content: $this->sanitiseContent((string) ($form['content'] ?? '')),
        status: isset($form['publish']) ? 'published' : 'draft',
        userId: $user->id,
        contentFormat: 'html',
        contentJson: $this->normaliseContentJson($form['content_json'] ?? null),
        metaTitle: $this->normaliseOptionalString($form['meta_title'] ?? null),
        metaDescription: $this->normaliseOptionalString($form['meta_description'] ?? null),
        metaKeywords: $this->normaliseOptionalString($form['meta_keywords'] ?? null),
        canonicalUrl: $this->normaliseOptionalString($form['canonical_url'] ?? null),
    );
});

if ($context->hasErrors()) {
    $firstError = implode(' ', array_merge(...array_values($context->errors())));
    $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), 'page.create_failed');

    return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $firstError, submitted: $form), 422);
}

$this->flashMessages?->success($this->t('Page created successfully.'), 'page.created');

return Response::redirect($this->adminUrl('/pages/edit?id=' . $createdId));
```

All existing behaviour is preserved (CSRF -> 419, processor errors -> 422, SEO
metadata fields, HTML sanitisation). The lifecycle is purely an **added**
extension point.

### (e) UserAdminController::update() - same shape

Wrap `$this->users->updateUser(...)` (and the optional password update) inside
`runEntitySave('admin_user', $form, $user->id, function (EntitySaveContext $c) { ... })`,
then branch on `$context->hasErrors()` for the 422 path. Locale handling via
`adminUserLocaleFromForm()` stays exactly as-is.
