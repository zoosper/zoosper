<?php
/**
 * Tag-style selector component with checkbox fallback.
 *
 * JavaScript enhances this markup into a modern tag selector. Without
 * JavaScript, editors still get a safe checkbox group and cannot accidentally
 * lose selections by missing Ctrl/Cmd in a native multi-select box.
 *
 * @var callable $e
 * @var string $name
 * @var string $label
 * @var list<array{id:int|string,label:string,description?:string}> $options
 * @var list<int|string> $selected
 * @var string|null $help
 */
$normalisedSelected = array_map(static fn (int|string $value): string => (string) $value, $selected ?? []);
$componentId = 'tag-selector-' . preg_replace('/[^a-z0-9_\-]+/i', '-', $name);
?>
<div class="zoosper-tag-selector" data-zoosper-tag-selector id="<?= $e($componentId) ?>">
    <fieldset class="zoosper-tag-selector__fieldset">
        <legend><?= $e($label) ?></legend>
        <?php if (($help ?? '') !== ''): ?>
            <p class="muted"><?= $e((string) $help) ?></p>
        <?php endif; ?>

        <div class="zoosper-tag-selector__selected" data-tag-selected aria-live="polite"></div>

        <label class="zoosper-tag-selector__search">
            <span class="sr-only">Search <?= $e($label) ?></span>
            <input type="search" data-tag-search placeholder="Search and select..." autocomplete="off">
        </label>

        <div class="zoosper-tag-selector__options" data-tag-options>
            <?php foreach ($options as $option): ?>
                <?php
                    $value = (string) $option['id'];
                    $isChecked = in_array($value, $normalisedSelected, true);
                ?>
                <label class="zoosper-tag-selector__option" data-tag-option data-label="<?= $e(strtolower((string) $option['label'])) ?>">
                    <input
                        type="checkbox"
                        name="<?= $e($name) ?>[]"
                        value="<?= $e($value) ?>"
                        data-tag-checkbox
                        <?= $isChecked ? 'checked' : '' ?>
                    >
                    <span><?= $e((string) $option['label']) ?></span>
                    <?php if (($option['description'] ?? '') !== ''): ?>
                        <small><?= $e((string) $option['description']) ?></small>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>
</div>
