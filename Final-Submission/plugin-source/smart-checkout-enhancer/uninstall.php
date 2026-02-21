<?php
/**
 * Plugin uninstall file.
 */

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'sce_checkout_analytics';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

delete_option('sce_settings');

