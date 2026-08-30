<?php

declare(strict_types=1);

namespace Zoosper\Core\Announcement;

interface AdminAnnouncementProviderInterface
{
    public function findUnacknowledgedForUser(int $userId): ?object;
}
