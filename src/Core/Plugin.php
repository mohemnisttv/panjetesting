<?php
declare(strict_types=1);

namespace Panje\Core;

final class Plugin
{
    private static ?self $instance = null;
    private Container $container;

    private function __construct() { $this->container = new Container(); }

    public static function boot(): self
    {
        return self::$instance ??= new self();
    }

    public function container(): Container { return $this->container; }
}
