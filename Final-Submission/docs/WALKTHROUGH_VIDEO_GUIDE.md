# Walkthrough Video Guide

This file is a script/checklist to record the required walkthrough video.

## Target Duration

8 to 12 minutes

## Recording Checklist

1. Show assignment deliverables folder structure.
2. Show plugin ZIP file available in `plugin-zip/`.
3. Open WordPress admin and upload plugin ZIP.
4. Activate plugin and confirm no activation errors.
5. Open `WooCommerce > Settings > Smart Checkout Enhancer`.
6. Explain each setting briefly.
7. Frontend/cart test:
   - Add subscription product.
   - Set customer country to allowed country.
   - Keep subtotal below threshold.
   - Show conditional fee appears.
8. Frontend/cart test for dynamic pricing:
   - Add qualifying product quantity to trigger bulk discount.
   - Add product in markup category.
   - Show price modifications in cart.
9. Checkout placement:
   - Place order and show order meta `_sce_checkout_analytics`.
10. Complete order:
   - Move order status to completed.
   - Show `_sce_background_processed_at` meta after async execution.
11. Show WooCommerce logs for plugin source `smart-checkout-enhancer`.
12. Brief code walkthrough:
   - `Bootstrap.php`
   - `CheckoutFeeManager.php`
   - `PricingEngine.php`
   - `AnalyticsRepository.php`
   - `BackgroundJobProcessor.php`

## File Naming Suggestion

`FunnelKit_Assignment_Walkthrough.mp4`

## Notes

Actual video file recording/export is a manual step and should be placed in the submission package before final upload.

