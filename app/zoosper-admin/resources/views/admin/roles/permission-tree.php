<?php
/** @var iterable<object> $tree */
/** @var array<int> $selected */
/** @var callable $escape */
?>
<link rel="stylesheet" href="/assets/admin/css/permission-explorer.css?v=8d" data-zoosper-permission-explorer-assets>
<script src="/assets/admin/js/permission-explorer.js?v=6d" defer data-zoosper-permission-explorer-assets></script>

<?php foreach ($tree as $group): ?>
    <fieldset>
        <legend><?= $escape($group->label) ?></legend>
        <?php foreach ($group->permissions as $permission): ?>
            <?php $id = (int) $permission['id']; ?>
            <label>
                <input type="checkbox" name="permission_ids[]" value="<?= $id ?>"<?= in_array($id, $selected, true) ? ' checked' : '' ?>>
                <code><?= $escape((string) $permission['code']) ?></code>
                <?= $escape((string) $permission['label']) ?>
            </label>
        <?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
