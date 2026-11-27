<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Api;

use DateTimeImmutable;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMappingException;
use Zoosper\ApiGrid\Mapping\ApiGridRowMapperInterface;

final class StoreOrderRowMapper implements ApiGridRowMapperInterface
{
    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    #[\Override]
    public function map(array $record): array
    {
        $orderId = trim((string) ($record['order_id'] ?? ''));
        $orderDate = trim((string) ($record['orderDate'] ?? ''));
        if ($orderId === '' || $orderDate === '') {
            throw new ApiGridResponseMappingException('Store Order record requires order_id and orderDate.');
        }

        $placedAt = $this->parseDate($orderDate);

        $total = $record['totalPaid_fx'] ?? 0;
        if ((!is_int($total) && !is_float($total) && !is_string($total))
            || !is_numeric($total) || !is_finite((float) $total)) {
            throw new ApiGridResponseMappingException('Store Order total is invalid.');
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
            'total_paid' => (float) $total,
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

        return $this->parseDate($value)->format(DATE_ATOM);
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        try {
            $date = new DateTimeImmutable($value);
        } catch (\Throwable $exception) {
            throw new ApiGridResponseMappingException('Store Order date is invalid.', previous: $exception);
        }
        $errors = DateTimeImmutable::getLastErrors();
        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw new ApiGridResponseMappingException('Store Order date is invalid.');
        }

        return $date;
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











