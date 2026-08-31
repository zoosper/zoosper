<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormConfigProviderFactory;
use Zoosper\AdminForm\AdminFormProcessingResult;
use Zoosper\AdminForm\AdminFormProcessorConfigFactory;
use Zoosper\AdminForm\AdminFormProcessorInterface;
use Zoosper\AdminForm\AdminFormProcessorRegistry;
use Zoosper\AdminForm\AdminFormProviderRegistry;
use Zoosper\AdminForm\AdminFormRenderer;
use Zoosper\AdminForm\AdminFormSection;
use Zoosper\AdminForm\AdminFormSectionProviderInterface;
use Zoosper\Page\Admin\Form\PageContentSectionProvider;
use Zoosper\Page\Admin\Form\PageDetailsSectionProvider;
use Zoosper\Page\Admin\Form\PagePublishingSectionProvider;
use Zoosper\Page\Admin\Form\PageSeoSectionProvider;

/**
 * Phase 1.41 (page decoupling, part A) — proves 9 of the 11 admin-form
 * classes zoosper-page depends on now live in Zoosper\AdminForm.
 *
 * File placement: app/zoosper-core/tests/Unit/Form/PageAdminFormCoreRelocationTest.php
 */
it('confirms all 9 relocated admin-form classes exist under Zoosper\\AdminForm', function (): void {
    expect(class_exists(AdminFormSection::class))->toBeTrue();
    expect(interface_exists(AdminFormSectionProviderInterface::class))->toBeTrue();
    expect(class_exists(AdminFormProviderRegistry::class))->toBeTrue();
    expect(class_exists(AdminFormRenderer::class))->toBeTrue();
    expect(interface_exists(AdminFormProcessorInterface::class))->toBeTrue();
    expect(class_exists(AdminFormProcessingResult::class))->toBeTrue();
    expect(class_exists(AdminFormProcessorRegistry::class))->toBeTrue();
    expect(class_exists(AdminFormConfigProviderFactory::class))->toBeTrue();
    expect(class_exists(AdminFormProcessorConfigFactory::class))->toBeTrue();
});

it('confirms all four page form section providers implement the Core interface', function (): void {
    expect(is_subclass_of(PageDetailsSectionProvider::class, AdminFormSectionProviderInterface::class))->toBeTrue();
    expect(is_subclass_of(PageContentSectionProvider::class, AdminFormSectionProviderInterface::class))->toBeTrue();
    expect(is_subclass_of(PageSeoSectionProvider::class, AdminFormSectionProviderInterface::class))->toBeTrue();
    expect(is_subclass_of(PagePublishingSectionProvider::class, AdminFormSectionProviderInterface::class))->toBeTrue();
});

