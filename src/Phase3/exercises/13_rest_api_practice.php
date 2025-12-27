<?php

declare(strict_types=1);

/**
 * Phase 3.5: 演習課題 - シンプルなREST API
 *
 * この演習では、TODOアプリケーション用のREST APIを実装します。
 * データベースを使用し、CRUD操作を提供します。
 */

// =====================================
// HTTPステータスコード定数
// =====================================

class HttpStatus
{
    public const OK = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;
    public const BAD_REQUEST = 400;
    public const NOT_FOUND = 404;
    public const UNPROCESSABLE_ENTITY = 422;
    public const INTERNAL_SERVER_ERROR = 500;
}

// =====================================
// JSONレスポンスヘルパー
// =====================================

/**
 * JSONレスポンスを送信
 *
 * @param mixed $data データ
 * @param int $statusCode ステータスコード
 */
function jsonResponse(mixed $data, int $statusCode = HttpStatus::OK): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * 成功レスポンス
 *
 * @param mixed $data データ
 * @param string $message メッセージ
 * @param int $statusCode ステータスコード
 */
function successResponse(
    mixed $data = null,
    string $message = 'Success',
    int $statusCode = HttpStatus::OK
): void {
    $response = [
        'success' => true,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    jsonResponse($response, $statusCode);
}

/**
 * エラーレスポンス
 *
 * @param string $message エラーメッセージ
 * @param int $statusCode ステータスコード
 * @param array<string, mixed>|null $errors エラー詳細
 */
function errorResponse(
    string $message,
    int $statusCode = HttpStatus::BAD_REQUEST,
    ?array $errors = null
): void {
    $response = [
        'success' => false,
        'message' => $message,
    ];

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    jsonResponse($response, $statusCode);
}

// =====================================
// リクエストヘルパー
// =====================================

/**
 * JSONリクエストボディを取得
 *
 * @return array<string, mixed>|null
 */
function getJsonBody(): ?array
{
    $json = file_get_contents('php://input');

    if ($json === false || $json === '') {
        return null;
    }

    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $data;
}

/**
 * HTTPメソッドを取得
 *
 * @return string
 */
function getMethod(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * URIパスを取得
 *
 * @return string
 */
function getPath(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return strtok($uri, '?');
}

// =====================================
// データベース接続
// =====================================

/**
 * データベース接続を取得
 *
 * @return PDO
 */
function getDatabase(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'sqlite:' . __DIR__ . '/todos_api.db',
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // テーブルを作成
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS todos (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    description TEXT,
                    completed INTEGER DEFAULT 0,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ');
        } catch (PDOException $e) {
            errorResponse(
                'データベース接続エラー',
                HttpStatus::INTERNAL_SERVER_ERROR
            );
            exit;
        }
    }

    return $pdo;
}

// =====================================
// TODOリポジトリ
// =====================================

/**
 * TODOリポジトリ
 */
class TodoRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * 全てのTODOを取得
     *
     * @param bool|null $completed 完了フィルター
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?bool $completed = null): array
    {
        $sql = 'SELECT * FROM todos';
        $params = [];

        if ($completed !== null) {
            $sql .= ' WHERE completed = :completed';
            $params['completed'] = $completed ? 1 : 0;
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $todos = $stmt->fetchAll();

        // completed を bool に変換
        return array_map(function ($todo) {
            $todo['completed'] = (bool) $todo['completed'];
            return $todo;
        }, $todos);
    }

    /**
     * IDでTODOを取得
     *
     * @param int $id TODO ID
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $todo = $stmt->fetch();

        if ($todo === false) {
            return null;
        }

        $todo['completed'] = (bool) $todo['completed'];
        return $todo;
    }

    /**
     * TODOを作成
     *
     * @param string $title タイトル
     * @param string|null $description 説明
     * @return int 作成されたTODOのID
     */
    public function create(string $title, ?string $description = null): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO todos (title, description)
            VALUES (:title, :description)
        ');

        $stmt->execute([
            'title' => $title,
            'description' => $description,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * TODOを更新
     *
     * @param int $id TODO ID
     * @param string|null $title タイトル
     * @param string|null $description 説明
     * @param bool|null $completed 完了フラグ
     * @return bool 成功したか
     */
    public function update(
        int $id,
        ?string $title = null,
        ?string $description = null,
        ?bool $completed = null
    ): bool {
        $fields = [];
        $params = ['id' => $id];

        if ($title !== null) {
            $fields[] = 'title = :title';
            $params['title'] = $title;
        }

        if ($description !== null) {
            $fields[] = 'description = :description';
            $params['description'] = $description;
        }

        if ($completed !== null) {
            $fields[] = 'completed = :completed';
            $params['completed'] = $completed ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = CURRENT_TIMESTAMP';

        $sql = 'UPDATE todos SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * TODOを削除
     *
     * @param int $id TODO ID
     * @return bool 成功したか
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}

// =====================================
// TODOコントローラー
// =====================================

/**
 * TODOコントローラー
 */
class TodoController
{
    public function __construct(
        private TodoRepository $repository
    ) {}

    /**
     * 一覧取得: GET /api/todos
     */
    public function index(): void
    {
        // クエリパラメータでフィルタリング
        $completed = null;
        if (isset($_GET['completed'])) {
            $completed = filter_var($_GET['completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $todos = $this->repository->findAll($completed);

        successResponse($todos, 'TODOを取得しました');
    }

    /**
     * 詳細取得: GET /api/todos/{id}
     *
     * @param int $id TODO ID
     */
    public function show(int $id): void
    {
        $todo = $this->repository->findById($id);

        if ($todo === null) {
            errorResponse('TODOが見つかりません', HttpStatus::NOT_FOUND);
            return;
        }

        successResponse($todo, 'TODOを取得しました');
    }

    /**
     * 作成: POST /api/todos
     */
    public function store(): void
    {
        $data = getJsonBody();

        if ($data === null) {
            errorResponse('リクエストボディが不正です', HttpStatus::BAD_REQUEST);
            return;
        }

        // バリデーション
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = ['タイトルは必須です'];
        } elseif (mb_strlen($data['title']) > 200) {
            $errors['title'] = ['タイトルは200文字以内で入力してください'];
        }

        if (!empty($errors)) {
            errorResponse(
                'バリデーションエラー',
                HttpStatus::UNPROCESSABLE_ENTITY,
                $errors
            );
            return;
        }

        // TODO作成
        $id = $this->repository->create(
            $data['title'],
            $data['description'] ?? null
        );

        $todo = $this->repository->findById($id);

        successResponse($todo, 'TODOを作成しました', HttpStatus::CREATED);
    }

    /**
     * 更新: PUT /api/todos/{id}
     *
     * @param int $id TODO ID
     */
    public function update(int $id): void
    {
        // TODOが存在するか確認
        if ($this->repository->findById($id) === null) {
            errorResponse('TODOが見つかりません', HttpStatus::NOT_FOUND);
            return;
        }

        $data = getJsonBody();

        if ($data === null) {
            errorResponse('リクエストボディが不正です', HttpStatus::BAD_REQUEST);
            return;
        }

        // バリデーション
        $errors = [];

        if (isset($data['title']) && mb_strlen($data['title']) === 0) {
            $errors['title'] = ['タイトルは必須です'];
        } elseif (isset($data['title']) && mb_strlen($data['title']) > 200) {
            $errors['title'] = ['タイトルは200文字以内で入力してください'];
        }

        if (!empty($errors)) {
            errorResponse(
                'バリデーションエラー',
                HttpStatus::UNPROCESSABLE_ENTITY,
                $errors
            );
            return;
        }

        // TODO更新
        $this->repository->update(
            $id,
            $data['title'] ?? null,
            $data['description'] ?? null,
            isset($data['completed']) ? (bool) $data['completed'] : null
        );

        $todo = $this->repository->findById($id);

        successResponse($todo, 'TODOを更新しました');
    }

    /**
     * 削除: DELETE /api/todos/{id}
     *
     * @param int $id TODO ID
     */
    public function destroy(int $id): void
    {
        $success = $this->repository->delete($id);

        if (!$success) {
            errorResponse('TODOが見つかりません', HttpStatus::NOT_FOUND);
            return;
        }

        successResponse(null, 'TODOを削除しました', HttpStatus::NO_CONTENT);
    }
}

// =====================================
// シンプルなルーター
// =====================================

/**
 * ルーター
 */
class Router
{
    /**
     * @var array<string, array<string, array{handler: callable, pattern: string, params: array<int, string>}>>
     */
    private array $routes = [];

    /**
     * ルートを追加
     *
     * @param string $method HTTPメソッド
     * @param string $path URIパス
     * @param callable $handler ハンドラー
     */
    public function addRoute(string $method, string $path, callable $handler): void
    {
        $params = [];
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) use (&$params) {
            $params[] = $matches[1];
            return '([^/]+)';
        }, $path);

        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'handler' => $handler,
            'pattern' => $pattern,
            'params' => $params,
        ];
    }

    /**
     * GETルート
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * POSTルート
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * PUTルート
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * DELETEルート
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * ディスパッチ
     */
    public function dispatch(): void
    {
        $method = getMethod();
        $path = getPath();

        if (!isset($this->routes[$method])) {
            errorResponse('Method Not Allowed', HttpStatus::NOT_FOUND);
            return;
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = [];
                foreach ($route['params'] as $index => $name) {
                    $params[$name] = $matches[$index + 1];
                }

                // ハンドラーを実行
                if (!empty($params)) {
                    // パラメータがある場合は引数として渡す
                    call_user_func($route['handler'], ...array_values($params));
                } else {
                    call_user_func($route['handler']);
                }
                return;
            }
        }

        errorResponse('Not Found', HttpStatus::NOT_FOUND);
    }
}

// =====================================
// ルート定義とアプリケーション実行
// =====================================

// CORSヘッダー設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// プリフライトリクエスト処理
if (getMethod() === 'OPTIONS') {
    http_response_code(HttpStatus::NO_CONTENT);
    exit;
}

// データベース接続とコントローラー初期化
$pdo = getDatabase();
$repository = new TodoRepository($pdo);
$controller = new TodoController($repository);

// ルーター初期化
$router = new Router();

// ルート定義
$router->get('/api/todos', [$controller, 'index']);
$router->get('/api/todos/{id}', [$controller, 'show']);
$router->post('/api/todos', [$controller, 'store']);
$router->put('/api/todos/{id}', [$controller, 'update']);
$router->delete('/api/todos/{id}', [$controller, 'destroy']);

// ディスパッチ
$router->dispatch();

// =====================================
// 使用例（コマンドラインテスト）
// =====================================

/*
以下のコマンドでAPIをテストできます（PHPビルトインサーバーを起動）:

# サーバー起動
php -S localhost:8000 13_rest_api_practice.php

# 別のターミナルで以下を実行:

# TODO一覧取得
curl http://localhost:8000/api/todos

# TODO作成
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{"title": "買い物に行く", "description": "牛乳を買う"}'

# TODO詳細取得
curl http://localhost:8000/api/todos/1

# TODO更新
curl -X PUT http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"completed": true}'

# TODO削除
curl -X DELETE http://localhost:8000/api/todos/1

# フィルタリング（完了済みのみ）
curl http://localhost:8000/api/todos?completed=true
*/
