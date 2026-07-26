# Events & observers

Zoosper separates **save lifecycle** hooks from **general events**:

| Mechanism | Can abort? | Use for |
|-----------|:----------:|---------|
| Entity save lifecycle | Yes | Validate or mutate data during a save |
| General events | No | Cache, audit, notifications after something happened |

General events are fire-and-forget. A listener cannot undo the action that emitted the event. If a listener throws, the dispatcher logs when an error handler is available and continues with remaining listeners.

## Subscribe in a module

`config/events.php`:

```php
<?php

declare(strict_types=1);

use Acme\Blog\Listener\WarmPageCache;
use Zoosper\Page\Event\PageEvents;

return [
    PageEvents::PUBLISHED => [WarmPageCache::class],
    PageEvents::UNPUBLISHED => [WarmPageCache::class],
];
```

## Listener example

```php
final readonly class WarmPageCache implements EventListenerInterface
{
    public function handle(object $event): void
    {
        if (!$event instanceof PagePublishedEvent) {
            return;
        }

        // React using $event->pageId (or equivalent API on the event object).
    }
}
```

Register the class in `services.php` when it needs dependencies; keep the class name in `events.php`.

## Built-in page events

| Event name | Constant | When emitted |
|------------|----------|--------------|
| `page.published` | `PageEvents::PUBLISHED` | Page published |
| `page.unpublished` | `PageEvents::UNPUBLISHED` | Page unpublished |

Emitters live in page admin/services; add new event classes in the owning module and document constants there.

## When **not** to use events

Do not validate or block saves with general events. Use [Entity save lifecycle](entity-save-lifecycle.md) and `$context->addError()`.

## Further reading

[Writing event listeners](../contributor/writing-event-listeners.md).
