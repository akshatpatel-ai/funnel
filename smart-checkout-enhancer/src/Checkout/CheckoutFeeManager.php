<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Checkout;

use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Logging\SecureLogger;
use FunnelKit\SCE\Settings\SettingsRepository;

final class CheckoutFeeManager implements HookableInterface
{
    private SettingsRepository $settings;
    private ConditionEvaluator $condition_evaluator;
    private SecureLogger $logger;

    public function __construct(
        SettingsRepository $settings,
        ConditionEvaluator $condition_evaluator,
        SecureLogger $logger
    ) {
        $this->settings            = $settings;
        $this->condition_evaluator = $condition_evaluator;
        $this->logger              = $logger;
    }

    public function register_hooks(): void
    {
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_conditional_fee'], 20, 1);
    }

    public function apply_conditional_fee(\WC_Cart $cart): void
    {
        if ((is_admin() && ! wp_doing_ajax()) || ! $cart instanceof \WC_Cart) {
            return;
        }

        if (! $this->condition_evaluator->should_apply_fee($cart)) {
            return;
        }

        $fee_amount = (float) $this->settings->get('fee_amount');
        if ($fee_amount <= 0) {
            return;
        }

        $fee_label = (string) $this->settings->get('fee_label');
        $cart->add_fee($fee_label, $fee_amount, false);

        $this->logger->info('fee_applied', [
            'fee_label' => $fee_label,
            'fee_amount' => $fee_amount,
            'cart_subtotal' => (float) $cart->get_subtotal(),
        ]);
    }
}

