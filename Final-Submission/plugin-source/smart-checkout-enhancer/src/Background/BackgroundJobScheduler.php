<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Background;

use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Logging\SecureLogger;

final class BackgroundJobScheduler implements HookableInterface
{
    private SecureLogger $logger;

    public function __construct(SecureLogger $logger)
    {
        $this->logger = $logger;
    }

    public function register_hooks(): void
    {
        add_action('woocommerce_order_status_completed', [$this, 'schedule_job'], 20, 1);
    }

    public function schedule_job(int $order_id): void
    {
        if ($order_id <= 0) {
            return;
        }

        if (function_exists('as_next_scheduled_action') && function_exists('as_enqueue_async_action')) {
            if (! as_next_scheduled_action('sce_process_completed_order', ['order_id' => $order_id], 'sce')) {
                as_enqueue_async_action('sce_process_completed_order', ['order_id' => $order_id], 'sce');
            }
        } elseif (! wp_next_scheduled('sce_process_completed_order', [$order_id])) {
            wp_schedule_single_event(time() + MINUTE_IN_SECONDS, 'sce_process_completed_order', [$order_id]);
        }

        $this->logger->info('background_job_scheduled', ['order_id' => $order_id]);
    }
}

