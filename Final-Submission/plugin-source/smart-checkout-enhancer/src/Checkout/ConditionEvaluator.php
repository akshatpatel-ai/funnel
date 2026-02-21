<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Checkout;

use FunnelKit\SCE\Settings\SettingsRepository;

final class ConditionEvaluator
{
    private SettingsRepository $settings;
    private CountryMatcher $country_matcher;
    private SubscriptionProductDetector $subscription_detector;

    public function __construct(
        SettingsRepository $settings,
        CountryMatcher $country_matcher,
        SubscriptionProductDetector $subscription_detector
    ) {
        $this->settings              = $settings;
        $this->country_matcher       = $country_matcher;
        $this->subscription_detector = $subscription_detector;
    }

    public function has_subscription_product(\WC_Cart $cart): bool
    {
        foreach ($cart->get_cart() as $item) {
            if ($this->subscription_detector->is_subscription_item($item)) {
                return true;
            }
        }

        return false;
    }

    public function should_apply_fee(\WC_Cart $cart): bool
    {
        $has_subscription = $this->has_subscription_product($cart);
        $country_matches  = $this->country_matcher->matches_target_country();
        $threshold        = (float) $this->settings->get('fee_threshold');
        $cart_subtotal    = (float) $cart->get_subtotal();
        $below_threshold  = $cart_subtotal < $threshold;

        return $has_subscription && $country_matches && $below_threshold;
    }
}

