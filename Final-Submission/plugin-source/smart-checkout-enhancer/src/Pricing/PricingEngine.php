<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Pricing;

use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Logging\SecureLogger;
use FunnelKit\SCE\Pricing\Rules\PricingRuleInterface;

final class PricingEngine implements HookableInterface
{
    /**
     * @var array<int, PricingRuleInterface>
     */
    private array $rules;

    private SecureLogger $logger;

    /**
     * @param array<int, PricingRuleInterface> $rules
     */
    public function __construct(array $rules, SecureLogger $logger)
    {
        usort(
            $rules,
            static fn (PricingRuleInterface $a, PricingRuleInterface $b): int => $a->priority() <=> $b->priority()
        );
        $this->rules  = $rules;
        $this->logger = $logger;
    }

    public function register_hooks(): void
    {
        add_action('woocommerce_before_calculate_totals', [$this, 'adjust_prices'], 20);
    }

    public function adjust_prices(\WC_Cart $cart): void
    {
        if ((is_admin() && ! wp_doing_ajax()) || ! $cart instanceof \WC_Cart) {
            return;
        }

        foreach ($cart->get_cart() as $item_key => $cart_item) {
            $product = $cart_item['data'] ?? null;
            if (! $product instanceof \WC_Product) {
                continue;
            }

            if (! isset($cart_item['_sce_original_price'])) {
                $cart_item['_sce_original_price'] = (float) $product->get_price();
            }

            $base_price  = (float) $cart_item['_sce_original_price'];
            $final_price = $base_price;
            $applied     = [];

            foreach ($this->rules as $rule) {
                if (! $rule->supports($cart_item, $cart)) {
                    continue;
                }

                $before_price = $final_price;
                $final_price  = $rule->apply($final_price, $cart_item, $cart);
                $applied[] = [
                    'rule_id' => $rule->id(),
                    'priority' => $rule->priority(),
                    'from' => $before_price,
                    'to' => $final_price,
                ];

                if ($rule->is_exclusive()) {
                    break;
                }
            }

            $final_price = max(0.01, wc_format_decimal($final_price, wc_get_price_decimals()));
            $product->set_price((float) $final_price);

            $cart_item['_sce_applied_rules'] = $applied;
            $cart->cart_contents[$item_key]  = $cart_item;

            if (! empty($applied)) {
                $this->logger->info('price_adjusted', [
                    'product_id' => $product->get_id(),
                    'base_price' => $base_price,
                    'final_price' => $final_price,
                    'rules' => $applied,
                ]);
            }
        }
    }
}
