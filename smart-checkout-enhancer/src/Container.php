<?php

declare(strict_types=1);

namespace FunnelKit\SCE;

final class Container
{
    /**
     * @var array<string, callable(self):mixed>
     */
    private array $factories = [];

    /**
     * @var array<string, mixed>
     */
    private array $resolved = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * @return mixed
     */
    public function get(string $id)
    {
        if (array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if (! array_key_exists($id, $this->factories)) {
            throw new \RuntimeException('Service not found: ' . $id);
        }

        $this->resolved[$id] = ($this->factories[$id])($this);
        return $this->resolved[$id];
    }
}

