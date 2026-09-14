<?php
declare(strict_types=1);

namespace Panje\Core;

final class Container
{
    /** @var array<string, callable> */
    private array $factories = [];
    /** @var array<string, object> */
    private array $instances = [];

    public function singleton(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) return $this->instances[$id];
        if (!isset($this->factories[$id])) throw new \RuntimeException("Service not registered: {$id}");
        return $this->instances[$id] = ($this->factories[$id])($this);
    }
}
