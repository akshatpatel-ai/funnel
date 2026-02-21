<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Infrastructure;

use FunnelKit\SCE\Analytics\AnalyticsRepository;
use FunnelKit\SCE\Settings\SettingsRepository;

final class Activation
{
    public static function activate(): void
    {
        AnalyticsRepository::create_table();
        SettingsRepository::seed_defaults();
    }
}

