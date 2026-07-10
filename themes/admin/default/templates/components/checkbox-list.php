<?php
/** @var callable $e @var string $name @var list<array<string, mixed>> $items @var list<int> $selected */
?>
<?php foreach (($items ?? []) as $item): ?>
    <?php $id = (int) ($item['id'] ?? 0); ?>
    <label class="checkbox">
        <input type="checkbox" name="<?= $e($name) ?>[]" value="<?= $id ?>"<?= in_array($id, $selected ?? [], true) ? ' checked' : '' ?>>
        <strong><?= $e((string) ($item['label'] ?? $item['name'] ?? $item['code'] ?? $id)) ?></strong>
        <?php if (isset($item['code'])): ?><span class="muted"><?= $e((string) $item['code']) ?></span><?php endif; ?>
    </label>
<?php endforeach; ?>
