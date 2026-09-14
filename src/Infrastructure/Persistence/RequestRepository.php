<?php
declare(strict_types=1);

namespace Panje\Infrastructure\Persistence;

final class RequestRepository
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'panje_requests';
    }

    /** @return array<string,mixed>|null */
    public function findByUuid(string $uuid, int $userId): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE request_uuid=%s AND user_id=%d LIMIT 1",
            $uuid,
            $userId
        ), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function updateStatus(string $uuid, string $from, string $to): bool
    {
        global $wpdb;
        return (bool) $wpdb->query($wpdb->prepare(
            "UPDATE {$this->table} SET status=%s WHERE request_uuid=%s AND status=%s",
            $to,
            $uuid,
            $from
        ));
    }
}
