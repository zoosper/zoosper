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
    <p class="alert alert--danger" role="alert"><?= $escape($error) ?></p>
<?php endif; ?>
<form method="post" action="<?= $escape($action) ?>" class="admin-role-form">
    <header class="page-header admin-role-header"><div><p class="page-header__eyebrow">Access control</p><h1>Role configuration</h1><p class="muted">Identity, permissions and direct user assignments remain one audited role boundary.</p></div></header>
    <section class="card admin-role-identity" aria-labelledby="role-identity-title"><div class="card__header"><h2 id="role-identity-title">Role identity</h2></div><div class="admin-role-identity__fields">
    <input type="hidden" name="_csrf_token" value="<?= $escape($csrfToken) ?>">

    <label>
        Role label
        <input type="text" name="label" value="<?= $escape($label) ?>">
    </label>

    <label>
        Role code
        <input type="text" name="code" value="<?= $escape($code) ?>">
    </label>

    </div></section>

    <section class="card admin-role-section permission-explorer" aria-labelledby="role-permissions-title"><div class="card__header"><h2 id="role-permissions-title">Permission tree</h2><p class="muted">Search, review and select the capabilities granted by this role.</p></div>
    <?= $permissionTree ?>
    </section>

    <section class="card admin-role-section" aria-labelledby="role-users-title"><div class="card__header"><h2 id="role-users-title">Assigned users</h2>
    <p class="muted">Search and tick admin users to assign them directly to this role.</p></div>
    <div class="admin-role-users"><?= $userAssignment ?></div>
    </section>

    <footer class="admin-role-actions"><a class="button button--secondary" href="<?= $escape($backUrl) ?>">Back</a><button type="submit">Save role</button></footer>
</form>

<?php if (!empty($lifecycleHtml)): ?><?= $lifecycleHtml ?><?php endif; ?>
