<?php
declare(strict_types=1);

namespace Panje\Infrastructure\Persistence;

final class ReportRepository
{
    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'panje_reports';
    }

    /** @return array<string,mixed>|null */
    public function findOwned(int $reportId, int $userId): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE id=%d AND user_id=%d LIMIT 1",
            $reportId,
            $userId
        ), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    /** @return list<array<string,mixed>> */
    public function recentForUser(int $userId, int $limit = 50): array
    {
        global $wpdb;
        $limit = max(1, min(200, $limit));
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE user_id=%d ORDER BY id DESC LIMIT %d",
            $userId,
            $limit
        ), ARRAY_A) ?: [];
    }

    /** @return list<array<string,mixed>> */
    public function recentForPet(int $userId, int $petId, int $limit = 50): array
    {
        global $wpdb;
        $limit = max(1, min(200, $limit));
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE user_id=%d AND pet_id=%d ORDER BY id DESC LIMIT %d",
            $userId, $petId, $limit
        ), ARRAY_A) ?: [];
    }

    public function pageForUser(int $userId, int $petId, int $page, int $perPage): array
    {
        global $wpdb;
        $perPage = max(1, min(100, $perPage));
        $offset = (max(1, $page) - 1) * $perPage;
        $sql = "SELECT r.id,r.pet_id,r.request_id,r.report_type,r.pdf_url,r.created_at,p.name pet_name
                FROM {$this->table} r LEFT JOIN {$wpdb->prefix}panje_pets p ON p.id=r.pet_id AND p.user_id=r.user_id
                WHERE r.user_id=%d";
        $args = [$userId];
        if ($petId > 0) { $sql .= ' AND r.pet_id=%d'; $args[] = $petId; }
        $sql .= ' ORDER BY r.id DESC LIMIT %d OFFSET %d';
        $args[] = $perPage;
        $args[] = $offset;
        return $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A) ?: [];
    }

    public function countForUser(int $userId, int $petId = 0): int
    {
        global $wpdb;
        if ($petId > 0) return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id=%d AND pet_id=%d",$userId,$petId));
        return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id=%d",$userId));
    }
}
