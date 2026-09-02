<?php

/**
 * @var string|null $workspaceHtml
 * @var string|null $gridHtml
 */
?>
<section class="audit-log-index" aria-labelledby="audit-log-index-title">
    <header class="audit-log-index__header">
        <p class="audit-log-index__breadcrumb">System / Audit Log</p>
        <h1 id="audit-log-index-title" class="audit-log-index__title">Audit Log</h1>
        <p class="audit-log-index__description">
            Review administrative activity across Zoosper.
        </p>
    </header>

    <?= is_string($workspaceHtml ?? null) ? $workspaceHtml : '' ?>
    <?= is_string($gridHtml ?? null) ? $gridHtml : '' ?>
</section>
