<?php

declare(strict_types=1);

namespace FunnelKit\SCE\Logging;

use FunnelKit\SCE\Settings\SettingsRepository;

final class SecureLogger
{
    private SettingsRepository $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        if ((string) $this->settings->get('logging_enabled') !== 'yes') {
            return;
        }

        if (! function_exists('wc_get_logger')) {
            return;
        }

        $logger = wc_get_logger();
        $logger->info($message, [
            'source' => 'smart-checkout-enhancer',
            'context' => $this->sanitize($context),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function sanitize(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            $normalized_key = strtolower((string) $key);

            if ($this->is_sensitive_key($normalized_key)) {
                $sanitized[$key] = is_scalar($value) ? hash('sha256', (string) $value) : '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
                continue;
            }

            if (is_object($value)) {
                $sanitized[$key] = sprintf('[object:%s]', get_class($value));
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function is_sensitive_key(string $key): bool
    {
        $needles = ['email', 'phone', 'address', 'name', 'postcode', 'zip', 'ip'];
        foreach ($needles as $needle) {
            if (strpos($key, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}

