<?php
/**
 * @var callable $e
 * @var string $csrfToken
 * @var list<\Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncement> $announcements
 * @var array<int, int> $acknowledgmentCounts
 * @var ?\Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncement $editItem
 * @var string $saveUrl
 * @var string $publishUrl
 * @var string $unpublishUrl
 * @var string $archiveUrl
 * @var string $announcementsUrl
 */
$announcements = $announcements ?? [];
$acknowledgmentCounts = $acknowledgmentCounts ?? [];
$editItem = $editItem ?? null;
?>
<header class="page-header">
    <div class="page-header__copy">
        <p class="page-header__eyebrow">Settings workspace</p>
        <h1>Global Announcements</h1>
        <p class="muted">Broadcast mandatory notifications to active dashboard users and offline users upon their next login.</p>
    </div>
</header>

<div class="admin-grid-layout" style="display: grid; grid-template-columns: minmax(300px, 420px) minmax(0, 1fr); gap: 1.5rem; align-items: start;">
    <section class="card">
        <h2><?= $editItem !== null ? 'Edit Announcement' : 'Draft New Announcement' ?></h2>
        <form method="post" action="<?= $e($saveUrl) ?>" class="admin-form">
            <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
            <?php if ($editItem !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem->id ?>">
            <?php endif; ?>

            <div class="field">
                <label for="announcement-title"><strong>Title</strong></label>
                <input type="text" id="announcement-title" name="title" value="<?= $e($editItem?->title ?? '') ?>" required placeholder="e.g. Scheduled System Maintenance" style="width: 100%;">
            </div>

            <div class="field" style="margin-top: 1rem;">
                <label for="announcement-body"><strong>Message Content</strong></label>
                <textarea id="announcement-body" name="body" rows="6" required placeholder="Write the announcement details here..." style="width: 100%;"><?= $e($editItem?->body ?? '') ?></textarea>
            </div>

            <div class="field" style="margin-top: 1rem;">
                <label for="announcement-status"><strong>Status</strong></label>
                <select id="announcement-status" name="status" style="width: 100%;">
                    <option value="draft"<?= ($editItem?->status ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft (offline/not broadcasted)</option>
                    <option value="published"<?= ($editItem?->status ?? '') === 'published' ? ' selected' : '' ?>>Published (broadcast to active &amp; next login)</option>
                    <option value="archived"<?= ($editItem?->status ?? '') === 'archived' ? ' selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div class="actions" style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                <button type="submit" class="button button--primary"><?= $editItem !== null ? 'Update Announcement' : 'Save Announcement' ?></button>
                <?php if ($editItem !== null): ?>
                    <a href="<?= $e($announcementsUrl) ?>" class="button button--secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Announcements History</h2>
        <?php if ($announcements === []): ?>
            <div class="admin-empty-state" style="padding: 2rem 1rem; text-align: center;">
                <p class="muted">No announcements drafted yet.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-container" style="overflow-x: auto;">
                <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--admin-border, #e5e7eb); text-align: left;">
                            <th style="padding: 0.75rem 0.5rem;">Title</th>
                            <th style="padding: 0.75rem 0.5rem;">Status</th>
                            <th style="padding: 0.75rem 0.5rem;">Published</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Acknowledgments</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $item): ?>
                            <?php $ackCount = $acknowledgmentCounts[$item->id] ?? 0; ?>
                            <tr style="border-bottom: 1px solid var(--admin-border, #e5e7eb);">
                                <td style="padding: 0.75rem 0.5rem;">
                                    <strong><?= $e($item->title) ?></strong>
                                    <div class="muted" style="font-size: 0.875rem; margin-top: 0.25rem;">
                                        <?= $e(mb_strlen($item->body) > 80 ? mb_substr($item->body, 0, 77) . '...' : $item->body) ?>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <?php if ($item->isPublished()): ?>
                                        <span class="badge badge--success" style="padding: 0.2rem 0.5rem; background: #ecfdf5; color: #047857; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Published</span>
                                    <?php elseif ($item->isDraft()): ?>
                                        <span class="badge badge--neutral" style="padding: 0.2rem 0.5rem; background: #f3f4f6; color: #374151; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Draft</span>
                                    <?php else: ?>
                                        <span class="badge badge--muted" style="padding: 0.2rem 0.5rem; background: #e5e7eb; color: #6b7280; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; font-size: 0.875rem;">
                                    <?= $item->publishedAt !== null ? $e($item->publishedAt->format('Y-m-d H:i')) : '<span class="muted">—</span>' ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center; font-weight: 600;">
                                    <?= (int) $ackCount ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; text-align: right;">
                                    <div style="display: inline-flex; gap: 0.25rem; align-items: center; justify-content: flex-end;">
                                        <a href="<?= $e($announcementsUrl . '?id=' . $item->id) ?>" class="button button--sm button--secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">Edit</a>
                                        <?php if ($item->isPublished()): ?>
                                            <form method="post" action="<?= $e($unpublishUrl) ?>" style="display: inline;">
                                                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                <button type="submit" class="button button--sm button--secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">Unpublish</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= $e($publishUrl) ?>" style="display: inline;">
                                                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                <button type="submit" class="button button--sm button--secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; background: #0284c7; color: #fff;">Publish</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (!$item->isArchived()): ?>
                                            <form method="post" action="<?= $e($archiveUrl) ?>" style="display: inline;">
                                                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                <button type="submit" class="button button--sm button--secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">Archive</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
