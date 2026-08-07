<?php
/**
 * @var string|null $gridHtml
 * @var string $createUrl
 */
?>
<div class="toolbar"><a class="button" href="<?= htmlspecialchars($createUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Create page</a></div>

<?php if (is_string($gridHtml ?? null) && $gridHtml !== ''): ?>
    <?= $gridHtml ?>
<?php else: ?>
    <p>No pages found.</p>
<?php endif; ?>
