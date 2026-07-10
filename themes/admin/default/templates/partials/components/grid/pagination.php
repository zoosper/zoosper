<?php
/**
 * Reusable admin grid pagination controls.
 *
 * @var callable $e
 * @var \Zoosper\Core\Pagination\PaginationResult<mixed> $pagination
 * @var array<string, string|int> $params
 * @var string $baseUrl
 */
$totalPages = $pagination->totalPages();
?>
<nav class="pagination" aria-label="Pagination">
    <span class="muted">Page <?= (int) $pagination->page ?> of <?= (int) $totalPages ?> · <?= (int) $pagination->total ?> result(s)</span>
    <div class="toolbar">
        <?php if ($pagination->hasPrevious()): ?>
            <a class="button secondary" href="<?= $e($baseUrl . '?' . http_build_query($params + ['page' => $pagination->page - 1])) ?>">Previous</a>
        <?php endif; ?>
        <?php if ($pagination->hasNext()): ?>
            <a class="button secondary" href="<?= $e($baseUrl . '?' . http_build_query($params + ['page' => $pagination->page + 1])) ?>">Next</a>
        <?php endif; ?>
    </div>
</nav>
