<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Mutable data bag used by admin save pipelines.
 *
 * This object may contain every submitted value, including values contributed by
 * third-party modules. Repositories must still persist only fields that are
 * declared by a FieldDefinitionRegistry write map.
 */
final class EntityDataObject
{
    /** @var array<string, mixed> */
    private array $data = [];


    public function setData(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function getData(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function hasData(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /** @param array<string, mixed> $values */
    public function addData(array $values): self
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && $key !== '') {
                $this->setData($key, $value);
            }
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function only(array $keys): array
    {
        $selected = [];
        foreach ($keys as $key) {
            if (is_string($key) && array_key_exists($key, $this->data)) {
                $selected[$key] = $this->data[$key];
            }
        }

        return $selected;
    }
}










