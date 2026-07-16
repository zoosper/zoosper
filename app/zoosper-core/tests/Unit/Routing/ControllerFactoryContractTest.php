<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use ReflectionClass;

/**
 * Guards against controller / DI-factory constructor drift.
 *
 * If a module config/controllers.php factory passes a NAMED argument the
 * controller constructor does not declare (e.g. the Phase 1.30 `events:`
 * regression), PHP throws "Unknown named parameter" at runtime - a fatal that
 * isolated unit tests never see. This test statically asserts every named
 * argument used in each factory is a real constructor parameter of a controller
 * registered in that same file.
 */

/** @return list<string> */
function controllerConfigFiles(): array
{
    $root = dirname(__DIR__, 5);

    return glob($root . '/app/*/config/controllers.php') ?: [];
}

/**
 * @param array<int|string, mixed> $factories
 * @return array<string, bool>
 */
function controllerParamNames(array $factories): array
{
    $names = [];
    foreach (array_keys($factories) as $class) {
        if (!is_string($class) || !class_exists($class)) {
            continue;
        }
        $constructor = (new ReflectionClass($class))->getConstructor();
        if ($constructor === null) {
            continue;
        }
        foreach ($constructor->getParameters() as $parameter) {
            $names[$parameter->getName()] = true;
        }
    }

    return $names;
}

/** @return list<string> */
function namedArgumentsIn(string $source): array
{
    // Named args in these factories are always followed by `$services` or `new`.
    preg_match_all('/([a-zA-Z_][a-zA-Z0-9_]*):\s*(?:\$services|new\s)/', $source, $matches);

    return array_values(array_unique($matches[1] ?? []));
}

test('every controller factory named argument is a real constructor parameter', function () {
    $files = controllerConfigFiles();
    expect($files)->not->toBe([]);

    foreach ($files as $file) {
        $factories = require $file;
        if (!is_array($factories)) {
            continue;
        }

        $params = controllerParamNames($factories);
        if ($params === []) {
            continue;
        }

        foreach (namedArgumentsIn((string) file_get_contents($file)) as $named) {
            expect($params)->toHaveKey(
                $named,
                sprintf('Named argument "%s:" in %s has no matching constructor parameter.', $named, $file),
            );
        }
    }
});