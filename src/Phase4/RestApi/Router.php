<?php

declare(strict_types=1);

namespace Phase4\RestApi;

use Phase4\RestApi\Helpers\ApiResponse;
use Phase4\RestApi\Helpers\Request;

/**
 * シンプルなルーター
 *
 * HTTPメソッドとURIパターンに基づいてリクエストをルーティング
 */
class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    /**
     * GETルートを登録
     *
     * @param string $pattern URIパターン
     * @param callable $handler ハンドラー
     * @return void
     */
    public function get(string $pattern, callable $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * POSTルートを登録
     *
     * @param string $pattern URIパターン
     * @param callable $handler ハンドラー
     * @return void
     */
    public function post(string $pattern, callable $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * PUTルートを登録
     *
     * @param string $pattern URIパターン
     * @param callable $handler ハンドラー
     * @return void
     */
    public function put(string $pattern, callable $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }

    /**
     * DELETEルートを登録
     *
     * @param string $pattern URIパターン
     * @param callable $handler ハンドラー
     * @return void
     */
    public function delete(string $pattern, callable $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * ルートを追加
     *
     * @param string $method HTTPメソッド
     * @param string $pattern URIパターン
     * @param callable $handler ハンドラー
     * @return void
     */
    private function addRoute(string $method, string $pattern, callable $handler): void
    {
        $this->routes[$method][$pattern] = $handler;
    }

    /**
     * リクエストをディスパッチ
     *
     * @return void
     * @throws never
     */
    public function dispatch(): void
    {
        $method = Request::method();
        $uri = Request::uri();

        // ルートが存在しない場合
        if (!isset($this->routes[$method])) {
            ApiResponse::notFound('ルートが見つかりません');
        }

        // パターンマッチング
        foreach ($this->routes[$method] as $pattern => $handler) {
            $params = $this->match($pattern, $uri);
            if ($params !== null) {
                // ハンドラーを実行
                call_user_func($handler, ...$params);
                return;
            }
        }

        // マッチするルートが見つからない
        ApiResponse::notFound('ルートが見つかりません');
    }

    /**
     * URIがパターンにマッチするかチェック
     *
     * @param string $pattern URIパターン（例: /api/v1/users/{id}）
     * @param string $uri リクエストURI
     * @return array<int, mixed>|null パラメータの配列、マッチしない場合はnull
     */
    private function match(string $pattern, string $uri): ?array
    {
        // パターンを正規表現に変換
        // {id} -> ([^/]+) のように置換
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // 最初のマッチ（全体）を除去
            array_shift($matches);
            return $matches;
        }

        return null;
    }
}
