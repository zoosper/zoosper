<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Core\Form\AdminFormConfigAggregator;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Form\AdminFormConfigProviderFactory;
use Zoosper\Core\Form\AdminFormProviderRegistry;
use Zoosper\Core\Form\AdminFormRenderer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Model\Page;
use Zoosper\Site\Repository\SiteRepository;

/** Owns the complete extensible Page Admin form presentation boundary. */
final readonly class PageAdminFormRenderer
{
    public function __construct(
        private CsrfTokenManager $csrf,
        private SiteRepository $sites,
        private ?ContentEditorInterface $contentEditor = null,
        private ?AdminFormProviderRegistry $sections = null,
        private ?AdminFormRenderer $renderer = null,
        private ?AdminFormConfigProviderFactory $configFactory = null,
        private ?ConfigRepository $config = null,
        private ?AdminUrlGenerator $adminUrls = null,
        private ?string $projectRoot = null,
    ) {
    }

    /** @param array<string, mixed> $submitted */
    public function render(string $action, ?Page $page = null, ?string $error = null, array $submitted = []): string
    {
        $siteId = (int) ($submitted['site_id'] ?? $page?->siteId ?? 0);
        $content = $this->escape((string) ($submitted['content'] ?? $page?->content ?? ''));
        $contentJson = $this->escape((string) ($submitted['content_json'] ?? $page?->contentJson ?? ''));
        $context = [
            'page' => $page,
            'submitted' => $submitted,
            'siteOptions' => $this->siteOptions($siteId),
            'title' => $this->escape((string) ($submitted['title'] ?? $page?->title ?? '')),
            'slug' => $this->escape((string) ($submitted['slug'] ?? $page?->slug ?? '')),
            'editorHtml' => $this->contentEditor($content, $page, $contentJson),
            'contentJson' => $contentJson,
            'metaTitle' => $this->escape((string) ($submitted['meta_title'] ?? $page?->metaTitle ?? '')),
            'metaDescription' => $this->escape((string) ($submitted['meta_description'] ?? $page?->metaDescription ?? '')),
            'metaKeywords' => $this->escape((string) ($submitted['meta_keywords'] ?? $page?->metaKeywords ?? '')),
            'canonicalUrl' => $this->escape((string) ($submitted['canonical_url'] ?? $page?->canonicalUrl ?? '')),
            'publishChecked' => (isset($submitted['publish']) || $page?->isPublished()) ? ' checked' : '',
            'backUrl' => $this->escape($this->adminUrl('/pages')),
        ];
        $sections = ($this->sections ?? $this->defaultSections())->sectionsFor('page.form', $context);
        $html = ($this->renderer ?? new AdminFormRenderer())->render($action, $this->csrf->token(), $sections);

        return $error !== null ? '<p class="error">' . $this->escape($error) . '</p>' . $html : $html;
    }

    private function contentEditor(string $escapedContent, ?Page $page, string $escapedContentJson): string
    {
        $content = html_entity_decode($escapedContent, ENT_QUOTES, 'UTF-8');
        $contentJson = html_entity_decode($escapedContentJson, ENT_QUOTES, 'UTF-8');
        if ($this->contentEditor === null) {
            return '<input type="hidden" name="content_json" value="' . $escapedContentJson . '">'
                . '<textarea name="content" rows="14" required>' . $escapedContent . '</textarea>';
        }
        return $this->contentEditor->render('content', $content, [
            'label' => 'Content', 'rows' => 14, 'required' => true,
            'page' => $page, 'content_json' => $contentJson,
        ]);
    }

    private function siteOptions(int $selectedSiteId): string
    {
        $html = '';
        foreach ($this->sites->allActive() as $site) {
            $selected = $site->id === $selectedSiteId ? ' selected' : '';
            $html .= '<option value="' . $site->id . '"' . $selected . '>'
                . $this->escape($site->name . ' (' . $site->code . ')') . '</option>';
        }
        return $html;
    }

    private function defaultSections(): AdminFormProviderRegistry
    {
        $rootConfig = $this->config?->array('admin_forms') ?? [];
        $moduleConfig = (new AdminFormConfigAggregator($this->projectRoot ?? dirname(__DIR__, 5)))->aggregate($rootConfig);
        return ($this->configFactory ?? new AdminFormConfigProviderFactory())->create($moduleConfig, [
            'page.form' => [
                PageDetailsSectionProvider::class,
                PageContentSectionProvider::class,
                PageSeoSectionProvider::class,
                PagePublishingSectionProvider::class,
            ],
        ]);
    }

    private function adminUrl(string $path): string
    {
        return $this->adminUrls?->url(ltrim($path, '/')) ?? '/admin' . $path;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
