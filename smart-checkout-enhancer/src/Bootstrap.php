<?php

declare(strict_types=1);

namespace FunnelKit\SCE;

use FunnelKit\SCE\Admin\SettingsPage;
use FunnelKit\SCE\Analytics\AnalyticsRepository;
use FunnelKit\SCE\Analytics\CheckoutAnalyticsTracker;
use FunnelKit\SCE\Background\BackgroundJobProcessor;
use FunnelKit\SCE\Background\BackgroundJobScheduler;
use FunnelKit\SCE\Checkout\CheckoutFeeManager;
use FunnelKit\SCE\Checkout\ConditionEvaluator;
use FunnelKit\SCE\Checkout\CountryMatcher;
use FunnelKit\SCE\Checkout\SubscriptionProductDetector;
use FunnelKit\SCE\Contracts\HookableInterface;
use FunnelKit\SCE\Logging\SecureLogger;
use FunnelKit\SCE\Pricing\PricingEngine;
use FunnelKit\SCE\Pricing\Rules\BulkQuantityDiscountRule;
use FunnelKit\SCE\Pricing\Rules\CategoryMarkupRule;
use FunnelKit\SCE\Settings\SettingsRepository;

final class Bootstrap
{
    private Container $container;

    public function __construct()
    {
        $this->container = new Container();
        $this->register_services();
    }

    public function run(): void
    {
        $services = [
            'settings_page',
            'checkout_fee_manager',
            'pricing_engine',
            'checkout_analytics_tracker',
            'background_job_scheduler',
            'background_job_processor',
        ];

        foreach ($services as $service_id) {
            $service = $this->container->get($service_id);
            if ($service instanceof HookableInterface) {
                $service->register_hooks();
            }
        }
    }

    private function register_services(): void
    {
        $this->container->set('settings_repository', static fn (): SettingsRepository => new SettingsRepository());

        $this->container->set(
            'logger',
            static fn (Container $c): SecureLogger => new SecureLogger($c->get('settings_repository'))
        );

        $this->container->set(
            'country_matcher',
            static fn (Container $c): CountryMatcher => new CountryMatcher($c->get('settings_repository'))
        );

        $this->container->set(
            'subscription_detector',
            static fn (): SubscriptionProductDetector => new SubscriptionProductDetector()
        );

        $this->container->set(
            'condition_evaluator',
            static fn (Container $c): ConditionEvaluator => new ConditionEvaluator(
                $c->get('settings_repository'),
                $c->get('country_matcher'),
                $c->get('subscription_detector')
            )
        );

        $this->container->set(
            'settings_page',
            static fn (Container $c): SettingsPage => new SettingsPage($c->get('settings_repository'))
        );

        $this->container->set(
            'checkout_fee_manager',
            static fn (Container $c): CheckoutFeeManager => new CheckoutFeeManager(
                $c->get('settings_repository'),
                $c->get('condition_evaluator'),
                $c->get('logger')
            )
        );

        $this->container->set(
            'pricing_engine',
            static function (Container $c): PricingEngine {
                $rules = [
                    new CategoryMarkupRule($c->get('settings_repository')),
                    new BulkQuantityDiscountRule($c->get('settings_repository')),
                ];

                $rules = apply_filters('sce_pricing_rules', $rules, $c);

                return new PricingEngine($rules, $c->get('logger'));
            }
        );

        $this->container->set('analytics_repository', static fn (): AnalyticsRepository => new AnalyticsRepository());

        $this->container->set(
            'checkout_analytics_tracker',
            static fn (Container $c): CheckoutAnalyticsTracker => new CheckoutAnalyticsTracker(
                $c->get('analytics_repository'),
                $c->get('condition_evaluator'),
                $c->get('logger')
            )
        );

        $this->container->set(
            'background_job_scheduler',
            static fn (Container $c): BackgroundJobScheduler => new BackgroundJobScheduler($c->get('logger'))
        );

        $this->container->set(
            'background_job_processor',
            static fn (Container $c): BackgroundJobProcessor => new BackgroundJobProcessor($c->get('logger'))
        );
    }
}

