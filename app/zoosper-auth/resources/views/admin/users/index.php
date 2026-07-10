<?php /** @var callable $e @var list<object|array<string,mixed>> $users */ ?>
<div class="toolbar"><a class="button" href="/admin/users/create">Create user</a></div>
<table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (($users ?? []) === []): ?>
        <tr><td colspan="5">No admin users found.</td></tr>
    <?php else: ?>
        <?php foreach ($users as $user): ?>
            <?php $id = (int) (is_array($user) ? ($user['id'] ?? 0) : ($user->id ?? 0)); ?>
            <tr>
                <td><?= $id ?></td>
                <td><?= $e((string) (is_array($user) ? ($user['name'] ?? '') : ($user->name ?? ''))) ?></td>
                <td><?= $e((string) (is_array($user) ? ($user['email'] ?? '') : ($user->email ?? ''))) ?></td>
                <td><code><?= $e((string) (is_array($user) ? ($user['status'] ?? '') : ($user->status ?? ''))) ?></code></td>
                <td><a href="/admin/users/edit?id=<?= $id ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
