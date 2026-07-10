<?php
/** @var callable $e @var string $name @var string $label @var string|null $value @var int|null $rows */
?>
<label><?= $e($label) ?>
    <textarea name="<?= $e($name) ?>" rows="<?= (int) ($rows ?? 5) ?>"><?= $e($value ?? '') ?></textarea>
</label>
