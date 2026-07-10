<?php
/** @var callable $e @var string $action @var string $csrfToken @var object|array<string,mixed>|null $user @var list<array<string,mixed>> $roles @var list<int> $selectedRoles @var string|null $error */
$get = static fn (mixed $item, string $key, mixed $default = ''): mixed => is_array($item) ? ($item[$key] ?? $default) : ($item->{$key} ?? $default);
?>
<?php if (($error ?? '') !== ''): ?><p class="error"><?= $e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $e($action) ?>" class="page-form">
    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
    <label>Name <input type="text" name="name" value="<?= $e((string) $get($user ?? [], 'name')) ?>" required></label>
    <label>Email <input type="email" name="email" value="<?= $e((string) $get($user ?? [], 'email')) ?>" required></label>
    <label>Status
        <?php $status = (string) $get($user ?? [], 'status', 'active'); ?>
        <select name="status"><option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option><option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option></select>
    </label>
    <label>Password <input type="password" name="password" autocomplete="new-password"<?= ($user ?? null) === null ? ' required' : '' ?>></label>
    <fieldset><legend>Roles</legend>
        <?php foreach (($roles ?? []) as $role): ?>
            <?php $id = (int) ($role['id'] ?? 0); ?>
            <label class="checkbox"><input type="checkbox" name="role_ids[]" value="<?= $id ?>"<?= in_array($id, $selectedRoles ?? [], true) ? ' checked' : '' ?>> <?= $e((string) ($role['label'] ?? $role['code'] ?? $id)) ?></label>
        <?php endforeach; ?>
    </fieldset>
    <div class="toolbar"><button type="submit">Save user</button><a class="button secondary" href="/admin/users">Back</a></div>
</form>
