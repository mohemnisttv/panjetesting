<?php
/**
 * Plugin Name: Panje
 * Description: Smart dog/cat nutrition analysis and diet generation with WooCommerce wallet and FastAPI engine.
 * Version: 3.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.3
 * Text Domain: panje
 */
if (!defined('ABSPATH')) exit;

define('PANJE_VERSION','3.1.3');
define('PANJE_DB_VERSION','3.1.3');
define('PANJE_FILE',__FILE__);
define('PANJE_DIR',plugin_dir_path(__FILE__));
define('PANJE_URL',plugin_dir_url(__FILE__));
$autoload = PANJE_DIR . 'vendor/autoload.php';
if (is_readable($autoload)) require_once $autoload;
else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'Panje\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
        $root = is_dir(PANJE_DIR . 'src') ? PANJE_DIR . 'src/' : PANJE_DIR . '../src/';
        $source = $root . $relative . '.php';
        if (is_readable($source)) require_once $source;
    });
}
if (class_exists('Panje\\Core\\Plugin')) {
    $panje_core = \Panje\Core\Plugin::boot();
    $panje_core->container()->singleton(
        'python_api',
        static fn() => new \Panje\Infrastructure\Api\PythonApiClient()
    );
    $panje_core->container()->singleton(
        'request_repository',
        static fn() => new \Panje\Infrastructure\Persistence\RequestRepository()
    );
    $panje_core->container()->singleton(
        'report_repository',
        static fn() => new \Panje\Infrastructure\Persistence\ReportRepository()
    );
}
foreach (['class-db.php','class-log.php','class-services.php','class-wallet.php','class-api.php','class-foods.php','class-pdf.php','class-rest.php','class-admin.php','class-shortcodes.php'] as $file) {
    require_once PANJE_DIR.'includes/'.$file;
}

// WordPress expects activation hooks to be silent. Some hosts emit dbDelta/PHP
// warnings during schema creation; keep those out of the activation response
// while allowing them to be recorded by the server error log.
register_activation_hook(__FILE__, static function (): void {
    ob_start();
    try { Panje_DB::install(); }
    finally { ob_end_clean(); }
});

add_action('plugins_loaded', static function () {
    Panje_DB::maybe_upgrade();
    Panje_Admin::boot();
    Panje_REST::boot();
    Panje_Wallet::boot();
    Panje_Shortcodes::boot();
});
