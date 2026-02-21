<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Checkout;

final class SubscriptionProductDetector
{
    /**
     * @param array<string, mixed> $cart_item
     */
    public function is_subscription_item(array $cart_item): bool
    {
        $product = $cart_item['data'] ?? null;

        if (! $product instanceof \WC_Product) {
            return false;
        }

        $subscription_types = ['subscription', 'variable-subscription', 'subscription_variation'];
        foreach ($subscription_types as $type) {
            if ($product->is_type($type)) {
                return true;
            }
        }

        $subscription_meta_keys = ['_subscription_price', '_subscription_period', '_subscription_period_interval'];
        foreach ($subscription_meta_keys as $meta_key) {
            if ($product->get_meta($meta_key, true) !== '') {
                return true;
            }
        }

        return (bool) apply_filters('sce_is_subscription_product', false, $product, $cart_item);
    }
}

