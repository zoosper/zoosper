<?php
/**
 * @var callable $e
 * @var \Zoosper\Admin\Form\AdminFormField $field
 * @var mixed $value
 */
$config = $field->config;
$type = $field->type;
$name = $field->name;
$label = $field->label;
?>
<?php if ($type === 'textarea'): ?>
    <label><?= $e($label) ?>
        <textarea name="<?= $e($name) ?>" rows="<?= (int) ($config['rows'] ?? 5) ?>"><?= $e((string) ($value ?? '')) ?></textarea>
    </label>
<?php elseif ($type === 'select'): ?>
    <label><?= $e($label) ?>
        <select name="<?= $e($name) ?>">
            <?php foreach (($config['options'] ?? []) as $optionValue => $optionLabel): ?>
                <option value="<?= $e((string) $optionValue) ?>"<?= ((string) $optionValue === (string) ($value ?? '')) ? ' selected' : '' ?>><?= $e((string) $optionLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
<?php elseif ($type === 'readonly'): ?>
    <label><?= $e($label) ?>
        <input type="text" value="<?= $e((string) ($value ?? ($config['value'] ?? ''))) ?>" readonly>
    </label>
<?php else: ?>
    <label><?= $e($label) ?>
        <input type="<?= $e($type) ?>" name="<?= $e($name) ?>" value="<?= $e((string) ($value ?? '')) ?>"<?= ($config['required'] ?? false) ? ' required' : '' ?>>
    </label>
<?php endif; ?>
