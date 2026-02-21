<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Infrastructure;

final class Deactivation
{
    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('sce_process_completed_order');
    }
}

