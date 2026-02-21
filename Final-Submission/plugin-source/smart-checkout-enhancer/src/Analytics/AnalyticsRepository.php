<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Analytics;

final class AnalyticsRepository
{
    public static function create_table(): void
    {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'sce_checkout_analytics';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            country VARCHAR(8) NOT NULL DEFAULT '',
            cart_total DECIMAL(20,6) NOT NULL DEFAULT 0,
            has_subscription TINYINT(1) NOT NULL DEFAULT 0,
            fee_applied TINYINT(1) NOT NULL DEFAULT 0,
            pricing_adjustments LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY order_id (order_id),
            KEY country (country),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function insert(array $payload): bool
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'sce_checkout_analytics';
        $result     = $wpdb->insert(
            $table_name,
            [
                'order_id' => (int) $payload['order_id'],
                'user_id' => (int) $payload['user_id'],
                'country' => sanitize_text_field((string) $payload['country']),
                'cart_total' => (float) $payload['cart_total'],
                'has_subscription' => ! empty($payload['has_subscription']) ? 1 : 0,
                'fee_applied' => ! empty($payload['fee_applied']) ? 1 : 0,
                'pricing_adjustments' => wp_json_encode($payload['pricing_adjustments'] ?? []),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['%d', '%d', '%s', '%f', '%d', '%d', '%s', '%s']
        );

        return (bool) $result;
    }
}

