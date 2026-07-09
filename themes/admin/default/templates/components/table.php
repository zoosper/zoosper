<?php
/**
 * @var callable $e
 * @var list<string> $headers
 * @var list<list<string>> $rows
 * @var string|null $empty
 */
?>
<table>
    <thead><tr><?php foreach (($headers ?? []) as $header): ?><th><?= $e($header) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
    <?php if (($rows ?? []) === []): ?>
        <tr><td colspan="<?= max(1, count($headers ?? [])) ?>"><?= $e($empty ?? 'No records found.') ?></td></tr>
    <?php else: ?>
        <?php foreach ($rows as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= $cell ?></td><?php endforeach; ?></tr><?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
