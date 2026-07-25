<?php

declare(strict_types=1);

namespace Zoosper\Page\Routing;

use Zoosper\Core\Routing\FallbackHandlerInterface;

/**
 * Page-module implementation of the core-owned fallback handler contract.
 */
final class PageFallbackHandler implements FallbackHandlerInterface
{
    public function __construct(
        private readonly object $pageController,
    ) {
    }

    public function supports(object $request): bool
    {
        if (method_exists($this->pageController, 'supports')) {
            return (bool) $this->pageController->supports($request);
        }

        return method_exists($this->pageController, 'handle')
            || method_exists($this->pageController, 'view')
            || method_exists($this->pageController, '__invoke');
    }

    public function handle(object $request): mixed
    {
        if (!$this->supports($request)) {
            return null;
        }

        if (method_exists($this->pageController, 'handle')) {
            return $this->pageController->handle($request);
        }

        if (method_exists($this->pageController, 'view')) {
            return $this->pageController->view($request);
        }

        if (method_exists($this->pageController, '__invoke')) {
            return ($this->pageController)($request);
        }

        return null;
    }
}
