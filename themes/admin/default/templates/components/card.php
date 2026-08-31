<?php
/** @var callable $e @var string $title @var string $body */
?>
<section class="card">
    <?php if (($title ?? '') !== ''): ?>
        <header class="card__header"><h2 class="card__title"><?= $e($title) ?></h2></header>
    <?php endif; ?>
    <div class="card__body"><?= $body ?? '' ?></div>
</section>



