<?php

declare(strict_types=1);

it('uses every named PDO placeholder only once per insert statement', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Repository/PdoMenuAdminRepository.php');

    expect($source)
        ->not->toContain(':now,:now')
        ->toContain('VALUES(:site,:code,:label,:status,:created_at,:updated_at)')
        ->toContain('VALUES(:menu,:parent,:page,:label,:url,:target,:position,:status,:created_at,:updated_at)')
        ->toContain("'created_at'=>\$now,'updated_at'=>\$now");

    preg_match_all("/'([a-z_]+)'\s*=>/", $source, $matches);
    expect($matches[1])->toContain('created_at')->toContain('updated_at');
});
