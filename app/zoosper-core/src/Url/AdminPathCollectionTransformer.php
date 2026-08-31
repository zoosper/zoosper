<?php

declare(strict_types=1);

namespace Zoosper\Core\Url;

use InvalidArgumentException;

/**
 * Expands canonical admin paths in module-owned route and menu declarations.
 *
 * The transformer is intentionally unaware of modules, containers and HTTP.
 * Loaders retain ownership of discovery and validation, then pass declarations
 * through this boundary before creating routes or menu items.
 */
final readonly class AdminPathCollectionTransformer
{
    public function __construct(private AdminUrlGenerator $urls)
    {
    }

    /**
     * @param list<array<string, mixed>> $routes
     * @return list<array<string, mixed>>
     */
    public function routes(array $routes): array
    {
        return $this->transform($routes, 'path', 'route');
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    public function menu(array $items): array
    {
        return $this->transform($items, 'url', 'menu item');
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function transform(array $rows, string $field, string $kind): array
    {
        $result = [];
        foreach ($rows as $index => $row) {
            if (!array_key_exists($field, $row)) {
                $result[] = $row;
                continue;
            }
            if (!is_string($row[$field]) || $row[$field] === '' || $row[$field][0] !== '/') {
                throw new InvalidArgumentException(sprintf(
                    'Admin %s declaration at index %d must contain an absolute string %s.',
                    $kind,
                    $index,
                    $field,
                ));
            }
            $row[$field] = $this->urls->expandCanonicalPath($row[$field]);
            $result[] = $row;
        }

        return $result;
    }
}










