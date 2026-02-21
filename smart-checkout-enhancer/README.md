# Smart Checkout Enhancer

Smart Checkout Enhancer is a WooCommerce plugin that provides:

- Conditional checkout fee based on subscription presence, country, and cart threshold.
- Dynamic cart item price adjustment through a rule-based pricing engine.
- Checkout analytics persistence for each order.
- Asynchronous post-order background processing.
- Secure event logging with sensitive field redaction.

## Requirements

- WordPress 6.2+
- WooCommerce 7.0+
- PHP 7.4+

## Installation

1. Copy the `smart-checkout-enhancer` folder into `wp-content/plugins/`.
2. Activate **Smart Checkout Enhancer** from WordPress admin.
3. Ensure WooCommerce is active before activating this plugin.
4. Go to `WooCommerce > Settings > Smart Checkout Enhancer`.
5. Configure:
   - Fee label and fee amount.
   - Threshold and allowed countries.
   - Pricing engine rules (bulk discount and category markup).
   - Secure logging on/off.

## How It Works

### Conditional Fee

Fee is applied in `woocommerce_cart_calculate_fees` only when all conditions are true:

- Cart has at least one subscription product.
- Customer country is in allowed countries.
- Cart subtotal is below threshold.

### Dynamic Pricing

The pricing engine executes in `woocommerce_before_calculate_totals` with priority-ordered rules.
Default rules:

- Category markup rule.
- Bulk quantity discount rule.

Rules are extendable via:

```php
add_filter('sce_pricing_rules', function(array $rules) {
    // Add custom rule implementing PricingRuleInterface.
    return $rules;
});
```

### Analytics

Order-level checkout analytics are saved:

- In custom table: `{prefix}sce_checkout_analytics`
- In order meta: `_sce_checkout_analytics`

### Background Job

After order status becomes `completed`, an async job is scheduled:

- Action Scheduler if available.
- WP Cron fallback otherwise.

Handler action: `sce_process_completed_order`

### Logging

Events are logged through WooCommerce logger source:

- `smart-checkout-enhancer`

Sensitive keys are hashed/redacted (email, phone, address, name, zip/postcode, IP).

## Data Storage

- Option key: `sce_settings`
- Analytics table: `{prefix}sce_checkout_analytics`
- Order meta:
  - `_sce_checkout_analytics`
  - `_sce_background_processed_at`

## Uninstall Behavior

On uninstall, plugin removes:

- `sce_settings` option.
- `{prefix}sce_checkout_analytics` table.

