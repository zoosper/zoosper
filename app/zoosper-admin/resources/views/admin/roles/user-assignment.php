<?php
/** @var iterable<object> $users */
/** @var array<int> $selected */
/** @var callable $escape */
$hasUsers = false;
?>
<?php foreach ($users as $user): ?>
    <?php $hasUsers = true; ?>
    <label>
        <input type="checkbox" name="user_ids[]" value="<?= (int) $user->id ?>"<?= in_array($user->id, $selected, true) ? ' checked' : '' ?>>
        <?= $escape($user->name) ?> <?= $escape($user->email) ?>
    </label>
<?php endforeach; ?>
<?php if (! $hasUsers): ?>
    <p>No admin users found.</p>
<?php endif; ?>
