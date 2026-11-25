<?php

declare(strict_types=1);

use Zoosper\Core\Entity\Save\EntityDataObject;

it('adds only nonempty string keys while preserving values and overwriting existing data', function (): void {
    $data = new EntityDataObject();
    $data->setData('existing', 'old');

    $result = $data->addData([
        4 => 'integer',
        '0' => 'numeric string',
        '' => 'empty',
        'existing' => 'new',
        'nullable' => null,
        'nested' => ['value' => 1],
        '01' => 'leading zero',
    ]);

    expect($result)->toBe($data)
        ->and($data->getData())->toBe([
            'existing' => 'new',
            'nullable' => null,
            'nested' => ['value' => 1],
            '01' => 'leading zero',
        ])
        ->and($data->hasData('nullable'))->toBeTrue();
});