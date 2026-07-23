<?php
/** @var iterable<array<string,mixed>> $roles */
/** @var callable $escape */
?>
<a href="/admin/roles/create">Create role</a>
<table>
    <tr>
        <th>ID</th>
        <th>Label</th>
        <th>Code</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($roles as $role): ?>
        <?php $id = (int) $role['id']; ?>
        <tr>
            <td><?= $id ?></td>
            <td><?= $escape((string) $role['label']) ?></td>
            <td><code><?= $escape((string) $role['code']) ?></code></td>
            <td><a href="/admin/roles/edit?id=<?= $id ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
</table>
