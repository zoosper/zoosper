<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Api;

use DateTimeImmutable;
use InvalidArgumentException;
use Zoosper\ApiGrid\Mapping\ApiGridRowMapperInterface;

final class StoreOrderRowMapper implements ApiGridRowMapperInterface
{
    /** @param array<string, mixed> $record */
    public function map(array $record): array
    {
        $orderId = trim((string) ($record['order_id'] ?? ''));
        $orderDate = trim((string) ($record['orderDate'] ?? ''));
        if ($orderId === '' || $orderDate === '') {
            throw new InvalidArgumentException('Store Order record requires order_id and orderDate.');
        }

        try {
            $placedAt = new DateTimeImmutable($orderDate);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException('Store Order orderDate is invalid.', previous: $exception);
        }

        $customer = trim((string) ($record['customer_name'] ?? ''));
        if ($customer === '') {
            $customer = trim(sprintf(
                '%s %s',
                (string) ($record['customer_firstname'] ?? ''),
                (string) ($record['customer_lastname'] ?? ''),
            ));
        }

        return [
            'order_id' => $orderId,
            'order_date' => $placedAt->format(DATE_ATOM),
            'customer_name' => $customer,
            'status' => trim((string) ($record['status'] ?? '')),
            'payment_type' => trim((string) ($record['payment_type'] ?? '')),
            'total_paid' => (float) ($record['totalPaid_fx'] ?? 0),
            'picked_up_at' => $this->optionalDate($record['picked_up_at'] ?? null),
            'packed_at' => $this->optionalDate($record['packed_at'] ?? null),
            'tracking' => $this->optionalString($record['tracking'] ?? null),
        ];
    }

    private function optionalDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException('Store Order optional date is invalid.', previous: $exception);
        }
    }

    private function optionalString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $normalised = trim((string) $value);

        return $normalised !== '' ? $normalised : null;
    }
}











