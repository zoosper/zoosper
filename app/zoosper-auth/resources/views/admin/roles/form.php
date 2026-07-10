<?php
/**
 * @var callable $e
 * @var string $action
 * @var string $csrfToken
 * @var array<string,mixed>|null $role
 * @var list<object> $permissionTree
 * @var list<int> $selectedPermissions
 * @var list<object|array<string,mixed>> $users
 * @var list<int> $selectedUsers
 * @var string|null $error
 */
?>
<?php if (($error ?? '') !== ''): ?><p class="error"><?= $e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $e($action) ?>" class="page-form">
    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
    <label>Role label <input type="text" name="label" value="<?= $e((string) ($role['label'] ?? '')) ?>" required></label>
    <label>Role code <input type="text" name="code" value="<?= $e((string) ($role['code'] ?? '')) ?>" required></label>

    <section class="card"><h2>Permission Tree</h2>
        <?php foreach (($permissionTree ?? []) as $group): ?>
            <fieldset><legend><?= $e($group->label ?? '') ?></legend>
                <?php foreach (($group->permissions ?? []) as $permission): ?>
                    <?php $id = (int) ($permission['id'] ?? 0); ?>
                    <label class="checkbox"><input type="checkbox" name="permission_ids[]" value="<?= $id ?>"<?= in_array($id, $selectedPermissions ?? [], true) ? ' checked' : '' ?>> <strong><?= $e((string) ($permission['code'] ?? '')) ?></strong> <span class="muted"><?= $e((string) ($permission['label'] ?? '')) ?></span></label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
    </section>

    <section class="card"><h2>Assigned Users</h2>
        <input type="search" placeholder="Search users by name or email" oninput="document.querySelectorAll('[data-role-user]').forEach(function(row){row.style.display=row.textContent.toLowerCase().includes(event.target.value.toLowerCase())?'flex':'none';})">
        <?php foreach (($users ?? []) as $user): ?>
            <?php $id = (int) (is_array($user) ? ($user['id'] ?? 0) : ($user->id ?? 0)); ?>
            <label class="checkbox" data-role-user><input type="checkbox" name="user_ids[]" value="<?= $id ?>"<?= in_array($id, $selectedUsers ?? [], true) ? ' checked' : '' ?>> <?= $e((string) (is_array($user) ? ($user['name'] ?? '') : ($user->name ?? ''))) ?> <span class="muted"><?= $e((string) (is_array($user) ? ($user['email'] ?? '') : ($user->email ?? ''))) ?></span></label>
        <?php endforeach; ?>
    </section>

    <div class="toolbar"><button type="submit">Save role</button><a class="button secondary" href="/admin/roles">Back</a></div>
</form>
