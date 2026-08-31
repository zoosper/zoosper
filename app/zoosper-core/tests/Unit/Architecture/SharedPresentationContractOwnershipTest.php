<?php

declare(strict_types=1);

use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\AdminForm\AdminFormConfigAggregator;
use Zoosper\AdminForm\AdminConfigLayeredFileLoader;
use Zoosper\Core\Message\FlashMessage;
use Zoosper\Core\Message\FlashMessageStoreInterface;

it('owns shared presentation contracts in Core', function (): void {
    expect(interface_exists(ContentEditorInterface::class))->toBeTrue()
        ->and(interface_exists(FlashMessageStoreInterface::class))->toBeTrue()
        ->and(class_exists(FlashMessage::class))->toBeTrue()
        ->and(class_exists(AdminFormConfigAggregator::class))->toBeTrue()
        ->and(class_exists(AdminConfigLayeredFileLoader::class))->toBeTrue();
});

it('retires the obsolete Admin-owned contract files', function (): void {
    $root = dirname(__DIR__, 5);
    foreach ([
        'app/zoosper-admin/src/Editor/ContentEditorInterface.php',
        'app/zoosper-admin/src/Message/FlashMessageStoreInterface.php',
        'app/zoosper-admin/src/Message/FlashMessage.php',
        'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
        'app/zoosper-admin/src/Form/AdminConfigLayeredFileLoader.php',
    ] as $relative) {
        expect(file_exists($root . '/' . $relative), $relative)->toBeFalse();
    }
});











