<?php
/** @var callable $e @var string $name @var string $label @var array<string,string> $options @var string|null $selected */
?>
<label><?= $e($label) ?>
    <select name="<?= $e($name) ?>">
        <?php foreach (($options ?? []) as $value => $text): ?>
            <option value="<?= $e((string) $value) ?>"<?= ((string) $value === (string) ($selected ?? '')) ? ' selected' : '' ?>><?= $e($text) ?></option>
        <?php endforeach; ?>
    </select>
</label>
