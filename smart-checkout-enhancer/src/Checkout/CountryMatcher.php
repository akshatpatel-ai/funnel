<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Checkout;

use FunnelKit\SCE\Settings\SettingsRepository;

final class CountryMatcher
{
    private SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function matches_target_country(): bool
    {
        if (! WC()->customer instanceof \WC_Customer) {
            return false;
        }

        $country = WC()->customer->get_shipping_country();
        if ($country === '') {
            $country = WC()->customer->get_billing_country();
        }

        $country = strtoupper(trim((string) $country));
        if ($country === '') {
            return false;
        }

        $allowed_countries = array_map('strtoupper', (array) $this->settings->get('allowed_countries'));
        return in_array($country, $allowed_countries, true);
    }
}

