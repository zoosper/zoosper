<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Describes the execution boundary required by a bulk action. */
enum GridBulkExecutionType: string
{
    case CLIENT_DOWNLOAD = 'client_download';
    case SERVER_DOWNLOAD = 'server_download';
    case SERVER_MUTATION = 'server_mutation';
    case REMOTE_MUTATION = 'remote_mutation';
}











