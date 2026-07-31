<?php
/**
 * @var string|null $gridHtml
 */
?>
<div class="toolbar"><a class="button" href="/admin/pages/create">Create page</a></div>

<?php if (is_string($gridHtml ?? null) && $gridHtml !== ''): ?>
    <?= $gridHtml ?>
<?php else: ?>
    <p>No pages found.</p>
<?php endif; ?>
