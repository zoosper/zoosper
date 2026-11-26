<?php

declare(strict_types=1);

use Zoosper\Mail\Message\EmailAddress;

it('escapes backslashes in display names without allowing header injection', function (): void {
    $address = new EmailAddress('user@example.test', "Name\\\r\nBcc: other@example.test");

    expect($address->headerValue())->toBe('"Name\\\\Bcc: other@example.test" <user@example.test>')
        ->and((new EmailAddress('user@example.test', 'Name\\'))->headerValue())
        ->toBe('"Name\\\\" <user@example.test>');
});