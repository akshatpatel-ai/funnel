<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Background;

use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Logging\SecureLogger;

final class BackgroundJobProcessor implements HookableInterface
{
    private SecureLogger $logger;

    public function __construct(SecureLogger $logger)
    {
        $this->logger = $logger;
    }

    public function register_hooks(): void
    {
        add_action('sce_process_completed_order', [$this, 'process'], 10, 1);
    }

    /**
     * @param int|array<string, mixed> $payload
     */
    public function process($payload): void
    {
        $order_id = is_array($payload) ? (int) ($payload['order_id'] ?? 0) : (int) $payload;

        if ($order_id <= 0) {
            return;
        }

        $order = wc_get_order($order_id);
        if (! $order instanceof \WC_Order) {
            return;
        }

        $order->update_meta_data('_sce_background_processed_at', gmdate('c'));
        $order->save_meta_data();

        $this->logger->info('background_job_processed', [
            'order_id' => $order_id,
            'order_total' => (float) $order->get_total(),
            'currency' => $order->get_currency(),
        ]);
    }
}

