<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Analytics;

use FunnelKit\SCE\Checkout\ConditionEvaluator;
use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Logging\SecureLogger;

final class CheckoutAnalyticsTracker implements HookableInterface
{
    private AnalyticsRepository $repository;
    private ConditionEvaluator $condition_evaluator;
    private SecureLogger $logger;

    public function __construct(
        AnalyticsRepository $repository,
        ConditionEvaluator $condition_evaluator,
        SecureLogger $logger
    ) {
        $this->repository          = $repository;
        $this->condition_evaluator = $condition_evaluator;
        $this->logger              = $logger;
    }

    public function register_hooks(): void
    {
        add_action('woocommerce_checkout_order_processed', [$this, 'store_analytics'], 20, 3);
    }

    /**
     * @param array<string, mixed> $posted_data
     */
    public function store_analytics(int $order_id, array $posted_data, \WC_Order $order): void
    {
        if (! WC()->cart instanceof \WC_Cart) {
            return;
        }

        $cart = WC()->cart;
        $pricing_adjustments = [];

        foreach ($cart->get_cart() as $cart_item) {
            if (! empty($cart_item['_sce_applied_rules'])) {
                $pricing_adjustments[] = [
                    'product_id' => isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0,
                    'rules' => $cart_item['_sce_applied_rules'],
                ];
            }
        }

        $country = '';
        if (WC()->customer instanceof \WC_Customer) {
            $country = WC()->customer->get_shipping_country();
            if ($country === '') {
                $country = WC()->customer->get_billing_country();
            }
        }

        $payload = [
            'order_id' => $order_id,
            'user_id' => (int) $order->get_user_id(),
            'country' => $country,
            'cart_total' => (float) $cart->get_subtotal(),
            'has_subscription' => $this->condition_evaluator->has_subscription_product($cart),
            'fee_applied' => $this->condition_evaluator->should_apply_fee($cart),
            'pricing_adjustments' => $pricing_adjustments,
        ];

        $stored = $this->repository->insert($payload);

        $order->update_meta_data('_sce_checkout_analytics', $payload);
        $order->save_meta_data();

        $this->logger->info('analytics_stored', [
            'order_id' => $order_id,
            'stored' => $stored,
            'country' => $country,
        ]);
    }
}
