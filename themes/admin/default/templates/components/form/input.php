<?php
/** @var callable $e @var string $name @var string $label @var string|null $value @var string|null $type @var bool|null $required */
?>
<label><?= $e($label) ?>
    <input type="<?= $e($type ?? 'text') ?>" name="<?= $e($name) ?>" value="<?= $e($value ?? '') ?>"<?= ($required ?? false) ? ' required' : '' ?>>
</label>
