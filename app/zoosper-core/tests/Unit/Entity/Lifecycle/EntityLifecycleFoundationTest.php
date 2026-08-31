<?php

declare(strict_types=1);

use Zoosper\Core\Entity\Lifecycle\EntityLifecycleBlocker;
use Zoosper\Core\Entity\Lifecycle\EntityLifecycleDecision;
use Zoosper\Core\Entity\Lifecycle\EntityLifecycleDeniedException;
use Zoosper\Core\Entity\Lifecycle\EntityLifecycleOperation;
use Zoosper\Core\Entity\Lifecycle\EntityLifecyclePolicyInterface;
use Zoosper\Core\Entity\Lifecycle\EntityLifecyclePolicyRegistry;
use Zoosper\Core\Entity\Lifecycle\EntityLifecycleService;
use Zoosper\Core\Entity\Lifecycle\EntityLifecycleSubject;

function lifecyclePolicy(string $entityType, callable $decide): EntityLifecyclePolicyInterface
{
    return new class($entityType, $decide) implements EntityLifecyclePolicyInterface {
        public function __construct(private string $type, private $callback) {}
        public function entityType(): string { return $this->type; }
        public function decide(EntityLifecycleSubject $subject, EntityLifecycleOperation $operation): EntityLifecycleDecision
        {
            return ($this->callback)($subject, $operation);
        }
    };
}

it('allows an entity-specific lifecycle operation through the shared policy boundary', function (): void {
    $registry = new EntityLifecyclePolicyRegistry();
    $registry->register(lifecyclePolicy('page', static fn ($subject, $operation) => EntityLifecycleDecision::allow($subject, $operation)));
    $subject = new EntityLifecycleSubject('page', 42, ['status' => 'archived']);
    $decision = (new EntityLifecycleService($registry))->requireAllowed($subject, EntityLifecycleOperation::Delete);

    expect($decision->isAllowed())->toBeTrue()
        ->and($decision->subject)->toBe($subject)
        ->and($decision->operation)->toBe(EntityLifecycleOperation::Delete);
});

it('returns complete blockers and throws a descriptive denial without mutating anything', function (): void {
    $registry = new EntityLifecyclePolicyRegistry();
    $registry->register(lifecyclePolicy('site', static fn ($subject, $operation) => EntityLifecycleDecision::deny($subject, $operation, [
        new EntityLifecycleBlocker('site.has-pages', 'The site still owns published pages.', 'Archive or move the pages first.', 'page', 3),
        new EntityLifecycleBlocker('site.has-domains', 'The site still owns domains.', 'Remove or move the domains first.', 'site_domain', 2),
    ])));
    $service = new EntityLifecycleService($registry);
    $subject = new EntityLifecycleSubject('site', 7);
    $decision = $service->decide($subject, EntityLifecycleOperation::Delete);

    expect($decision->isAllowed())->toBeFalse()
        ->and($decision->blockers)->toHaveCount(2)
        ->and($decision->blockers[0]->referenceCount)->toBe(3)
        ->and(fn () => $service->requireAllowed($subject, EntityLifecycleOperation::Delete))
        ->toThrow(EntityLifecycleDeniedException::class, 'Cannot delete site "7"');
});

it('fails loudly for duplicate missing and dishonest policies', function (): void {
    $registry = new EntityLifecyclePolicyRegistry();
    $policy = lifecyclePolicy('page', static fn ($subject, $operation) => EntityLifecycleDecision::allow($subject, $operation));
    $registry->register($policy);

    expect(fn () => $registry->register($policy))->toThrow(LogicException::class, 'already registered')
        ->and(fn () => $registry->get('media'))->toThrow(LogicException::class, 'No lifecycle policy is registered')
        ->and($registry->entityTypes())->toBe(['page']);

    $dishonest = new EntityLifecyclePolicyRegistry();
    $dishonest->register(lifecyclePolicy('page', static fn () => EntityLifecycleDecision::allow(
        new EntityLifecycleSubject('page', 99),
        EntityLifecycleOperation::Archive,
    )));
    expect(fn () => (new EntityLifecycleService($dishonest))->decide(
        new EntityLifecycleSubject('page', 1),
        EntityLifecycleOperation::Delete,
    ))->toThrow(LogicException::class, 'different subject or operation');
});

it('validates lifecycle value objects and denied decisions', function (): void {
    expect(fn () => new EntityLifecycleSubject('', 1))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new EntityLifecycleBlocker('', 'message'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new EntityLifecycleBlocker('references', 'message', referenceCount: -1))->toThrow(InvalidArgumentException::class)
        ->and(fn () => EntityLifecycleDecision::deny(
            new EntityLifecycleSubject('page', 1),
            EntityLifecycleOperation::Delete,
            [],
        ))->toThrow(InvalidArgumentException::class, 'at least one blocker');
});










