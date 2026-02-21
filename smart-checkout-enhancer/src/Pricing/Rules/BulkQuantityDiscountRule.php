<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Pricing\Rules;

use FunnelKit\SCE\Settings\SettingsRepository;

final class BulkQuantityDiscountRule implements PricingRuleInterface
{
    private SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function id(): string
    {
        return 'bulk_quantity_discount';
    }

    public function priority(): int
    {
        return 30;
    }

    public function is_exclusive(): bool
    {
        return false;
    }

    /**
     * @param array<string, mixed> $cart_item
     */
    public function supports(array $cart_item, \WC_Cart $cart): bool
    {
        $qty = (int) ($cart_item['quantity'] ?? 0);
        $min = (int) $this->settings->get('bulk_discount_min_qty');
        return $qty >= $min;
    }

    /**
     * @param array<string, mixed> $cart_item
     */
    public function apply(float $price, array $cart_item, \WC_Cart $cart): float
    {
        $percent = (float) $this->settings->get('bulk_discount_percent');
        if ($percent <= 0) {
            return $price;
        }

        $discounted = $price - ($price * ($percent / 100));
        return max(0.01, $discounted);
    }
}

