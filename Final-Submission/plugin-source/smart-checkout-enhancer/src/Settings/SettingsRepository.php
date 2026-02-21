<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Settings;

final class SettingsRepository
{
    private const OPTION_KEY = 'sce_settings';

    /**
     * @var array<string, mixed>
     */
    private array $defaults = [
        'fee_amount'                => 20.0,
        'fee_label'                 => 'Subscription Handling Fee',
        'fee_threshold'             => 500.0,
        'allowed_countries'         => ['US'],
        'bulk_discount_percent'     => 10.0,
        'bulk_discount_min_qty'     => 3,
        'category_markup_percent'   => 5.0,
        'category_markup_slug'      => 'premium',
        'logging_enabled'           => 'yes',
    ];

    public function get(string $key)
    {
        $options = get_option(self::OPTION_KEY, []);
        $value   = $options[$key] ?? $this->defaults[$key] ?? null;

        if ($key === 'allowed_countries' && is_string($value)) {
            return array_filter(array_map('trim', explode(',', strtoupper($value))));
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $stored = get_option(self::OPTION_KEY, []);
        if (! is_array($stored)) {
            $stored = [];
        }

        return array_merge($this->defaults, $stored);
    }

    /**
     * @param array<string, mixed> $value
     */
    public function save(array $value): void
    {
        update_option(self::OPTION_KEY, $value);
    }

    public static function seed_defaults(): void
    {
        $instance = new self();
        $stored   = get_option(self::OPTION_KEY, null);

        if (! is_array($stored)) {
            update_option(self::OPTION_KEY, $instance->defaults);
        }
    }
}

