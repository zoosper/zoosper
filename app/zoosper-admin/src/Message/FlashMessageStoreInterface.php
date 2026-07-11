<?php

declare(strict_types=1);

namespace Zoosper\Admin\Message;

/**
 * Stores short-lived admin UI messages for redirect and future AJAX flows.
 */
interface FlashMessageStoreInterface
{
    public function add(FlashMessage $message): void;

    public function success(string $text, string $key = 'success'): void;

    public function error(string $text, string $key = 'error'): void;

    public function warning(string $text, string $key = 'warning'): void;

    public function info(string $text, string $key = 'info'): void;

    /**
     * Return pending messages and clear them from storage.
     *
     * @return list<FlashMessage>
     */
    public function pull(): array;

    /**
     * Return pending messages without clearing them.
     *
     * @return list<FlashMessage>
     */
    public function peek(): array;
}
