<?php
/** @var callable $e @var list<array<string, mixed>> $rows */
?>
<table>
    <thead>
        <tr><th>Time</th><th>Email</th><th>Status</th><th>IP</th></tr>
    </thead>
    <tbody>
    <?php if (($rows ?? []) === []): ?>
        <tr><td colspan="4">No login history yet.</td></tr>
    <?php else: ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= $e($row['created_at'] ?? '') ?></td>
                <td><?= $e($row['email'] ?? '') ?></td>
                <td><code><?= $e($row['status'] ?? '') ?></code></td>
                <td><?= $e($row['ip_address'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
