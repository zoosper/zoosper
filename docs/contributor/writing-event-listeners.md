# Writing Event Listeners

Use general events when your module wants to **react** to something that already happened: clear a cache, write an audit log, send a notification, update a search index.

Do **not** use events to validate or block saves. Use the entity-save lifecycle for that.

## Listener class

```php
<?php

declare(strict_types=1);

namespace Acme\Blog\Listener;

use Zoosper\Core\Event\EventListenerInterface;
use Zoosper\Page\Event\PagePublishedEvent;

final readonly class WarmPageCache implements EventListenerInterface
{
    public function handle(object $event): void
    {
        if (!$event instanceof PagePublishedEvent) {
            return;
        }

        // React to $event->pageId.
    }
}
```

## Registration

```php
<?php

declare(strict_types=1);

use Acme\Blog\Listener\WarmPageCache;
use Zoosper\Page\Event\PageEvents;

return [
    PageEvents::PUBLISHED => [
        WarmPageCache::class,
    ],
];
```

If the listener needs dependencies, register it in `config/services.php` and keep referencing it by class-string here. The loader resolves from the container first.
