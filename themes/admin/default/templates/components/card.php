<?php
/** @var callable $e @var string $title @var string $body */
?>
<section class="card">
    <?php if (($title ?? '') !== ''): ?><h2><?= $e($title) ?></h2><?php endif; ?>
    <?= $body ?? '' ?>
</section>
