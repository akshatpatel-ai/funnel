<?php
/*
Plugin Name: Checkout Fee Test
*/
add_action('woocommerce_cart_calculate_fees', function($cart){
   if (is_admin()) return;
   $country = WC()->customer->get_billing_country();
   $has_subscription = false;
   foreach($cart->get_cart() as $item){
      if ($item['data']->is_type('subscription')) {
         $has_subscription = true;
      }
   }
   if ($has_subscription && $country === 'US' && $cart->subtotal < 500) {
      $cart->add_fee('Handling Fee', 20);
   }
});
```

## Why This Was Not Shipped

- Not OOP.
- Not SOLID.
- Hardcoded country/value.
- No settings UI.
- No analytics persistence.
- No background job.
- No secure logging and no sanitization strategy.
- No extensibility for pricing rules.

## Refactored Outcome

Replaced by production-ready architecture with:

- Service container and bootstrapping.
- Condition evaluator and dedicated domain classes.
- Rule-driven pricing engine with interface contracts.
- Analytics repository with custom table.
- Async job scheduler and processor.
- Secure logger with context redaction.
- Documentation and packaging.

