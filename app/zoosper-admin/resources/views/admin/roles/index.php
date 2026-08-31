<?php
/** @var iterable<array<string,mixed>> $roles */
/** @var callable $escape */
?>
<header class="page-header admin-role-header">
    <div><p class="page-header__eyebrow">Access control</p><h1>Roles &amp; Permissions</h1><p class="muted">Define reusable permission sets and manage assigned administrators.</p></div>
    <a class="button" href="<?= $escape($createUrl) ?>">Create role</a>
</header>
<div class="admin-table-scroll admin-role-list" role="region" aria-label="Roles and permissions" tabindex="0">
<table>
    <thead><tr><th scope="col">ID</th><th scope="col">Label</th><th scope="col">Code</th><th scope="col">Actions</th></tr></thead>
    <tbody><?php foreach ($roles as $role): ?>
        <?php $id = (int) $role['id']; ?>
        <tr><td><?= $id ?></td><td><strong><?= $escape((string) $role['label']) ?></strong></td><td><code><?= $escape((string) $role['code']) ?></code></td><td><a class="button button--secondary" href="<?= $escape($editBaseUrl) ?>?id=<?= $id ?>">Edit</a></td></tr>
    <?php endforeach; ?></tbody>
</table>
</div>










