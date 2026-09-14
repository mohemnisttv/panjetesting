<?php
declare(strict_types=1);

namespace Panje\Application;

use Panje\Infrastructure\Persistence\RequestRepository;

final class RequestService
{
    public function __construct(private RequestRepository $requests) {}

    /** @return array<string,mixed>|\WP_Error */
    public function getOwned(string $uuid, int $userId): array|\WP_Error
    {
        $request = $this->requests->findByUuid($uuid, $userId);
        return $request ?? new \WP_Error('panje_request_not_found', 'درخواست پیدا نشد.', ['status' => 404]);
    }
}
