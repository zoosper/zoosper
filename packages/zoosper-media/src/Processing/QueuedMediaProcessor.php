<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use PDO;
use Zoosper\Media\Model\MediaAsset;

/**
 * Offloads media derivative processing to a database-backed queue.
 *
 * This implementation satisfies the MediaProcessorInterface by persisting the
 * processing intent, allowing the HTTP request to complete without waiting
 * for expensive GD operations.
 */
final readonly class QueuedMediaProcessor implements MediaProcessorInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function process(MediaAsset $asset, MediaDerivativePlan $plan): MediaProcessingResult
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO media_processing_queue (asset_id, plan_json, status, attempts, created_at, updated_at)
             VALUES (:asset_id, :plan_json, :status, :attempts, :created_at, :updated_at)'
        );

        $now = gmdate('Y-m-d H:i:s');
        $statement->execute([
            'asset_id' => $asset->id,
            'plan_json' => json_encode($plan->toArray(), JSON_THROW_ON_ERROR),
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return MediaProcessingResult::queued();
    }
}











