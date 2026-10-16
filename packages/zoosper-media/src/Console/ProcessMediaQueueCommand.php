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
/** Background worker for database-backed Media derivative processing. */
/**
 * Declarative runtime entry point discovered through module configuration.
 *
 * @psalm-api
 */
final readonly class ProcessMediaQueueCommand implements ConsoleCommandInterface
{
    private const BATCH_SIZE = 50;
    private const MAX_ATTEMPTS = 3;
    private const STALE_AFTER_SECONDS = 900;
    public function __construct(private PDO $pdo, private MediaAssetRepository $assets, private GdMediaProcessor $processor, private MediaDerivativeRepository $derivatives) {}
    #[\Override] public function name(): string { return 'media:process-queue'; }
    #[\Override] public function description(): string { return 'Atomically claim and process pending Media derivative tasks.'; }
    #[\Override]
    public function run(array $args, ConsoleOutput $output): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $this->recoverStaleClaims($now);
        $statement = $this->pdo->query("SELECT * FROM media_processing_queue WHERE status = 'pending' ORDER BY id ASC LIMIT " . self::BATCH_SIZE);
        $tasks = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if ($tasks === []) { $output->writeln('No pending media tasks.'); return 0; }
        $failures = 0;
        foreach ($tasks as $task) {
            $taskId = (int) $task['id'];
            $assetId = (int) $task['asset_id'];
            if (!$this->claim($taskId, $now)) { continue; }
            $output->writeln("Processing task #{$taskId} for asset #{$assetId}...");
            try {
                $asset = $this->assets->findById($assetId);
                if ($asset === null) { throw new RuntimeException("Media asset #{$assetId} not found."); }
                $planData = json_decode((string) $task['plan_json'], true, 512, JSON_THROW_ON_ERROR);
                $plan = MediaDerivativePlan::fromArray($planData);
                $result = $this->processor->process($asset, $plan);
                if (!$result->successful) { throw new RuntimeException(implode(' ', $result->errors)); }
                $this->derivatives->replaceForAsset($asset, $plan);
                $this->finish($taskId, 'completed', completedAt: gmdate('Y-m-d H:i:s'));
                $output->writeln("Successfully processed task #{$taskId}.");
            } catch (Throwable $e) {
                ++$failures;
                $attempts = (int) ($task['attempts'] ?? 0) + 1;
                $this->finish($taskId, $attempts >= self::MAX_ATTEMPTS ? 'failed' : 'pending', $e->getMessage());
                $output->errorln("Failed processing task #{$taskId}: " . $e->getMessage());
            }
        }
        return $failures === 0 ? 0 : 1;
    }
    private function claim(int $id, string $now): bool
    {
        $statement = $this->pdo->prepare("UPDATE media_processing_queue SET status = 'processing', started_at = :now, completed_at = NULL, error_message = NULL, updated_at = :now WHERE id = :id AND status = 'pending'");
        $statement->execute(['now' => $now, 'id' => $id]);
        return $statement->rowCount() === 1;
    }
    private function recoverStaleClaims(string $now): void
    {
        $cutoff = (new \DateTimeImmutable($now, new \DateTimeZone('UTC')))
            ->modify('-' . self::STALE_AFTER_SECONDS . ' seconds')
            ->format('Y-m-d H:i:s');
        $statement = $this->pdo->prepare("UPDATE media_processing_queue SET status = CASE WHEN attempts + 1 >= :max_attempts THEN 'failed' ELSE 'pending' END, attempts = attempts + 1, error_message = 'Worker lease expired before completion.', started_at = NULL, completed_at = NULL, updated_at = :now WHERE status = 'processing' AND updated_at < :cutoff");
        $statement->execute(['max_attempts' => self::MAX_ATTEMPTS, 'now' => $now, 'cutoff' => $cutoff]);
        $statement->rowCount();
    }
    private function finish(int $id, string $status, ?string $error = null, ?string $completedAt = null): void
    {
        $sql = 'UPDATE media_processing_queue SET status = :status, updated_at = :now, completed_at = :completed_at';
        $params = ['status' => $status, 'now' => gmdate('Y-m-d H:i:s'), 'completed_at' => $completedAt, 'id' => $id];
        if ($error !== null) { $sql .= ', attempts = attempts + 1, error_message = :error'; $params['error'] = mb_substr($error, 0, 1000); }
        else { $sql .= ', error_message = NULL'; }
        if ($status === 'pending') { $sql .= ', started_at = NULL'; }
        $sql .= " WHERE id = :id AND status = 'processing'";
        $statement = $this->pdo->prepare($sql); $statement->execute($params);
        if ($statement->rowCount() !== 1) { throw new RuntimeException("Media queue task #{$id} lost its processing claim."); }
    }
}
