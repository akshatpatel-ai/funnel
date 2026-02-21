<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Admin;

use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Settings\SettingsRepository;

final class SettingsPage implements HookableInterface
{
    private SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function register_hooks(): void
    {
        add_filter('woocommerce_settings_tabs_array', [$this, 'add_tab'], 80);
        add_action('woocommerce_settings_tabs_sce', [$this, 'render_settings']);
        add_action('woocommerce_update_options_sce', [$this, 'save_settings']);
    }

    /**
     * @param array<string, string> $tabs
     * @return array<string, string>
     */
    public function add_tab(array $tabs): array
    {
        $tabs['sce'] = __('Smart Checkout Enhancer', 'smart-checkout-enhancer');
        return $tabs;
    }

    public function render_settings(): void
    {
        woocommerce_admin_fields($this->get_fields());
    }

    public function save_settings(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        check_admin_referer('woocommerce-settings');

        $fields   = $this->get_fields();
        $settings = $this->settings->all();

        foreach ($fields as $field) {
            $id = $field['id'] ?? '';
            if ($id === '' || strpos($id, 'sce_') !== 0) {
                continue;
            }

            $raw_value = wc_clean(wp_unslash($_POST[$id] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $key       = substr($id, 4);

            switch ($key) {
                case 'fee_amount':
                case 'fee_threshold':
                case 'bulk_discount_percent':
                case 'category_markup_percent':
                    $settings[$key] = max(0.0, (float) $raw_value);
                    break;
                case 'bulk_discount_min_qty':
                    $settings[$key] = max(1, (int) $raw_value);
                    break;
                case 'allowed_countries':
                    $countries = array_filter(array_map('trim', explode(',', strtoupper((string) $raw_value))));
                    $settings[$key] = array_values(array_unique($countries));
                    break;
                case 'logging_enabled':
                    $settings[$key] = $raw_value === 'yes' ? 'yes' : 'no';
                    break;
                default:
                    $settings[$key] = sanitize_text_field((string) $raw_value);
                    break;
            }
        }

        $this->settings->save($settings);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function get_fields(): array
    {
        $options = $this->settings->all();

        return [
            [
                'name' => __('Smart Checkout Enhancer', 'smart-checkout-enhancer'),
                'type' => 'title',
                'desc' => __('Conditional fee, dynamic pricing, analytics and async processing controls.', 'smart-checkout-enhancer'),
                'id'   => 'sce_section',
            ],
            [
                'name' => __('Fee Label', 'smart-checkout-enhancer'),
                'id'   => 'sce_fee_label',
                'type' => 'text',
                'desc' => __('Displayed when the fee is applied.', 'smart-checkout-enhancer'),
                'value' => (string) $options['fee_label'],
            ],
            [
                'name' => __('Fee Amount', 'smart-checkout-enhancer'),
                'id'   => 'sce_fee_amount',
                'type' => 'number',
                'css'  => 'min-width:120px;',
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
                'value' => (string) $options['fee_amount'],
            ],
            [
                'name' => __('Cart Threshold', 'smart-checkout-enhancer'),
                'id'   => 'sce_fee_threshold',
                'type' => 'number',
                'css'  => 'min-width:120px;',
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
                'desc' => __('Fee applies only when cart subtotal is below this value.', 'smart-checkout-enhancer'),
                'value' => (string) $options['fee_threshold'],
            ],
            [
                'name' => __('Allowed Countries', 'smart-checkout-enhancer'),
                'id'   => 'sce_allowed_countries',
                'type' => 'text',
                'desc' => __('Comma-separated ISO country codes, e.g. US,CA,GB.', 'smart-checkout-enhancer'),
                'value' => implode(',', (array) $options['allowed_countries']),
            ],
            [
                'name' => __('Bulk Discount %', 'smart-checkout-enhancer'),
                'id'   => 'sce_bulk_discount_percent',
                'type' => 'number',
                'css'  => 'min-width:120px;',
                'custom_attributes' => ['step' => '0.01', 'min' => '0', 'max' => '100'],
                'value' => (string) $options['bulk_discount_percent'],
            ],
            [
                'name' => __('Bulk Discount Min Qty', 'smart-checkout-enhancer'),
                'id'   => 'sce_bulk_discount_min_qty',
                'type' => 'number',
                'css'  => 'min-width:120px;',
                'custom_attributes' => ['step' => '1', 'min' => '1'],
                'value' => (string) $options['bulk_discount_min_qty'],
            ],
            [
                'name' => __('Category Markup %', 'smart-checkout-enhancer'),
                'id'   => 'sce_category_markup_percent',
                'type' => 'number',
                'css'  => 'min-width:120px;',
                'custom_attributes' => ['step' => '0.01', 'min' => '0', 'max' => '100'],
                'value' => (string) $options['category_markup_percent'],
            ],
            [
                'name' => __('Markup Category Slug', 'smart-checkout-enhancer'),
                'id'   => 'sce_category_markup_slug',
                'type' => 'text',
                'value' => (string) $options['category_markup_slug'],
            ],
            [
                'name' => __('Secure Logging', 'smart-checkout-enhancer'),
                'id'   => 'sce_logging_enabled',
                'type' => 'select',
                'options' => [
                    'yes' => __('Enabled', 'smart-checkout-enhancer'),
                    'no'  => __('Disabled', 'smart-checkout-enhancer'),
                ],
                'value' => (string) $options['logging_enabled'],
            ],
            [
                'type' => 'sectionend',
                'id'   => 'sce_section',
            ],
        ];
    }
}
