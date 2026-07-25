<?php
/**
 * Page momentum dashboard cards partial.
 *
 * Expects a $cards variable: a list of
 *   ['key' => string, 'label' => string, 'value' => string, 'hint' => string]
 * as produced by PageMomentumCardsPresenter::cards().
 *
 * This partial is framework-agnostic plain PHP and escapes all output.
 *
 * @var array<int, array{key: string, label: string, value: string, hint: string}> $cards
 */

declare(strict_types=1);

$cards = $cards ?? [];

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<div class="page-momentum-cards" role="list">
<?php foreach ($cards as $card): ?>
    <div class="page-momentum-card" role="listitem" data-card="<?= $e($card['key']) ?>">
        <div class="page-momentum-card__label"><?= $e($card['label']) ?></div>
        <div class="page-momentum-card__value"><?= $e($card['value']) ?></div>
        <div class="page-momentum-card__hint"><?= $e($card['hint']) ?></div>
    </div>
<?php endforeach; ?>
<?php if ($cards === []): ?>
    <div class="page-momentum-cards__empty">No page momentum data available yet.</div>
<?php endif; ?>
</div>
