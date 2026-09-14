<?php
declare(strict_types=1);

namespace Panje\Contracts;

interface ApiClientInterface
{
    /** @return array<string, mixed>|\WP_Error */
    public function health(): array|\WP_Error;
}
