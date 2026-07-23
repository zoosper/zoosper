<?php
/** @var string $action */
/** @var string $csrfToken */
/** @var string $code */
/** @var string $label */
/** @var string|null $error */
/** @var string $permissionTree */
/** @var string $userAssignment */
/** @var callable $escape */
?>
<?php if ($error !== null): ?>
    <p class="error"><?= $escape($error) ?></p>
<?php endif; ?>
<form method="post" action="<?= $escape($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">

    <label>
        Role label
        <input type="text" name="label" value="<?= $escape($label) ?>">
    </label>

    <label>
        Role code
        <input type="text" name="code" value="<?= $escape($code) ?>">
    </label>

    <h3>Permission Tree</h3>
    <?= $permissionTree ?>

    <h3>Assigned Users</h3>
    <p>Search and tick admin users to assign them directly to this role.</p>
    <?= $userAssignment ?>

    <button type="submit">Save role</button>
    <a href="/admin/roles">Back</a>
</form>
