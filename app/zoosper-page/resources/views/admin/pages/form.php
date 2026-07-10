<?php
/**
 * @var callable $e
 * @var string $action
 * @var string $csrfToken
 * @var array<string, mixed>|object|null $page
 * @var list<object|array<string,mixed>> $sites
 * @var string|null $error
 */
$get = static function (mixed $item, string $key, mixed $default = ''): mixed {
    return is_array($item) ? ($item[$key] ?? $default) : ($item->{$key} ?? $default);
};
?>
<?php if (($error ?? '') !== ''): ?><p class="error"><?= $e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $e($action) ?>" class="page-form">
    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
    <label>Site
        <select name="site_id">
            <?php foreach (($sites ?? []) as $site): ?>
                <?php $siteId = (int) $get($site, 'id'); ?>
                <option value="<?= $siteId ?>"<?= (int) $get($page ?? [], 'siteId', $get($page ?? [], 'site_id', 0)) === $siteId ? ' selected' : '' ?>><?= $e((string) $get($site, 'name')) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Title <input type="text" name="title" value="<?= $e((string) $get($page ?? [], 'title')) ?>" required></label>
    <label>Slug <input type="text" name="slug" value="<?= $e((string) $get($page ?? [], 'slug')) ?>" required></label>
    <label>Status
        <?php $status = (string) $get($page ?? [], 'status', 'draft'); ?>
        <select name="status">
            <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Draft</option>
            <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Published</option>
            <option value="archived"<?= $status === 'archived' ? ' selected' : '' ?>>Archived</option>
        </select>
    </label>
    <label>Content <textarea name="content" rows="12"><?= $e((string) $get($page ?? [], 'content')) ?></textarea></label>
    <label>Meta title <input type="text" name="meta_title" value="<?= $e((string) $get($page ?? [], 'metaTitle', $get($page ?? [], 'meta_title', ''))) ?>"></label>
    <div class="toolbar"><button type="submit">Save page</button><a class="button secondary" href="/admin/pages">Back</a></div>
</form>
