<?php
/**
 * Pages admin grid filter form.
 *
 * This file intentionally lives under `partials/components/grid` because the
 * admin TemplateRenderer partial helper prefixes partial names with `partials/`.
 * Keeping this mirror prevents `components/grid/page-filters.php` from being
 * resolved as `partials/components/grid/page-filters.php` and failing at runtime.
 *
 * @var callable $e
 * @var string $q
 * @var string $status
 * @var string|int|null $siteId
 * @var int $pageSize
 * @var list<object|array<string,mixed>> $sites
 */
$get = static fn (mixed $item, string $key, mixed $default = ''): mixed => is_array($item) ? ($item[$key] ?? $default) : ($item->{$key} ?? $default);
?>
<form method="get" action="/admin/pages" class="card grid-filters">
    <div class="grid-filter-row">
        <label>Search
            <input type="search" name="q" value="<?= $e($q ?? '') ?>" placeholder="Title or slug">
        </label>
        <label>Status
            <select name="status">
                <option value="">Any status</option>
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                    <option value="<?= $e($value) ?>"<?= ($status ?? '') === $value ? ' selected' : '' ?>><?= $e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Site
            <select name="site_id">
                <option value="">Any site</option>
                <?php foreach (($sites ?? []) as $site): ?>
                    <?php $id = (int) $get($site, 'id'); ?>
                    <option value="<?= $id ?>"<?= (string) ($siteId ?? '') === (string) $id ? ' selected' : '' ?>><?= $e((string) $get($site, 'name')) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Page size
            <select name="page_size">
                <?php foreach ([10, 20, 50, 100] as $size): ?>
                    <option value="<?= $size ?>"<?= (int) ($pageSize ?? 20) === $size ? ' selected' : '' ?>><?= $size ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <div class="toolbar">
        <button type="submit">Apply filters</button>
        <a class="button secondary" href="/admin/pages">Reset</a>
    </div>
</form>
