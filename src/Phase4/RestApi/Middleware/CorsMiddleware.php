<?php

declare(strict_types=1);

namespace Phase4\RestApi\Middleware;

use Phase4\RestApi\Helpers\Request;

/**
 * CORSミドルウェア
 *
 * Cross-Origin Resource Sharing (CORS) ヘッダーを設定
 */
class CorsMiddleware
{
    /**
     * CORSヘッダーを設定
     *
     * @return void
     */
    public static function handle(): void
    {
        // すべてのオリジンを許可（本番環境では特定のオリジンに制限すべき）
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24時間

        // プリフライトリクエスト（OPTIONS）の場合は、ここで終了
        if (Request::method() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
