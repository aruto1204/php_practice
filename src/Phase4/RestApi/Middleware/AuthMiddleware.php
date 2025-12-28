<?php

declare(strict_types=1);

namespace Phase4\RestApi\Middleware;

use Phase4\RestApi\Helpers\ApiResponse;
use Phase4\RestApi\Helpers\Request;
use Phase4\RestApi\Services\JwtService;

/**
 * 認証ミドルウェア
 *
 * JWTトークンによる認証を行う
 */
class AuthMiddleware
{
    /**
     * 認証チェック
     *
     * @param JwtService $jwtService JWTサービス
     * @param bool $requireAdmin 管理者権限が必要かどうか
     * @return array<string, mixed> デコードされたトークンペイロード
     * @throws never 認証失敗時
     */
    public static function handle(JwtService $jwtService, bool $requireAdmin = false): array
    {
        $token = Request::bearerToken();

        if ($token === null) {
            ApiResponse::unauthorized('認証トークンが提供されていません');
        }

        try {
            $payload = $jwtService->decode($token);

            // トークンタイプをチェック（アクセストークンのみ許可）
            if (($payload['type'] ?? null) !== 'access') {
                ApiResponse::unauthorized('無効なトークンタイプです');
            }

            // 管理者権限のチェック
            if ($requireAdmin && !($payload['is_admin'] ?? false)) {
                ApiResponse::forbidden('この操作には管理者権限が必要です');
            }

            return $payload;
        } catch (\InvalidArgumentException $e) {
            ApiResponse::unauthorized($e->getMessage());
        }
    }
}
