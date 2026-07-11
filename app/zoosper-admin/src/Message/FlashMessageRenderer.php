<?php

declare(strict_types=1);

namespace Zoosper\Admin\Message;

/**
 * Renders admin flash messages into a small accessible message region.
 */
final readonly class FlashMessageRenderer
{
    /**
     * @param list<FlashMessage> $messages
     */
    public function render(array $messages): string
    {
        if ($messages === []) {
            return '';
        }

        $html = '<section class="admin-flash-messages" aria-live="polite" aria-atomic="true">';
        foreach ($messages as $message) {
            $type = htmlspecialchars(FlashMessage::normaliseType($message->type), ENT_QUOTES, 'UTF-8');
            $key = htmlspecialchars($message->key, ENT_QUOTES, 'UTF-8');
            $text = htmlspecialchars($message->text, ENT_QUOTES, 'UTF-8');
            $dismiss = $message->dismissible
                ? '<button type="button" class="admin-flash-message__dismiss" aria-label="Dismiss message">×</button>'
                : '';

            $html .= '<div class="admin-flash-message admin-flash-message--' . $type . '" data-message-key="' . $key . '">'
                . '<div class="admin-flash-message__text">' . $text . '</div>'
                . $dismiss
                . '</div>';
        }

        return $html . '</section>';
    }
}
