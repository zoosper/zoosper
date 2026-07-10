<?php
/** @var callable $e @var list<array<string, mixed>|object> $pages */
?>
<div class="toolbar"><a class="button" href="/admin/pages/create">Create page</a></div>
<table>
    <thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (($pages ?? []) === []): ?>
        <tr><td colspan="5">No pages found.</td></tr>
    <?php else: ?>
        <?php foreach ($pages as $page): ?>
            <?php
                $id = (int) (is_array($page) ? ($page['id'] ?? 0) : ($page->id ?? 0));
                $title = (string) (is_array($page) ? ($page['title'] ?? '') : ($page->title ?? ''));
                $slug = (string) (is_array($page) ? ($page['slug'] ?? '') : ($page->slug ?? ''));
                $status = (string) (is_array($page) ? ($page['status'] ?? '') : ($page->status ?? ''));
            ?>
            <tr>
                <td><?= $id ?></td>
                <td><?= $e($title) ?></td>
                <td><code><?= $e($slug) ?></code></td>
                <td><code><?= $e($status) ?></code></td>
                <td>
                    <a href="/admin/pages/edit?id=<?= $id ?>">Edit</a>
                    &nbsp;|&nbsp;<a href="/admin/pages/preview?id=<?= $id ?>" target="_blank">Preview</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
