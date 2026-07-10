<?php /** @var callable $e @var string|null $message */ ?>
<?php if (($message ?? '') !== ''): ?>
    <p class="error"><?= $e($message) ?></p>
<?php endif; ?>
