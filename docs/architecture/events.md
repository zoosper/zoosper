# Events and Observers

Zoosper has two deliberately separate extension mechanisms:

| Mechanism | Can abort? | Use it for |
|---|---:|---|
| Entity-save lifecycle | yes | validating or mutating a save in progress |
| General events | no | side effects after something happened |

General events are fire-and-forget notifications. A listener can react, but it cannot stop the action that emitted the event. If a listener throws, the dispatcher logs the exception when an `ErrorHandler` is available and continues to the next listener.

Modules subscribe through `config/events.php`:

```php
<?php

declare(strict_types=1);

use Acme\Blog\Listener\WarmPageCache;
use Zoosper\Page\Event\PageEvents;

return [
    PageEvents::PUBLISHED => [WarmPageCache::class],
];
```
