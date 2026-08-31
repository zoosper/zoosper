<?php

declare(strict_types=1);

namespace Zoosper\Page\Application\Save;

use RuntimeException;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
use Zoosper\AdminForm\AdminFormProcessorRegistry;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRevisionService;

/** Owns Page form processing, input normalisation, lifecycle execution and persistence. */
final readonly class PageSaveCoordinator
{
    public function __construct(
        private PageRepository $pages,
        private HtmlSanitizerInterface $sanitizer,
        private ?ConfigRepository $config = null,
        private ?AdminFormProcessorRegistry $processors = null,
        private ?EntitySaveLifecycleRunner $lifecycle = null,
        private ?ErrorHandler $errors = null,
        private ?PageRevisionService $revisions = null,
    ) {
    }

    /** @param array<string, mixed> $form */
    public function create(array $form, AdminUser $user): PageSaveResult
    {
        return $this->save('create', $form, null, $user);
    }

    /** @param array<string, mixed> $form */
    public function update(array $form, Page $page, AdminUser $user): PageSaveResult
    {
        return $this->save('update', $form, $page, $user);
    }

    /** @param array<string, mixed> $form */
    private function save(string $action, array $form, ?Page $page, AdminUser $user): PageSaveResult
    {
        $processed = $this->processors?->process('page.form', $form, [
            'action' => $action,
            'page' => $page,
            'user' => $user,
        ]);
        if ($processed !== null && !$processed->valid) {
            return PageSaveResult::failure(implode(' ', $processed->errors), true);
        }

        try {
            $input = PageSaveInput::fromForm($form, $this->sanitizer, $this->config);
            $pageId = $page?->id;
            $context = new EntitySaveContext(
                'page',
                (new EntityDataObject())->addData($form),
                new FieldDefinitionRegistry(),
                $pageId,
            );
            $persist = function (EntitySaveContext $context) use ($action, $input, $page, $user, &$pageId): void {
                if ($action === 'create') {
                    $pageId = $this->pages->create(
                        siteId: $input->siteId,
                        title: $input->title,
                        slug: $input->slug,
                        content: $input->content,
                        status: $input->publish ? 'published' : 'draft',
                        userId: $user->id,
                        contentFormat: $input->contentFormat,
                        contentJson: $input->contentJson,
                        metaTitle: $input->metaTitle,
                        metaDescription: $input->metaDescription,
                        metaKeywords: $input->metaKeywords,
                        canonicalUrl: $input->canonicalUrl,
                    );
                    if ($this->revisions !== null && $pageId !== null) {
                        $created = $this->pages->findById((int) $pageId);
                        if ($created !== null) { $this->revisions->capturePage($created, $user->id); }
                    }
                    return;
                }
                if ($this->revisions !== null && $page !== null) {
                    $this->revisions->capturePage($page, $user->id);
                }
                $this->pages->update(
                    id: (int) $pageId,
                    siteId: $input->siteId,
                    title: $input->title,
                    slug: $input->slug,
                    content: $input->content,
                    userId: $user->id,
                    contentFormat: $input->contentFormat,
                    contentJson: $input->contentJson,
                    metaTitle: $input->metaTitle,
                    metaDescription: $input->metaDescription,
                    metaKeywords: $input->metaKeywords,
                    canonicalUrl: $input->canonicalUrl,
                );
                if ($input->publish) {
                    $this->pages->publish((int) $pageId, $user->id);
                }
            };
            $context = $this->lifecycle?->run($context, $persist) ?? $this->runDirect($context, $persist);
            if ($context->hasErrors()) {
                return PageSaveResult::failure($this->firstError($context));
            }

            return PageSaveResult::success((int) $pageId);
        } catch (RuntimeException $exception) {
            $this->errors?->logException($exception, ['service' => self::class, 'action' => $action]);
            return PageSaveResult::failure($exception->getMessage());
        }
    }

    /** @param callable(EntitySaveContext): void $persist */
    private function runDirect(EntitySaveContext $context, callable $persist): EntitySaveContext
    {
        $persist($context);
        return $context;
    }

    private function firstError(EntitySaveContext $context): string
    {
        foreach ($context->errors() as $errors) {
            foreach ($errors as $message) {
                return (string) $message;
            }
        }
        return 'Please review the form.';
    }
}











