<?php

declare(strict_types=1);

/**
 * Phase 3.5: RESTful API 開発 - ルーティング
 *
 * このファイルでは、REST APIにおけるルーティングの仕組みと
 * 簡易ルーターの実装について学習します。
 */

echo "=== ルーティング ===\n\n";

// =====================================
// 1. ルーティングとは
// =====================================

echo "--- 1. ルーティングとは ---\n\n";

/**
 * ルーティング
 *
 * HTTPリクエスト（メソッド + URI）を適切なハンドラー（コントローラー）に
 * 振り分ける仕組み
 *
 * 例：
 * GET  /api/users      -> UserController::index()
 * GET  /api/users/123  -> UserController::show(123)
 * POST /api/users      -> UserController::store()
 * PUT  /api/users/123  -> UserController::update(123)
 * DELETE /api/users/123 -> UserController::destroy(123)
 */

echo "ルーティングの役割：\n";
echo "  - リクエストを解析してパラメータを抽出\n";
echo "  - 適切なハンドラーを実行\n";
echo "  - 404エラーなどのハンドリング\n";

echo "\n";

// =====================================
// 2. シンプルなルーター
// =====================================

echo "--- 2. シンプルなルーター ---\n\n";

/**
 * シンプルなルータークラス
 */
class Router
{
    /**
     * @var array<string, array<string, callable>> ルート定義
     */
    private array $routes = [];

    /**
     * GETルートを登録
     *
     * @param string $path URIパス
     * @param callable $handler ハンドラー
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * POSTルートを登録
     *
     * @param string $path URIパス
     * @param callable $handler ハンドラー
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * PUTルートを登録
     *
     * @param string $path URIパス
     * @param callable $handler ハンドラー
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * DELETEルートを登録
     *
     * @param string $path URIパス
     * @param callable $handler ハンドラー
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * ルートを追加
     *
     * @param string $method HTTPメソッド
     * @param string $path URIパス
     * @param callable $handler ハンドラー
     */
    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    /**
     * リクエストをディスパッチ
     *
     * @param string $method HTTPメソッド
     * @param string $uri リクエストURI
     * @return mixed ハンドラーの戻り値
     */
    public function dispatch(string $method, string $uri): mixed
    {
        // クエリ文字列を除去
        $uri = strtok($uri, '?');

        // メソッドのルートが存在するか
        if (!isset($this->routes[$method])) {
            http_response_code(405);
            return ['error' => 'Method Not Allowed'];
        }

        // 完全一致するルートを探す
        if (isset($this->routes[$method][$uri])) {
            return call_user_func($this->routes[$method][$uri]);
        }

        // 404 Not Found
        http_response_code(404);
        return ['error' => 'Not Found'];
    }
}

// ルーターの使用例
$router = new Router();

$router->get('/api/users', function () {
    return ['message' => 'ユーザー一覧を取得'];
});

$router->post('/api/users', function () {
    return ['message' => '新しいユーザーを作成'];
});

echo "シンプルなルーターの例：\n";
echo "GET /api/users: " . json_encode($router->dispatch('GET', '/api/users'), JSON_UNESCAPED_UNICODE) . "\n";
echo "POST /api/users: " . json_encode($router->dispatch('POST', '/api/users'), JSON_UNESCAPED_UNICODE) . "\n";
echo "GET /api/unknown: " . json_encode($router->dispatch('GET', '/api/unknown'), JSON_UNESCAPED_UNICODE) . "\n";

echo "\n";

// =====================================
// 3. パラメータ付きルーター
// =====================================

echo "--- 3. パラメータ付きルーター ---\n\n";

/**
 * パラメータをサポートするルーター
 *
 * /api/users/{id} のようなパターンマッチングをサポート
 */
class ParameterizedRouter
{
    /**
     * @var array<string, array<int, array{pattern: string, handler: callable, params: array<int, string>}>> ルート定義
     */
    private array $routes = [];

    /**
     * ルートを追加
     *
     * @param string $method HTTPメソッド
     * @param string $path URIパス（パラメータは {name} 形式）
     * @param callable $handler ハンドラー
     */
    public function addRoute(string $method, string $path, callable $handler): void
    {
        // パラメータ名を抽出
        $params = [];
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) use (&$params) {
            $params[] = $matches[1];
            return '([^/]+)';
        }, $path);

        // パターンを正規表現に変換
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'params' => $params,
        ];
    }

    /**
     * GETルートを登録
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * POSTルートを登録
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * PUTルートを登録
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * DELETEルートを登録
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * リクエストをディスパッチ
     *
     * @param string $method HTTPメソッド
     * @param string $uri リクエストURI
     * @return mixed ハンドラーの戻り値
     */
    public function dispatch(string $method, string $uri): mixed
    {
        // クエリ文字列を除去
        $uri = strtok($uri, '?');

        // メソッドのルートが存在するか
        if (!isset($this->routes[$method])) {
            http_response_code(405);
            return ['error' => 'Method Not Allowed'];
        }

        // パターンマッチング
        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // パラメータを抽出
                $params = [];
                foreach ($route['params'] as $index => $name) {
                    $params[$name] = $matches[$index + 1];
                }

                // ハンドラーを実行
                return call_user_func($route['handler'], $params);
            }
        }

        // 404 Not Found
        http_response_code(404);
        return ['error' => 'Not Found'];
    }
}

// パラメータ付きルーターの使用例
$paramRouter = new ParameterizedRouter();

$paramRouter->get('/api/users/{id}', function (array $params) {
    return [
        'message' => 'ユーザー詳細を取得',
        'id' => $params['id'],
    ];
});

$paramRouter->put('/api/users/{id}', function (array $params) {
    return [
        'message' => 'ユーザーを更新',
        'id' => $params['id'],
    ];
});

$paramRouter->delete('/api/users/{id}', function (array $params) {
    return [
        'message' => 'ユーザーを削除',
        'id' => $params['id'],
    ];
});

$paramRouter->get('/api/users/{userId}/posts/{postId}', function (array $params) {
    return [
        'message' => 'ユーザーの投稿を取得',
        'userId' => $params['userId'],
        'postId' => $params['postId'],
    ];
});

echo "パラメータ付きルーターの例：\n";
echo "GET /api/users/123: " . json_encode($paramRouter->dispatch('GET', '/api/users/123'), JSON_UNESCAPED_UNICODE) . "\n";
echo "PUT /api/users/456: " . json_encode($paramRouter->dispatch('PUT', '/api/users/456'), JSON_UNESCAPED_UNICODE) . "\n";
echo "GET /api/users/1/posts/2: " . json_encode($paramRouter->dispatch('GET', '/api/users/1/posts/2'), JSON_UNESCAPED_UNICODE) . "\n";

echo "\n";

// =====================================
// 4. ミドルウェア対応ルーター
// =====================================

echo "--- 4. ミドルウェア対応ルーター ---\n\n";

/**
 * ミドルウェア
 *
 * リクエスト処理の前後に実行される処理
 * - 認証・認可
 * - ロギング
 * - CORS設定
 * - レート制限
 */

/**
 * ミドルウェアインターフェース
 */
interface Middleware
{
    /**
     * ミドルウェア処理
     *
     * @param callable $next 次の処理
     * @return mixed
     */
    public function handle(callable $next): mixed;
}

/**
 * ロギングミドルウェア
 */
class LoggingMiddleware implements Middleware
{
    public function handle(callable $next): mixed
    {
        echo "[ログ] リクエスト開始\n";
        $result = $next();
        echo "[ログ] リクエスト終了\n";
        return $result;
    }
}

/**
 * 認証ミドルウェア
 */
class AuthMiddleware implements Middleware
{
    public function __construct(
        private bool $isAuthenticated = false
    ) {}

    public function handle(callable $next): mixed
    {
        if (!$this->isAuthenticated) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }

        return $next();
    }
}

/**
 * ミドルウェア対応ルーター
 */
class MiddlewareRouter
{
    /**
     * @var array<int, Middleware> グローバルミドルウェア
     */
    private array $middlewares = [];

    /**
     * @var array<string, array<int, array{pattern: string, handler: callable, params: array<int, string>, middlewares: array<int, Middleware>}>>
     */
    private array $routes = [];

    /**
     * グローバルミドルウェアを追加
     *
     * @param Middleware $middleware
     */
    public function addMiddleware(Middleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * ルートを追加
     *
     * @param string $method
     * @param string $path
     * @param callable $handler
     * @param array<int, Middleware> $middlewares
     */
    public function addRoute(
        string $method,
        string $path,
        callable $handler,
        array $middlewares = []
    ): void {
        $params = [];
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) use (&$params) {
            $params[] = $matches[1];
            return '([^/]+)';
        }, $path);

        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'params' => $params,
            'middlewares' => $middlewares,
        ];
    }

    /**
     * GETルートを登録
     *
     * @param string $path
     * @param callable $handler
     * @param array<int, Middleware> $middlewares
     */
    public function get(string $path, callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * ディスパッチ
     *
     * @param string $method
     * @param string $uri
     * @return mixed
     */
    public function dispatch(string $method, string $uri): mixed
    {
        $uri = strtok($uri, '?');

        if (!isset($this->routes[$method])) {
            http_response_code(405);
            return ['error' => 'Method Not Allowed'];
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = [];
                foreach ($route['params'] as $index => $name) {
                    $params[$name] = $matches[$index + 1];
                }

                // ミドルウェアチェーンを構築
                $handler = function () use ($route, $params) {
                    return call_user_func($route['handler'], $params);
                };

                // ルート固有のミドルウェア
                foreach (array_reverse($route['middlewares']) as $middleware) {
                    $handler = fn() => $middleware->handle($handler);
                }

                // グローバルミドルウェア
                foreach (array_reverse($this->middlewares) as $middleware) {
                    $handler = fn() => $middleware->handle($handler);
                }

                return $handler();
            }
        }

        http_response_code(404);
        return ['error' => 'Not Found'];
    }
}

// ミドルウェア対応ルーターの使用例
$middlewareRouter = new MiddlewareRouter();

// グローバルミドルウェア（全ルートに適用）
$middlewareRouter->addMiddleware(new LoggingMiddleware());

// 公開エンドポイント（認証不要）
$middlewareRouter->get('/api/public', function () {
    return ['message' => '公開データ'];
});

// 保護されたエンドポイント（認証必要）
$middlewareRouter->get('/api/private', function () {
    return ['message' => '保護されたデータ'];
}, [new AuthMiddleware(true)]); // 認証済みとしてシミュレート

echo "ミドルウェア対応ルーターの例：\n";
echo "公開エンドポイント:\n";
$result = $middlewareRouter->dispatch('GET', '/api/public');
echo json_encode($result, JSON_UNESCAPED_UNICODE) . "\n\n";

echo "保護されたエンドポイント:\n";
$result = $middlewareRouter->dispatch('GET', '/api/private');
echo json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";

echo "\n";

// =====================================
// 5. RESTfulリソースルーター
// =====================================

echo "--- 5. RESTfulリソースルーター ---\n\n";

/**
 * RESTfulリソースルーター
 *
 * リソースに対する標準的なCRUD操作のルートを一括登録
 */
class ResourceRouter extends ParameterizedRouter
{
    /**
     * RESTfulリソースルートを登録
     *
     * @param string $resource リソース名（複数形）
     * @param string $controller コントローラークラス名
     */
    public function resource(string $resource, string $controller): void
    {
        // 一覧取得
        $this->get("/api/{$resource}", function () use ($controller) {
            return "{$controller}::index()";
        });

        // 詳細取得
        $this->get("/api/{$resource}/{id}", function (array $params) use ($controller) {
            return "{$controller}::show({$params['id']})";
        });

        // 作成
        $this->post("/api/{$resource}", function () use ($controller) {
            return "{$controller}::store()";
        });

        // 更新
        $this->put("/api/{$resource}/{id}", function (array $params) use ($controller) {
            return "{$controller}::update({$params['id']})";
        });

        // 削除
        $this->delete("/api/{$resource}/{id}", function (array $params) use ($controller) {
            return "{$controller}::destroy({$params['id']})";
        });
    }
}

// RESTfulリソースルーターの使用例
$resourceRouter = new ResourceRouter();
$resourceRouter->resource('users', 'UserController');
$resourceRouter->resource('posts', 'PostController');

echo "RESTfulリソースルーターの例：\n";
echo "GET /api/users: " . json_encode($resourceRouter->dispatch('GET', '/api/users'), JSON_UNESCAPED_UNICODE) . "\n";
echo "GET /api/users/123: " . json_encode($resourceRouter->dispatch('GET', '/api/users/123'), JSON_UNESCAPED_UNICODE) . "\n";
echo "POST /api/users: " . json_encode($resourceRouter->dispatch('POST', '/api/users'), JSON_UNESCAPED_UNICODE) . "\n";
echo "PUT /api/users/123: " . json_encode($resourceRouter->dispatch('PUT', '/api/users/123'), JSON_UNESCAPED_UNICODE) . "\n";
echo "DELETE /api/users/123: " . json_encode($resourceRouter->dispatch('DELETE', '/api/users/123'), JSON_UNESCAPED_UNICODE) . "\n";

echo "\n";

// =====================================
// まとめ
// =====================================

echo "=== まとめ ===\n\n";
echo "✅ ルーティングの基本概念\n";
echo "✅ シンプルなルーターの実装\n";
echo "✅ パラメータ付きルーター（パターンマッチング）\n";
echo "✅ ミドルウェアの概念と実装\n";
echo "✅ RESTfulリソースルーター\n";
echo "\n次は、JWT認証について学びます。\n";
