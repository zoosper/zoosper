<?php
/** @var iterable<object> $users */
/** @var array<int> $selected */
/** @var callable $escape */
$hasUsers = false;
$userList = is_array($users) ? $users : iterator_to_array($users);
$totalUsers = count($userList);
$selectedCount = count($selected);
?>
<div class="admin-role-user-assignment" data-role-user-assignment>
    <div class="admin-role-user-toolbar">
        <label class="admin-role-user-search-label">
            <span>Search users</span>
            <input type="search" class="admin-role-user-search" data-role-user-search placeholder="Name or email address" autocomplete="off" aria-label="Search assigned users">
        </label>
        <div class="admin-role-user-actions">
            <button type="button" class="admin-role-user-action-btn" data-role-user-action="select-visible">Select visible</button>
            <button type="button" class="admin-role-user-action-btn" data-role-user-action="clear-visible">Clear visible</button>
        </div>
        <output class="admin-role-user-count" data-role-user-count aria-live="polite"><?= $selectedCount ?> of <?= $totalUsers ?> selected</output>
    </div>
    <div class="admin-role-user-options" data-role-user-options>
    <?php foreach ($userList as $user): ?>
        <?php $hasUsers = true; ?>
        <?php $isSelected = in_array($user->id, $selected, true); ?>
        <label class="admin-role-user-option<?= $isSelected ? ' is-selected' : '' ?>" data-role-user-item>
            <input type="checkbox" name="user_ids[]" value="<?= (int) $user->id ?>"<?= $isSelected ? ' checked' : '' ?>>
            <span class="admin-role-user-meta">
                <strong class="admin-role-user-name"><?= $escape($user->name) ?></strong>
                <span class="admin-role-user-email"><?= $escape($user->email) ?></span>
            </span>
        </label>
    <?php endforeach; ?>
    </div>
    <?php if (! $hasUsers): ?>
        <p class="admin-role-user-empty muted">No admin users found.</p>
    <?php endif; ?>
</div>
