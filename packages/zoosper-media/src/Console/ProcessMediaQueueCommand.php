<?php

declare(strict_types=1);

namespace Zoosper\Media\Console;

use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Media\Processing\GdMediaProcessor;
use Zoosper\Media\Processing\MediaDerivativePlan;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Repository\MediaDerivativeRepository;

/**
 * Background worker for media derivative processing.
 *
 * This command processes pending tasks from the media_processing_queue table,
 * generating requested image derivatives and updating the repository.
 */
final readonly class ProcessMediaQueueCommand implements ConsoleCommandInterface
{
    public function __construct(
        private PDO $pdo,
        private MediaAssetRepository $assets,
        private GdMediaProcessor $processor,
        private MediaDerivativeRepository $derivatives
    ) {
    }

    public function name(): string
    {
        return 'media:process-queue';
    }

    public function description(): string
    {
        return 'Process pending media derivative tasks.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $statement = $this->pdo->query("SELECT * FROM media_processing_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 50");
        $tasks = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($tasks === []) {
            $output->writeln('No pending media tasks.');
            return 0;
        }

        foreach ($tasks as $task) {
            $taskId = (int) $task['id'];
            $assetId = (int) $task['asset_id'];
            
            $output->writeln("Processing task #{$taskId} for asset #{$assetId}...");

            try {
                $this->updateStatus($taskId, 'processing');

                $asset = $this->assets->findById($assetId);
                if ($asset === null) {
                    throw new RuntimeException("Media asset #{$assetId} not found.");
                }

                $planData = json_decode((string) $task['plan_json'], true, 512, JSON_THROW_ON_ERROR);
                $plan = MediaDerivativePlan::fromArray($planData);

                $result = $this->processor->process($asset, $plan);
                if (!$result->successful) {
                    throw new RuntimeException(implode(' ', $result->errors));
                }

                $this->derivatives->replaceForAsset($asset, $plan);
                $this->updateStatus($taskId, 'completed');
                $output->writeln("Successfully processed task #{$taskId}.");
            } catch (Throwable $e) {
                $attempts = (int) ($task['attempts'] ?? 0) + 1;
                $status = $attempts >= 3 ? 'failed' : 'pending';
                $this->updateStatus($taskId, $status, $attempts, $e->getMessage());
                $output->errorln("Failed processing task #{$taskId}: " . $e->getMessage());
            }
        }

        return 0;
    }

    private function updateStatus(int $id, string $status, ?int $attempts = null, ?string $error = null): void
    {
        $sql = 'UPDATE media_processing_queue SET status = :status, updated_at = :now';
        $params = ['status' => $status, 'now' => gmdate('Y-m-d H:i:s'), 'id' => $id];
        
        if ($attempts !== null) {
            $sql .= ', attempts = :attempts';
            $params['attempts'] = $attempts;
        }
        
        if ($error !== null) {
            $sql .= ', error_message = :error';
            $params['error'] = mb_substr($error, 0, 1000);
        }

        $sql .= ' WHERE id = :id';
        $this->pdo->prepare($sql)->execute($params);
    }
}











