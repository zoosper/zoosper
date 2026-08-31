<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Admin;

use InvalidArgumentException;
use Throwable;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;

/** Server-side export of the current resolved Store Orders page. */
final readonly class StoreOrderCsvExportController
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private SessionGuard $guard,
        private StoreOrderGridWorkspace $workspace,
        private StoreOrderDataSourceFactory $dataSources,
        private GridWorkspaceCsvExportService $exports,
        private array $config,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function export(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect($this->adminUrls?->url('login') ?? '/admin/login');
        }

        $values = $_GET;
        $values['store_code'] ??= 3;
        $values['kiosk_website_id'] ??= 55;
        if (isset($values['page_size']) && !in_array((int) $values['page_size'], [5, 10, 20, 50, 100], true)) {
            $values['page_size'] = 20;
        }

        try {
            $resolved = $this->workspace->resolve(
                $user->id,
                StoreOrderGridQueryState::fromQuery($values),
                StoreOrderGridQueryState::bookmarkId($values),
            );
            $state = $resolved['state'];
            $result = $this->dataSources->create($this->config, $user->id, [
                'store_code' => $state->criteria->filters['store_code'] ?? 3,
                'kiosk_website_id' => $state->criteria->filters['kiosk_website_id'] ?? 55,
            ])->fetch(new GridQuery(
                page: $state->criteria->pager->page,
                pageSize: $state->criteria->pager->pageSize,
                sort: $state->criteria->sortBy,
                direction: $state->criteria->sortDir,
                filters: $state->criteria->filters,
            ));
            $export = $this->exports->export($state, $result->items, 'store-orders-current-page.csv');

            return Response::raw("\xEF\xBB\xBF" . $export->csv, 200, $export->headers());
        } catch (InvalidArgumentException $exception) {
            return Response::html(htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 422);
        } catch (Throwable) {
            return Response::html('The Store Orders export is currently unavailable.', 503);
        }
    }
}











