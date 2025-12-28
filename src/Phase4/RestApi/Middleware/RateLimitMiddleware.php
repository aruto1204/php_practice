<?php

declare(strict_types=1);

namespace Phase4\RestApi\Middleware;

use Phase4\RestApi\Helpers\ApiResponse;
use Phase4\RestApi\Helpers\Request;

/**
 * レート制限ミドルウェア
 *
 * IPアドレスベースのレート制限を実装
 */
class RateLimitMiddleware
{
    // レート制限の設定
    private const MAX_REQUESTS = 100; // 1時間あたりの最大リクエスト数
    private const WINDOW_SECONDS = 3600; // 1時間

    // レート制限データを保存（本番環境ではRedisなどを使用すべき）
    private static array $requests = [];

    /**
     * レート制限チェック
     *
     * @return void
     * @throws never レート制限超過時
     */
    public static function handle(): void
    {
        $ip = Request::ip();
        $now = time();

        // 古いリクエスト記録をクリーンアップ
        self::cleanup($now);

        // 現在のリクエスト数を取得
        $requests = self::getRequests($ip, $now);
        $requestCount = count($requests);

        // レート制限ヘッダーを設定
        header('X-RateLimit-Limit: ' . self::MAX_REQUESTS);
        header('X-RateLimit-Remaining: ' . max(0, self::MAX_REQUESTS - $requestCount));

        if (!empty($requests)) {
            $resetTime = min($requests) + self::WINDOW_SECONDS;
            header('X-RateLimit-Reset: ' . $resetTime);
        }

        // レート制限チェック
        if ($requestCount >= self::MAX_REQUESTS) {
            $retryAfter = min($requests) + self::WINDOW_SECONDS - $now;
            ApiResponse::rateLimitExceeded(max(0, $retryAfter));
        }

        // リクエストを記録
        self::recordRequest($ip, $now);
    }

    /**
     * IPアドレスのリクエスト履歴を取得
     *
     * @param string $ip IPアドレス
     * @param int $now 現在時刻
     * @return int[] タイムスタンプの配列
     */
    private static function getRequests(string $ip, int $now): array
    {
        if (!isset(self::$requests[$ip])) {
            return [];
        }

        // ウィンドウ内のリクエストのみを返す
        return array_filter(
            self::$requests[$ip],
            fn(int $timestamp) => $timestamp > $now - self::WINDOW_SECONDS
        );
    }

    /**
     * リクエストを記録
     *
     * @param string $ip IPアドレス
     * @param int $timestamp タイムスタンプ
     * @return void
     */
    private static function recordRequest(string $ip, int $timestamp): void
    {
        if (!isset(self::$requests[$ip])) {
            self::$requests[$ip] = [];
        }

        self::$requests[$ip][] = $timestamp;
    }

    /**
     * 古いリクエスト記録をクリーンアップ
     *
     * @param int $now 現在時刻
     * @return void
     */
    private static function cleanup(int $now): void
    {
        foreach (self::$requests as $ip => $timestamps) {
            self::$requests[$ip] = array_filter(
                $timestamps,
                fn(int $timestamp) => $timestamp > $now - self::WINDOW_SECONDS
            );

            // 空になったIPアドレスは削除
            if (empty(self::$requests[$ip])) {
                unset(self::$requests[$ip]);
            }
        }
    }

    /**
     * レート制限をリセット（テスト用）
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$requests = [];
    }
}
