<?php
/** @var iterable<object> $tree */
/** @var array<int> $selected */
/** @var callable $escape */
?>
<link rel="stylesheet" href="/asset/zoosper-auth/css/permission-explorer.css" data-zoosper-permission-explorer-assets>
<script src="/asset/zoosper-auth/js/permission-explorer.js" defer data-zoosper-permission-explorer-assets></script>

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
