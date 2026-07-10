<?php
/** @var callable $e @var list<array<string, mixed>> $rows */
?>
<table>
    <thead>
        <tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>ID</th><th>Summary</th></tr>
    </thead>
    <tbody>
    <?php if (($rows ?? []) === []): ?>
        <tr><td colspan="6">No audit records yet.</td></tr>
    <?php else: ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= $e($row['created_at'] ?? '') ?></td>
                <td><?= $e($row['actor_email'] ?? '') ?></td>
                <td><code><?= $e($row['action'] ?? '') ?></code></td>
                <td><?= $e($row['entity_type'] ?? '') ?></td>
                <td><?= $e($row['entity_id'] ?? '') ?></td>
                <td><?= $e($row['summary'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
