<?php
declare(strict_types=1);

namespace Panje\Infrastructure\Api;

use Panje\Contracts\ApiClientInterface;

final class PythonApiClient implements ApiClientInterface
{
    public function health(): array|\WP_Error
    {
        $settings = \Panje_DB::settings();
        if (empty($settings['api_url']) || empty($settings['api_key'])) {
            return new \WP_Error('panje_api_config', 'Python API تنظیم نشده است.');
        }
        $response = wp_remote_get(rtrim((string) $settings['api_url'], '/') . '/api/v1/health', [
            'timeout' => min(15, (int) $settings['timeout']),
            'redirection' => 0,
            'headers' => [
                'Accept' => 'application/json',
                'X-Panje-API-Key' => (string) $settings['api_key'],
                'Cache-Control' => 'no-store',
            ],
        ]);
        if (is_wp_error($response)) return $response;
        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if ($code < 200 || $code >= 300 || !is_array($body)) {
            return new \WP_Error('panje_api_health', 'بررسی اتصال Python ناموفق بود.', ['status' => $code]);
        }
        return (array) ($body['data'] ?? $body);
    }
}
