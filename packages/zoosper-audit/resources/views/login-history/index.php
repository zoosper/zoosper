<?php

/**
 * @var string|null $workspaceHtml
 * @var string|null $gridHtml
 */
?>
<section class="login-history-index" aria-labelledby="login-history-index-title">
    <header class="login-history-index__header">
        <p class="login-history-index__breadcrumb">System / Login History</p>
        <h1 id="login-history-index-title" class="login-history-index__title">Login History</h1>
        <p class="login-history-index__description">
            Review authentication activity across Zoosper.
        </p>
    </header>

    <?= is_string($workspaceHtml ?? null) ? $workspaceHtml : '' ?>
    <?= is_string($gridHtml ?? null) ? $gridHtml : '' ?>
</section>
