<?php
/**
 * @var callable $e
 * @var callable $partial
 * @var list<array<string, mixed>|object> $pages
 * @var \Zoosper\Core\Pagination\PaginationResult<mixed>|null $pagination
 * @var \Zoosper\Page\Admin\PageGridCriteria|null $criteria
 * @var list<object|array<string,mixed>> $sites
 */
?>
<div class="toolbar"><a class="button" href="/admin/pages/create">Create page</a></div>

<?php if (isset($criteria)): ?>
    <?= $partial('components/grid/page-filters.php', [
        'q' => $criteria->query,
        'status' => $criteria->status,
        'siteId' => $criteria->siteId,
        'pageSize' => $criteria->pager->pageSize,
        'sites' => $sites ?? [],
    ]) ?>
<?php endif; ?>

<table>
    <thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Site</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (($pages ?? []) === []): ?>
        <tr><td colspan="6">No pages found.</td></tr>
    <?php else: ?>
        <?php foreach ($pages as $page): ?>
            <?php
                $id = (int) (is_array($page) ? ($page['id'] ?? 0) : ($page->id ?? 0));
                $title = (string) (is_array($page) ? ($page['title'] ?? '') : ($page->title ?? ''));
                $slug = (string) (is_array($page) ? ($page['slug'] ?? '') : ($page->slug ?? ''));
                $status = (string) (is_array($page) ? ($page['status'] ?? '') : ($page->status ?? ''));
                $siteName = (string) (is_array($page) ? ($page['site_name'] ?? '') : ($page->siteName ?? ''));
            ?>
            <tr>
                <td><?= $id ?></td>
                <td><?= $e($title) ?></td>
                <td><code><?= $e($slug) ?></code></td>
                <td><code><?= $e($status) ?></code></td>
                <td><?= $e($siteName) ?></td>
                <td><a href="/admin/pages/edit?id=<?= $id ?>">Edit</a> &nbsp;|&nbsp; <a href="/admin/pages/preview?id=<?= $id ?>" target="_blank">Preview</a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php if (isset($pagination, $criteria)): ?>
    <?= $partial('components/grid/pagination.php', [
        'pagination' => $pagination,
        'params' => $criteria->linkParameters(),
        'baseUrl' => '/admin/pages',
    ]) ?>
<?php endif; ?>
