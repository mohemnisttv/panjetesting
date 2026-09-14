<?php
declare(strict_types=1);

namespace Panje\Admin;

/** Read-only, paginated user directory with batched accounting summaries. */
final class UserDirectory
{
    public function search(string $search = '', int $page = 1, int $perPage = 25): array|\WP_Error
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('panje_forbidden', 'دسترسی مجاز نیست.', ['status' => 403]);
        }

        global $wpdb;
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $search = sanitize_text_field($search);
        $args = [
            'number' => $perPage,
            'paged' => $page,
            'orderby' => 'ID',
            'order' => 'DESC',
            'count_total' => true,
            'fields' => ['ID', 'display_name', 'user_email'],
        ];
        if ($search !== '') {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'display_name', 'user_email'];
        }

        $query = new \WP_User_Query($args);
        $total = (int) $query->get_total();
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
            $args['paged'] = $page;
            $query = new \WP_User_Query($args);
        }
        $users = $query->get_results();
        $items = [];
        foreach ($users as $user) {
            $id = (int) $user->ID;
            $items[$id] = [
                'id' => $id,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'balance' => 0,
                'pets' => 0,
                'spent' => 0,
            ];
        }

        if ($items) {
            $ids = array_keys($items);
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            // Only server-owned table/column expressions are interpolated here.
            $summaries = [
                'balance' => "SELECT user_id, balance value FROM {$wpdb->prefix}panje_wallet WHERE user_id IN ($placeholders)",
                'pets' => "SELECT user_id, COUNT(*) value FROM {$wpdb->prefix}panje_pets WHERE user_id IN ($placeholders) GROUP BY user_id",
                'spent' => "SELECT user_id, SUM(amount) value FROM {$wpdb->prefix}panje_transactions WHERE user_id IN ($placeholders) AND type='debit' GROUP BY user_id",
            ];
            foreach ($summaries as $field => $sql) {
                $rows = $wpdb->get_results($wpdb->prepare($sql, ...$ids), ARRAY_A);
                if ($wpdb->last_error) {
                    return new \WP_Error('panje_user_summary', 'دریافت اطلاعات کاربران ناموفق بود. دوباره تلاش کنید.', ['status' => 500]);
                }
                foreach ($rows ?: [] as $row) {
                    $items[(int) $row['user_id']][$field] = (int) $row['value'];
                }
            }
        }

        return [
            'items' => array_values($items), 'page' => $page, 'per_page' => $perPage,
            'total' => $total, 'pages' => $pages, 'search' => $search,
        ];
    }
}
