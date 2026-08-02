<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Api;

use InvalidArgumentException;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridRequestMapperInterface;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\Grid\DataSource\GridQuery;

final class StoreOrderRequestMapper implements ApiGridRequestMapperInterface
{
    public function map(GridQuery $query, ApiGridContext $context): ApiRequest
    {
        $parameters = [
            'page' => $query->page,
            'per_page' => $query->pageSize,
            'store_code' => $context->requireInt('store_code'),
            'kiosk_website_id' => $context->requireInt('kiosk_website_id'),
        ];

        $this->copyText($parameters, $query->filters, 'order_id', 64);
        $this->copyText($parameters, $query->filters, 'customer', 120);
        $this->copyText($parameters, $query->filters, 'status', 64);
        $this->copyDate($parameters, $query->filters, 'placed_from');
        $this->copyDate($parameters, $query->filters, 'placed_to');
        $sortable = [
            'order_id',
            'order_date',
            'customer_name',
            'status',
            'payment_type',
            'total_paid',
            'picked_up_at',
        ];
        if ($query->sort !== null) {
            if (!in_array($query->sort, $sortable, true)) {
                throw new InvalidArgumentException('Unsupported Store Orders sort field.');
            }
            $parameters['sort'] = $query->sort;
            $parameters['dir'] = $query->direction;
        }
        if (isset($parameters['placed_from'], $parameters['placed_to'])
            && $parameters['placed_from'] > $parameters['placed_to']) {
            throw new InvalidArgumentException('Placed From must not be after Placed To.');
        }

        return new ApiRequest(method: 'GET', endpoint: '/v3/orders/store', query: $parameters);
    }

    /** @param array<string, scalar> $target @param array<string, mixed> $filters */
    private function copyText(array &$target, array $filters, string $key, int $maximumLength): void
    {
        $value = $filters[$key] ?? null;
        if ($value === null || $value === '') return;
        if (is_array($value) || !is_scalar($value)) {
            throw new InvalidArgumentException($key . ' filter must be a scalar value.');
        }
        $value = trim((string) $value);
        if ($value === '') return;
        if (mb_strlen($value) > $maximumLength) {
            throw new InvalidArgumentException($key . ' filter exceeds its maximum length.');
        }
        $target[$key] = $value;
    }

    /** @param array<string, scalar> $target @param array<string, mixed> $filters */
    private function copyDate(array &$target, array $filters, string $key): void
    {
        $value = $filters[$key] ?? null;
        if ($value === null || $value === '') return;
        if (is_array($value) || !is_scalar($value)) {
            throw new InvalidArgumentException($key . ' filter must be a date.');
        }
        $value = trim((string) $value);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException($key . ' filter must use YYYY-MM-DD.');
        }
        $target[$key] = $value;
    }
}
