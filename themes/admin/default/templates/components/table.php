<?php
/**
 * @var callable $e
 * @var list<string> $headers
 * @var list<list<string>> $rows
 * @var string|null $empty
 * @var string|null $caption
 */
?>
<div class="admin-table-scroll" tabindex="0" role="region" aria-label="<?= $e($caption ?? 'Data table') ?>">
    <table>
        <?php if (($caption ?? '') !== ''): ?><caption><?= $e($caption) ?></caption><?php endif; ?>
        <thead><tr><?php foreach (($headers ?? []) as $header): ?><th scope="col"><?= $e($header) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
        <?php if (($rows ?? []) === []): ?>
            <tr><td class="admin-table-empty" colspan="<?= max(1, count($headers ?? [])) ?>"><?= $e($empty ?? 'No records found.') ?></td></tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= $cell ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
