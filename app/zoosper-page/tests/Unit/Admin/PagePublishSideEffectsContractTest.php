<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

it('binds bulk publication to the established event and audit contracts', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents(
        $root . '/app/zoosper-page/src/Admin/BulkAction/PagePublishSideEffects.php',
    );

    expect($source)->not->toBeFalse();
    expect($source)->toContain('private EventDispatcherInterface $events');
    expect($source)->toContain('private AuditLoggerInterface $audit');
    expect($source)->toContain('PageEvents::PUBLISHED');
    expect($source)->toContain(<<<'PHP'
new PagePublishedEvent($page->id, $actor->adminUserId)
PHP);
    expect($source)->toContain("action: 'page.bulk_publish'");
    expect($source)->toContain("'bulk_action' => PagePublishSelectedExecutor::ACTION_ID");
    expect($source)->toContain(<<<'PHP'
'previous_status' => $page->status
PHP);
    expect($source)->toContain("'new_status' => 'published'");
});
