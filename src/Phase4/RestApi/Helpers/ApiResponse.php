<?php

declare(strict_types=1);

namespace Phase4\RestApi\Helpers;

/**
 * APIレスポンスヘルパー
 *
 * 統一されたJSON API レスポンスを生成
 */
class ApiResponse
{
    /**
     * 成功レスポンスを返す
     *
     * @param mixed $data レスポンスデータ
     * @param string $message メッセージ
     * @param int $statusCode HTTPステータスコード
     * @param array<string, mixed> $meta 追加のメタ情報
     * @return never
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = [],
    ): never {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'meta' => array_merge([
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'api_version' => 'v1',
            ], $meta),
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * エラーレスポンスを返す
     *
     * @param string $code エラーコード
     * @param string $message エラーメッセージ
     * @param int $statusCode HTTPステータスコード
     * @param array<string, mixed> $details 詳細情報
     * @return never
     */
    public static function error(
        string $code,
        string $message,
        int $statusCode = 400,
        array $details = [],
    ): never {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if (!empty($details)) {
            $error['details'] = $details;
        }

        $response = [
            'success' => false,
            'error' => $error,
            'meta' => [
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'api_version' => 'v1',
            ],
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * ページネーション付き成功レスポンスを返す
     *
     * @param array<mixed> $data データ配列
     * @param int $currentPage 現在のページ
     * @param int $perPage ページあたりの件数
     * @param int $total 総件数
     * @param string $message メッセージ
     * @return never
     */
    public static function paginated(
        array $data,
        int $currentPage,
        int $perPage,
        int $total,
        string $message = 'Success',
    ): never {
        $totalPages = (int) ceil($total / $perPage);

        self::success($data, $message, 200, [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ]);
    }

    /**
     * バリデーションエラーレスポンスを返す
     *
     * @param array<string, array<string>> $errors バリデーションエラー
     * @return never
     */
    public static function validationError(array $errors): never
    {
        self::error('VALIDATION_ERROR', 'Validation failed', 422, $errors);
    }

    /**
     * 認証エラーレスポンスを返す
     *
     * @param string $message エラーメッセージ
     * @return never
     */
    public static function unauthorized(string $message = 'Unauthorized'): never
    {
        self::error('AUTHENTICATION_FAILED', $message, 401);
    }

    /**
     * 認可エラーレスポンスを返す
     *
     * @param string $message エラーメッセージ
     * @return never
     */
    public static function forbidden(string $message = 'Forbidden'): never
    {
        self::error('AUTHORIZATION_FAILED', $message, 403);
    }

    /**
     * Not Foundエラーレスポンスを返す
     *
     * @param string $message エラーメッセージ
     * @return never
     */
    public static function notFound(string $message = 'Resource not found'): never
    {
        self::error('RESOURCE_NOT_FOUND', $message, 404);
    }

    /**
     * サーバーエラーレスポンスを返す
     *
     * @param string $message エラーメッセージ
     * @return never
     */
    public static function serverError(string $message = 'Internal server error'): never
    {
        self::error('INTERNAL_SERVER_ERROR', $message, 500);
    }

    /**
     * レート制限エラーレスポンスを返す
     *
     * @param int $retryAfter 再試行可能になるまでの秒数
     * @return never
     */
    public static function rateLimitExceeded(int $retryAfter): never
    {
        header("Retry-After: $retryAfter");
        self::error(
            'RATE_LIMIT_EXCEEDED',
            'Too many requests. Please try again later.',
            429,
            ['retry_after' => $retryAfter]
        );
    }
}
