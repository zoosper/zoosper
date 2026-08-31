<?php /** @var callable $e @var string|null $message */ ?>
<?php if (($message ?? '') !== ''): ?>
    <div class="notice notice--danger" role="alert"><p><?= $e($message) ?></p></div>
<?php endif; ?>



