<?php

declare(strict_types=1);
$path='packages/zoosper-admin-grid/config/services.php';$s=file_get_contents($path);if($s===false)throw new RuntimeException('read failed');
$uses=['use Zoosper\\AdminGrid\\GridWorkspaceCsvExportService;','use Zoosper\\AdminGrid\\GridWorkspaceAuditedCsvExportService;','use Zoosper\\AdminGrid\\GridWorkspaceExportAuditorFactory;','use Zoosper\\AdminGrid\\GridWorkspaceAuditLoggerInterface;','use Zoosper\\Grid\\GridCsvExporter;'];
$anchor='use Zoosper\\AdminGrid\\GridViewStateResolver;';foreach($uses as $u){if(!str_contains($s,$u)){$s=str_replace($anchor,$anchor."\n".$u,$s);}}
if(!str_contains($s,'GridWorkspaceCsvExportService::class =>')){$entry=<<<'PHP'
    GridCsvExporter::class => static fn (ServiceContainer $services): GridCsvExporter => new GridCsvExporter(),
    GridWorkspaceCsvExportService::class => static fn (ServiceContainer $services): GridWorkspaceCsvExportService => new GridWorkspaceCsvExportService($services->get(GridCsvExporter::class)),
    GridWorkspaceAuditedCsvExportService::class => static fn (ServiceContainer $services): GridWorkspaceAuditedCsvExportService => new GridWorkspaceAuditedCsvExportService(
        $services->get(GridWorkspaceCsvExportService::class),
        GridWorkspaceExportAuditorFactory::create($services->has(GridWorkspaceAuditLoggerInterface::class) ? $services->get(GridWorkspaceAuditLoggerInterface::class) : null),
    ),
PHP;$s=str_replace("return [\n","return [\n".$entry,$s);}file_put_contents($path,$s);
