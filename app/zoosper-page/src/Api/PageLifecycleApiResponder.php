<?php
declare(strict_types=1);
namespace Zoosper\Page\Api;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Lifecycle\PageLifecycleResult;
use Zoosper\Page\Model\Page;
/** Maps Page lifecycle domain outcomes to stable, secret-free API responses. */
final readonly class PageLifecycleApiResponder
{
    public function __construct(private JsonResponder $json) {}
    public function respond(PageLifecycleResult $result, ?Page $page): Response
    {
        if (!$result->successful) {
            $status = $result->blockers !== [] ? 409 : 422;
            return $this->json->error('page_lifecycle_conflict', $result->message ?? 'Page lifecycle operation was rejected.', $status, ['operation' => $result->operation, 'blockers' => $result->blockers]);
        }
        return $this->json->success(['operation' => $result->operation, 'page_id' => $result->pageId, 'previous_status' => $result->previousStatus, 'current_status' => $result->currentStatus, 'deleted' => $result->operation === 'delete', 'page' => $page === null ? null : ['id' => $page->id, 'site_id' => $page->siteId, 'status' => $page->status]]);
    }
}
