# Unified Pricing Engine Architecture Plan

## Goal

Design a pricing engine that:

- Supports conditional rules.
- Handles priority-based execution.
- Resolves rule conflicts predictably.
- Allows third-party extensibility.

## Proposed Folder Structure

```text
src/
  Pricing/
    PricingEngine.php
    Rules/
      PricingRuleInterface.php
      BulkQuantityDiscountRule.php
      CategoryMarkupRule.php
      ...
```

For growth:

```text
src/
  Pricing/
    Context/
      PricingContext.php
    Conflict/
      ConflictResolverInterface.php
      PriorityResolver.php
      BestPriceResolver.php
    Registry/
      RuleRegistry.php
```

## Execution Flow

1. Build cart pricing context.
2. Collect built-in and third-party rules.
3. Sort by priority.
4. For each cart item:
   - Identify applicable rules (`supports()`).
   - Apply each rule in order (`apply()`).
   - Stop if a rule is exclusive.
5. Set final item price.
6. Persist applied rules for analytics.

## Conflict Resolution Strategy

Current strategy:

- Deterministic priority order.
- Exclusive short-circuit support.

Future strategy options:

- Best final price resolver.
- First-match resolver.
- Weighted resolver by rule type.

Strategy can be injected to avoid hardcoding policy in engine core.

## Third-Party Extensibility

Extension point:

- Filter `sce_pricing_rules`.

Contract:

- Implement `PricingRuleInterface`.

This supports plugin-level integration without modifying core code.

## Backward Compatibility Strategy

- Keep public rule interface stable.
- Introduce new interface versions only when required (e.g., `PricingRuleInterfaceV2`).
- Provide adapter layer when evolving context object.
- Maintain existing hooks and filters while deprecating gradually.

## Risk Analysis

1. Rule stacking side effects:
   - Mitigation: deterministic order + recorded applied rules.
2. Performance degradation with many rules:
   - Mitigation: early `supports()` rejection and context caching.
3. Third-party rule quality variance:
   - Mitigation: strict interfaces, defensive casting, logging.
4. Price precision issues:
   - Mitigation: WooCommerce decimal formatting at final write.

## Migration Approach

1. Introduce engine alongside legacy pricing behavior.
2. Feature flag to enable new engine.
3. Run shadow-mode logs comparing legacy vs new outputs.
4. Roll out gradually by store segment/country.
5. Remove legacy path after verification window.

