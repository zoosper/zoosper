# Module Console Commands

Zoosper modules can contribute CLI commands to `bin/zoosper` without editing core files.

## Register a command

Create a module-owned `config/console.php` file:

```php
<?php

declare(strict_types=1);

use Vendor\Blog\Console\ReindexPostsCommand;

return [
    ReindexPostsCommand::class,
];
```

The command class must implement `Zoosper\Core\Console\ConsoleCommandInterface`.

## Commands with dependencies

If a command needs constructor dependencies, register the command in the same module's `config/services.php` and keep `config/console.php` as the command list.

```php
<?php

declare(strict_types=1);

use Vendor\Blog\Console\ReindexPostsCommand;
use Vendor\Blog\Repository\PostRepository;
use Zoosper\Core\Container\ServiceContainer;

return [
    ReindexPostsCommand::class => static fn (ServiceContainer $services): ReindexPostsCommand => new ReindexPostsCommand(
        $services->get(PostRepository::class),
    ),
];
```

## Command names

Use stable, vendor/module-prefixed names such as:

```text
blog:posts:reindex
catalog:feeds:export
```

## Security

CLI commands must never print secrets, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values.
