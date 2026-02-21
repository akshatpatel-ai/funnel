<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Pricing\Rules;

interface PricingRuleInterface
{
    public function id(): string;

    public function priority(): int;

    /**
     * @param array<string, mixed> $cart_item
     */
    public function supports(array $cart_item, \WC_Cart $cart): bool;

    /**
     * @param array<string, mixed> $cart_item
     */
    public function apply(float $price, array $cart_item, \WC_Cart $cart): float;

    public function is_exclusive(): bool;
}

