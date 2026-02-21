<?php
/**
 * Plugin Name: Smart Checkout Enhancer
 * Description: Conditional checkout fee, dynamic pricing, checkout analytics, and async order processing for WooCommerce.
 * Version: 1.0.0
 * Author: Assignment Submission
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 * Text Domain: smart-checkout-enhancer
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('SCE_VERSION', '1.0.0');
define('SCE_PLUGIN_FILE', __FILE__);
define('SCE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCE_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once SCE_PLUGIN_DIR . 'src/Autoloader.php';

\FunnelKit\SCE\Autoloader::register();

register_activation_hook(SCE_PLUGIN_FILE, [\FunnelKit\SCE\Infrastructure\Activation::class, 'activate']);
register_deactivation_hook(SCE_PLUGIN_FILE, [\FunnelKit\SCE\Infrastructure\Deactivation::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    if (! class_exists('WooCommerce')) {
        return;
    }

    $bootstrap = new \FunnelKit\SCE\Bootstrap();
    $bootstrap->run();
});

