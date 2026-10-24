<?php

namespace SchachWorld;

defined('ABSPATH') || exit;

/**
 * Entry point: registers all hooks of the plugin.
 */
final class Plugin
{
    public const REST_NAMESPACE = 'schach-world/v1';

    public static function boot(): void
    {
        add_action('rest_api_init', [self::class, 'registerRestRoutes']);
    }

    public static function registerRestRoutes(): void
    {
        // Public status endpoint, used as a smoke test that the plugin is loaded.
        register_rest_route(self::REST_NAMESPACE, '/status', [
            'methods' => 'GET',
            'callback' => fn() => ['version' => VERSION],
            'permission_callback' => '__return_true',
        ]);
    }
}
