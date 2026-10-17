<?php

declare(strict_types=1);

return [
    \Zoosper\Auth\Http\ApiBearerAuthenticationMiddleware::class,
    \Zoosper\Auth\Http\ApiSessionMiddleware::class,
    \Zoosper\Auth\Http\ApiCsrfMiddleware::class,
];
