<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;
use Zoosper\Grid\BulkAction\GridBulkActionExecutionResult;

/** Converts a framework-neutral execution result into a safe HTTP outcome. */
final readonly class GridBulkExecutionResultAdapter
{
    public function adapt(
        GridBulkActionExecutionResult $result,
        string $successRedirectPath,
    ): GridBulkHttpResult {
        $redirectPath = trim($successRedirectPath);
        if ($redirectPath === '' || !str_starts_with($redirectPath, '/')) {
            throw new InvalidArgumentException('Grid bulk-action redirect path must be application-relative.');
        }
        if (str_starts_with($redirectPath, '//')) {
            throw new InvalidArgumentException('Grid bulk-action redirect path cannot be protocol-relative.');
        }

        return $result->successful
            ? new GridBulkHttpResult(303, $result->message, $redirectPath)
            : new GridBulkHttpResult(422, $result->message);
    }
}
