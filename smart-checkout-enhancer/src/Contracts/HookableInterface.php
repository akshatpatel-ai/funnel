<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Contracts;

interface HookableInterface
{
    public function register_hooks(): void;
}

