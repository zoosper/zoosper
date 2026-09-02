<?php
/**
 * @var string|null $gridHtml
 * @var string $createUrl
 */
$escape = static fn (string $value): string => htmlspecialchars(
    $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
);
$pagesUrl = preg_replace('~/create(?:\?.*)?$~', '', $createUrl) ?: '/admin/pages';
$exportUrl = rtrim($pagesUrl, '/') . '/export';
?>
<section class="page-grid-index" aria-labelledby="page-grid-index-title">
    <header class="page-grid-index__header">
        <p class="page-grid-index__breadcrumb">Content / Pages</p>
        <div class="page-grid-index__heading-row">
            <div class="page-grid-index__heading">
                <h1 id="page-grid-index-title" class="page-grid-index__title">Pages</h1>
                <p class="page-grid-index__description">Manage and publish pages across all your sites.</p>
            </div>
            <div class="page-grid-index__actions" aria-label="Page actions">
                <a class="button button--secondary" href="<?= $escape($exportUrl) ?>">Export</a>
                <a class="button" href="<?= $escape($createUrl) ?>">Create page</a>
            </div>
        </div>
    </header>
    <?php if (is_string($gridHtml ?? null) && $gridHtml !== ''): ?>
        <?= $gridHtml ?>
    <?php else: ?>
        <p>No pages found.</p>
    <?php endif; ?>
</section>
