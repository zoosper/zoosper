<?php

declare(strict_types=1);

namespace Zoosper\Admin\Message;

/**
 * Session-backed admin flash message store.
 *
 * Messages are deduplicated by key so repeated saves do not pile up identical
 * notices. This is intentionally tiny and framework-independent; future AJAX
 * endpoints can return the same message shape as JSON.
 */
final class SessionFlashMessageStore implements FlashMessageStoreInterface
{
    private const SESSION_KEY = '_zoosper_admin_flash_messages';

    public function add(FlashMessage $message): void
    {
        $this->ensureSessionStarted();
        $_SESSION[self::SESSION_KEY] ??= [];
        $_SESSION[self::SESSION_KEY][$message->key] = $message->toArray();
    }

    public function success(string $text, string $key = 'success'): void
    {
        $this->add(new FlashMessage(FlashMessage::SUCCESS, $text, $key));
    }

    public function error(string $text, string $key = 'error'): void
    {
        $this->add(new FlashMessage(FlashMessage::ERROR, $text, $key, dismissible: true));
    }

    public function warning(string $text, string $key = 'warning'): void
    {
        $this->add(new FlashMessage(FlashMessage::WARNING, $text, $key));
    }

    public function info(string $text, string $key = 'info'): void
    {
        $this->add(new FlashMessage(FlashMessage::INFO, $text, $key));
    }

    public function pull(): array
    {
        $messages = $this->peek();
        unset($_SESSION[self::SESSION_KEY]);

        return $messages;
    }

    public function peek(): array
    {
        $this->ensureSessionStarted();
        $stored = $_SESSION[self::SESSION_KEY] ?? [];
        if (!is_array($stored)) {
            return [];
        }

        $messages = [];
        foreach ($stored as $message) {
            if (is_array($message)) {
                $messages[] = FlashMessage::fromArray($message);
            }
        }

        return $messages;
    }

    private function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
