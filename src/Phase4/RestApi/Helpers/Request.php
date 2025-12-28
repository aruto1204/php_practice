<?php

declare(strict_types=1);

namespace Phase4\RestApi\Helpers;

/**
 * リクエストヘルパー
 *
 * HTTPリクエストの処理を担当
 */
class Request
{
    /**
     * リクエストメソッドを取得
     *
     * @return string
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * リクエストURIを取得（クエリ文字列を除く）
     *
     * @return string
     */
    public static function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        return $uri;
    }

    /**
     * JSONリクエストボディを取得
     *
     * @return array<string, mixed>
     */
    public static function json(): array
    {
        $body = file_get_contents('php://input');
        if ($body === false || $body === '') {
            return [];
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }

    /**
     * クエリパラメータを取得
     *
     * @param string|null $key キー（null の場合は全体を返す）
     * @param mixed $default デフォルト値
     * @return mixed
     */
    public static function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * リクエストヘッダーを取得
     *
     * @param string $name ヘッダー名
     * @param string|null $default デフォルト値
     * @return string|null
     */
    public static function header(string $name, ?string $default = null): ?string
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$name] ?? $default;
    }

    /**
     * Authorizationヘッダーからベアラートークンを取得
     *
     * @return string|null
     */
    public static function bearerToken(): ?string
    {
        $header = self::header('Authorization');
        if ($header === null) {
            return null;
        }

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    /**
     * クライアントIPアドレスを取得
     *
     * @return string
     */
    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * User-Agentを取得
     *
     * @return string
     */
    public static function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * JSONリクエストかどうかをチェック
     *
     * @return bool
     */
    public static function isJson(): bool
    {
        $contentType = self::header('Content-Type');
        return $contentType !== null && str_contains($contentType, 'application/json');
    }
}
