<?php
/** CLI-only smoke tests against an explicitly named local WordPress install. */
if (PHP_SAPI !== 'cli') { exit(1); }
$root = realpath($argv[1] ?? '');
if (!$root || !is_file($root . '/wp-load.php')) { throw new RuntimeException('WordPress root required.'); }
define('DISABLE_WP_CRON', true);
$_SERVER['HTTP_HOST'] = 'panje-test.local';
$_SERVER['REQUEST_METHOD'] = 'GET';
require $root . '/wp-load.php';
if (!class_exists('Panje_REST')) { throw new RuntimeException('Panje is not active.'); }
function verify(bool $ok, string $label): void {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $label); }
    echo 'PASS: ' . $label . PHP_EOL;
}
function call_route(string $method, string $route, array $payload = []): WP_REST_Response {
    $request = new WP_REST_Request($method, '/panje/v1' . $route);
    if ($payload) {
        $request->set_header('Content-Type', 'application/json');
        $request->set_body(wp_json_encode($payload));
    }
    return rest_do_request($request);
}
wp_set_current_user(0);
verify(call_route('GET', '/pets')->get_status() === 401, 'anonymous pets denied');
$admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
verify((bool)$admins, 'local administrator exists');
wp_set_current_user((int)$admins[0]);
$beforeVersion = Panje_Foods::active_version();
$csv = $root . '/wp-content/uploads/panje-integration-' . wp_generate_uuid4() . '.csv';
file_put_contents($csv, "\xEF\xBB\xBFcategory,foods,kcal/100gr\nTest,Integration Food,100\n");
$version = 'integration-' . wp_generate_uuid4();
$import = Panje_Foods::import_csv($csv, $version, (int)$admins[0]);
verify(!is_wp_error($import), 'UTF-8 BOM CSV imported');
verify(Panje_Foods::active_version() === $version, 'imported version active');
if ($beforeVersion) {
    verify(!is_wp_error(Panje_Foods::rollback((string)$beforeVersion)), 'food version rollback');
} else {
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'panje_food_database', ['version' => $version]);
    $wpdb->delete($wpdb->prefix . 'panje_food_versions', ['version' => $version]);
    verify(true, 'food fixture cleanup');
}
unlink($csv);
foreach (['/pets', '/reports', '/foods', '/bootstrap', '/wallet'] as $route) {
    verify(call_route('GET', $route)->get_status() === 200, 'authenticated ' . $route);
}
verify(call_route('POST', '/pets', ['species' => 'invalid'])->get_status() === 422, 'invalid pet rejected');
$petId = 0;
try {
    $created = call_route('POST', '/pets', [
        'name' => 'Panje integration fixture', 'species' => 'cat', 'breed_name' => 'DSH',
        'breed' => 'domestic', 'sex' => 'male', 'weight' => 4, 'bcs' => 5,
        'activity' => 5, 'status' => 'adult', 'age_years' => 2,
    ]);
    $petId = (int)($created->get_data()['id'] ?? 0);
    verify($petId > 0, 'pet created');
    verify(call_route('GET', '/pets/' . $petId)->get_status() === 200, 'owned pet read');
    verify(call_route('PATCH', '/pets/' . $petId, ['name' => 'Updated fixture'])->get_status() === 200, 'pet update');
} finally {
    if ($petId) {
        verify(call_route('DELETE', '/pets/' . $petId)->get_status() === 200, 'fixture removed');
    }
}
echo 'INTEGRATION COMPLETE' . PHP_EOL;
