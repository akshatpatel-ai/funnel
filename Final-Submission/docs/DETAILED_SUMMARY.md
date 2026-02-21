# Detailed Summary Document

## 1. Architecture Decisions

The plugin is implemented using an OOP, service-based design with clear responsibilities:

- `Bootstrap` wires services and registers hookable modules.
- `Container` provides lightweight dependency injection.
- Domain-oriented modules:
  - `Checkout` for fee condition logic.
  - `Pricing` for rule-driven price transformation.
  - `Analytics` for checkout data persistence.
  - `Background` for async post-order processing.
  - `Logging` for secure event logging.
  - `Admin/Settings` for configuration.

This keeps the plugin extensible and testable while avoiding god classes.

## 2. SOLID Application

- Single Responsibility: Each class handles one concern (e.g., `CountryMatcher`, `CheckoutFeeManager`, `AnalyticsRepository`).
- Open/Closed: Pricing rules are pluggable through `PricingRuleInterface` and `sce_pricing_rules` filter.
- Liskov Substitution: Any rule implementation can replace another as long as it respects the interface.
- Interface Segregation: `HookableInterface` is minimal and focused.
- Dependency Inversion: High-level modules depend on abstractions/contracts and repository/services rather than procedural globals.

## 3. Hook Usage

Core hooks used:

- `woocommerce_cart_calculate_fees`
  - Apply conditional fee.
- `woocommerce_before_calculate_totals`
  - Run pricing engine and adjust cart item prices.
- `woocommerce_checkout_order_processed`
  - Persist checkout analytics in DB and order meta.
- `woocommerce_order_status_completed`
  - Queue async background job.
- `sce_process_completed_order`
  - Execute background processor.
- `woocommerce_settings_tabs_array`
- `woocommerce_settings_tabs_sce`
- `woocommerce_update_options_sce`
  - Add and manage admin settings tab.

## 4. Conditional Fee Logic

Fee is applied only when all conditions are true:

1. Cart includes subscription product.
2. Customer country is in configured allowed countries.
3. Cart subtotal is below configured threshold (default 500).

`ConditionEvaluator` centralizes this logic to avoid duplication.

## 5. Dynamic Pricing Logic

Pricing engine executes ordered rules by priority:

- `CategoryMarkupRule` (priority 20)
- `BulkQuantityDiscountRule` (priority 30)

Conflict strategy:

- Deterministic order by priority.
- Optional exclusive rule support (`is_exclusive()`).
- Custom third-party rules can be injected via `sce_pricing_rules`.

## 6. Analytics Storage

Checkout analytics persisted in two places:

- Table: `{prefix}sce_checkout_analytics`
- Order meta: `_sce_checkout_analytics`

Stored fields include:

- `order_id`, `user_id`, `country`
- `cart_total`
- `has_subscription`, `fee_applied`
- `pricing_adjustments` JSON
- creation timestamp

This supports both reporting use-cases and order-local debugging.

## 7. Background Job

On order completion:

- If Action Scheduler is available, enqueue async action.
- Else fallback to WP-Cron single event.

Processor updates order meta (`_sce_background_processed_at`) and logs processed payload.

## 8. Security Measures

- Direct access blocked (`ABSPATH` checks).
- Inputs sanitized:
  - `wc_clean`, `sanitize_text_field`, `sanitize_title`, strict casting.
- Sensitive log fields hashed/redacted.
- DB writes parameterized using `$wpdb->insert` formats.
- Admin settings save path sanitizes all field values by type.
- No unsafe eval / no dynamic includes from user input.

## 9. Performance Considerations

- Rules resolved in a single pass over cart items.
- Pricing uses stored original price in cart item context to prevent repeated compounding.
- Analytics insert is one write per checkout.
- Async work moved off checkout critical path.
- Minimal service initialization with lazy container resolution.

## 10. Trade-Offs

- Chose pragmatic custom table + order meta dual-write for query flexibility and debuggability (slightly more storage).
- Used lightweight custom DI instead of full container/composer dependency to keep deployment simple.
- Default pricing rules are generic and configurable; complex business-specific pricing can be layered via extensibility filter.

