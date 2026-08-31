<?php

declare(strict_types=1);

namespace Zoosper\Database\Tests\Schema;

use Zoosper\Database\Schema\SchemaRegistry;
use Zoosper\Database\Schema\SchemaTable;
use Zoosper\Database\Schema\SchemaValidator;

test('longtext is accepted by declarative schema validation', function (): void {
    $registry = new SchemaRegistry();
    $registry->addTable(new SchemaTable(
        name: 'message_archive',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'body' => ['type' => 'longtext', 'nullable' => true],
        ],
        indexes: [],
    ));

    $result = (new SchemaValidator())->validate($registry);

    expect($result->isValid())->toBeTrue();
    expect($result->errors)->toBe([]);
});











