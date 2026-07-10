<?php /** @var callable $e @var list<array<string,mixed>> $roles */ ?>
<div class="toolbar"><a class="button" href="/admin/roles/create">Create role</a></div>
<table>
    <thead><tr><th>ID</th><th>Label</th><th>Code</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach (($roles ?? []) as $role): ?>
        <tr><td><?= (int) $role['id'] ?></td><td><?= $e((string) $role['label']) ?></td><td><code><?= $e((string) $role['code']) ?></code></td><td><a href="/admin/roles/edit?id=<?= (int) $role['id'] ?>">Edit</a></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
