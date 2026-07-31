<?php
/**
 * Phase A (Grid Core): this template now simply echoes the pre-rendered grid
 * HTML (filter bar + sortable table + pagination) built by the shared
 * Zoosper\Grid\GridHtmlRenderer. Column layout, filters and sorting are
 * declared once in Zoosper\Admin\Audit\AuditLogGrid, not in this template.
 *
 * @var string $gridHtml
 */
?>
<?= $gridHtml ?? '' ?>

