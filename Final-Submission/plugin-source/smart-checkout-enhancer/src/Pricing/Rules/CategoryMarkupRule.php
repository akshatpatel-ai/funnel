<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Pricing\Rules;

use FunnelKit\SCE\Settings\SettingsRepository;

final class CategoryMarkupRule implements PricingRuleInterface
{
    private SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function id(): string
    {
        return 'category_markup';
    }

    public function priority(): int
    {
        return 20;
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
        $slug    = sanitize_title((string) $this->settings->get('category_markup_slug'));
        $product = $cart_item['data'] ?? null;

        if ($slug === '' || ! $product instanceof \WC_Product) {
            return false;
        }

        return has_term($slug, 'product_cat', $product->get_id());
    }

    /**
     * @param array<string, mixed> $cart_item
     */
    public function apply(float $price, array $cart_item, \WC_Cart $cart): float
    {
        $percent = (float) $this->settings->get('category_markup_percent');
        if ($percent <= 0) {
            return $price;
        }

        return $price + ($price * ($percent / 100));
    }
}

