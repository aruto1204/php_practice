<?php

declare(strict_types=1);

/**
 * Phase 3.5: 演習課題 - API認証の実装
 *
 * この演習では、JWT認証を使った保護されたREST APIを実装します。
 * - ユーザー登録
 * - ログイン（JWTトークン発行）
 * - 認証が必要なエンドポイント
 * - トークンリフレッシュ
 */

// =====================================
// HTTPステータスコード
// =====================================

class HttpStatus
{
    public const OK = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const NOT_FOUND = 404;
    public const UNPROCESSABLE_ENTITY = 422;
    public const INTERNAL_SERVER_ERROR = 500;
}

// =====================================
// JWT実装（簡易版）
// =====================================

/**
 * シンプルなJWTクラス
 */
class JWT
{
    /**
     * JWTを生成
     */
    public static function encode(
        array $payload,
        string $secret,
        int $expiration = 3600
    ): string {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "{$headerEncoded}.{$payloadEncoded}",
            $secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * JWTをデコード
     */
    public static function decode(string $jwt, string $secret): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $signature = hash_hmac(
            'sha256',
            "{$headerEncoded}.{$payloadEncoded}",
            $secret,
            true
        );
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if ($payload === null) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// =====================================
// ユーティリティ関数
// =====================================

function jsonResponse(mixed $data, int $statusCode = HttpStatus::OK): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function successResponse(
    mixed $data = null,
    string $message = 'Success',
    int $statusCode = HttpStatus::OK
): void {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response, $statusCode);
}

function errorResponse(
    string $message,
    int $statusCode = HttpStatus::BAD_REQUEST,
    ?array $errors = null
): void {
    $response = ['success' => false, 'message' => $message];
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    jsonResponse($response, $statusCode);
}

function getJsonBody(): ?array
{
    $json = file_get_contents('php://input');
    if ($json === false || $json === '') {
        return null;
    }
    $data = json_decode($json, true);
    return json_last_error() === JSON_ERROR_NONE ? $data : null;
}

function getMethod(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function getPath(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return strtok($uri, '?');
}

function getBearerToken(): ?string
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}

// =====================================
// データベース
// =====================================

function getDatabase(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'sqlite:' . __DIR__ . '/auth_api.db',
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // usersテーブル
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // notesテーブル（認証が必要なリソース）
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS notes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    content TEXT,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ');
        } catch (PDOException $e) {
            errorResponse(
                'データベース接続エラー',
                HttpStatus::INTERNAL_SERVER_ERROR
            );
        }
    }

    return $pdo;
}

// =====================================
// ユーザーリポジトリ
// =====================================

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $username, string $email, string $password): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
        ');

        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_ARGON2ID),
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}

// =====================================
// ノートリポジトリ
// =====================================

class NoteRepository
{
    public function __construct(private PDO $pdo) {}

    public function findAllByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM notes
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM notes
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $note = $stmt->fetch();
        return $note ?: null;
    }

    public function create(int $userId, string $title, ?string $content = null): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO notes (user_id, title, content)
            VALUES (:user_id, :title, :content)
        ');

        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM notes
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}

// =====================================
// 認証コントローラー
// =====================================

class AuthController
{
    private const JWT_SECRET = 'your-super-secret-jwt-key-change-in-production';
    private const ACCESS_TOKEN_EXPIRATION = 900; // 15分
    private const REFRESH_TOKEN_EXPIRATION = 604800; // 7日

    public function __construct(private UserRepository $userRepository) {}

    /**
     * ユーザー登録: POST /api/auth/register
     */
    public function register(): void
    {
        $data = getJsonBody();
        if ($data === null) {
            errorResponse('リクエストボディが不正です', HttpStatus::BAD_REQUEST);
        }

        // バリデーション
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = ['ユーザー名は必須です'];
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = ['ユーザー名は3文字以上で入力してください'];
        }

        if (empty($data['email'])) {
            $errors['email'] = ['メールアドレスは必須です'];
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['メールアドレスの形式が正しくありません'];
        }

        if (empty($data['password'])) {
            $errors['password'] = ['パスワードは必須です'];
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = ['パスワードは8文字以上で入力してください'];
        }

        if (!empty($errors)) {
            errorResponse('バリデーションエラー', HttpStatus::UNPROCESSABLE_ENTITY, $errors);
        }

        // 重複チェック
        if ($this->userRepository->findByUsername($data['username']) !== null) {
            errorResponse(
                'バリデーションエラー',
                HttpStatus::UNPROCESSABLE_ENTITY,
                ['username' => ['このユーザー名は既に使用されています']]
            );
        }

        if ($this->userRepository->findByEmail($data['email']) !== null) {
            errorResponse(
                'バリデーションエラー',
                HttpStatus::UNPROCESSABLE_ENTITY,
                ['email' => ['このメールアドレスは既に使用されています']]
            );
        }

        // ユーザー作成
        $userId = $this->userRepository->create(
            $data['username'],
            $data['email'],
            $data['password']
        );

        $user = $this->userRepository->findById($userId);

        successResponse(
            [
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                ],
            ],
            'ユーザー登録が完了しました',
            HttpStatus::CREATED
        );
    }

    /**
     * ログイン: POST /api/auth/login
     */
    public function login(): void
    {
        $data = getJsonBody();
        if ($data === null) {
            errorResponse('リクエストボディが不正です', HttpStatus::BAD_REQUEST);
        }

        if (empty($data['username']) || empty($data['password'])) {
            errorResponse(
                'ユーザー名とパスワードを入力してください',
                HttpStatus::UNAUTHORIZED
            );
        }

        $user = $this->userRepository->findByUsername($data['username']);

        if ($user === null || !password_verify($data['password'], $user['password'])) {
            errorResponse(
                'ユーザー名またはパスワードが正しくありません',
                HttpStatus::UNAUTHORIZED
            );
        }

        // トークン生成
        $accessToken = JWT::encode(
            [
                'user_id' => $user['id'],
                'username' => $user['username'],
            ],
            self::JWT_SECRET,
            self::ACCESS_TOKEN_EXPIRATION
        );

        $refreshToken = JWT::encode(
            [
                'user_id' => $user['id'],
                'type' => 'refresh',
            ],
            self::JWT_SECRET,
            self::REFRESH_TOKEN_EXPIRATION
        );

        successResponse([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
            ],
            'tokens' => [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => self::ACCESS_TOKEN_EXPIRATION,
            ],
        ], 'ログインしました');
    }

    /**
     * トークンリフレッシュ: POST /api/auth/refresh
     */
    public function refresh(): void
    {
        $data = getJsonBody();
        if ($data === null || empty($data['refresh_token'])) {
            errorResponse('リフレッシュトークンが必要です', HttpStatus::BAD_REQUEST);
        }

        $payload = JWT::decode($data['refresh_token'], self::JWT_SECRET);

        if ($payload === null || ($payload['type'] ?? '') !== 'refresh') {
            errorResponse('リフレッシュトークンが無効です', HttpStatus::UNAUTHORIZED);
        }

        $user = $this->userRepository->findById($payload['user_id']);

        if ($user === null) {
            errorResponse('ユーザーが見つかりません', HttpStatus::UNAUTHORIZED);
        }

        // 新しいアクセストークンを生成
        $accessToken = JWT::encode(
            [
                'user_id' => $user['id'],
                'username' => $user['username'],
            ],
            self::JWT_SECRET,
            self::ACCESS_TOKEN_EXPIRATION
        );

        successResponse([
            'access_token' => $accessToken,
            'expires_in' => self::ACCESS_TOKEN_EXPIRATION,
        ], 'トークンをリフレッシュしました');
    }

    /**
     * 現在のユーザー情報取得: GET /api/auth/me
     */
    public function me(): void
    {
        $token = getBearerToken();
        if ($token === null) {
            errorResponse('トークンが提供されていません', HttpStatus::UNAUTHORIZED);
        }

        $payload = JWT::decode($token, self::JWT_SECRET);
        if ($payload === null) {
            errorResponse('トークンが無効です', HttpStatus::UNAUTHORIZED);
        }

        $user = $this->userRepository->findById($payload['user_id']);
        if ($user === null) {
            errorResponse('ユーザーが見つかりません', HttpStatus::UNAUTHORIZED);
        }

        successResponse([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
            ],
        ], 'ユーザー情報を取得しました');
    }
}

// =====================================
// ノートコントローラー（認証が必要）
// =====================================

class NoteController
{
    private const JWT_SECRET = 'your-super-secret-jwt-key-change-in-production';

    public function __construct(private NoteRepository $noteRepository) {}

    /**
     * 認証チェック
     */
    private function authenticate(): ?array
    {
        $token = getBearerToken();
        if ($token === null) {
            errorResponse('トークンが提供されていません', HttpStatus::UNAUTHORIZED);
        }

        $payload = JWT::decode($token, self::JWT_SECRET);
        if ($payload === null) {
            errorResponse('トークンが無効または期限切れです', HttpStatus::UNAUTHORIZED);
        }

        return $payload;
    }

    /**
     * ノート一覧取得: GET /api/notes
     */
    public function index(): void
    {
        $user = $this->authenticate();
        $notes = $this->noteRepository->findAllByUserId($user['user_id']);

        successResponse($notes, 'ノートを取得しました');
    }

    /**
     * ノート作成: POST /api/notes
     */
    public function store(): void
    {
        $user = $this->authenticate();
        $data = getJsonBody();

        if ($data === null) {
            errorResponse('リクエストボディが不正です', HttpStatus::BAD_REQUEST);
        }

        // バリデーション
        $errors = [];
        if (empty($data['title'])) {
            $errors['title'] = ['タイトルは必須です'];
        }

        if (!empty($errors)) {
            errorResponse('バリデーションエラー', HttpStatus::UNPROCESSABLE_ENTITY, $errors);
        }

        // ノート作成
        $noteId = $this->noteRepository->create(
            $user['user_id'],
            $data['title'],
            $data['content'] ?? null
        );

        $note = $this->noteRepository->findById($noteId, $user['user_id']);

        successResponse($note, 'ノートを作成しました', HttpStatus::CREATED);
    }

    /**
     * ノート削除: DELETE /api/notes/{id}
     */
    public function destroy(int $id): void
    {
        $user = $this->authenticate();
        $success = $this->noteRepository->delete($id, $user['user_id']);

        if (!$success) {
            errorResponse('ノートが見つかりません', HttpStatus::NOT_FOUND);
        }

        successResponse(null, 'ノートを削除しました', HttpStatus::NO_CONTENT);
    }
}

// =====================================
// ルーター
// =====================================

class Router
{
    private array $routes = [];

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

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function dispatch(): void
    {
        $method = getMethod();
        $path = getPath();

        if (!isset($this->routes[$method])) {
            errorResponse('Method Not Allowed', HttpStatus::NOT_FOUND);
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = [];
                foreach ($route['params'] as $index => $name) {
                    $params[$name] = (int) $matches[$index + 1];
                }

                if (!empty($params)) {
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
// アプリケーション起動
// =====================================

// CORSヘッダー
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (getMethod() === 'OPTIONS') {
    http_response_code(HttpStatus::NO_CONTENT);
    exit;
}

// データベース接続とリポジトリ初期化
$pdo = getDatabase();
$userRepository = new UserRepository($pdo);
$noteRepository = new NoteRepository($pdo);

// コントローラー初期化
$authController = new AuthController($userRepository);
$noteController = new NoteController($noteRepository);

// ルーター初期化
$router = new Router();

// 認証関連のルート
$router->post('/api/auth/register', [$authController, 'register']);
$router->post('/api/auth/login', [$authController, 'login']);
$router->post('/api/auth/refresh', [$authController, 'refresh']);
$router->get('/api/auth/me', [$authController, 'me']);

// 保護されたリソース（認証が必要）
$router->get('/api/notes', [$noteController, 'index']);
$router->post('/api/notes', [$noteController, 'store']);
$router->delete('/api/notes/{id}', [$noteController, 'destroy']);

// ディスパッチ
$router->dispatch();

// =====================================
// 使用例
// =====================================

/*
# サーバー起動
php -S localhost:8000 14_api_authentication_practice.php

# 別のターミナルで以下を実行:

# 1. ユーザー登録
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username": "yamada", "email": "yamada@example.com", "password": "password123"}'

# 2. ログイン
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "yamada", "password": "password123"}'

# レスポンスからaccess_tokenとrefresh_tokenを取得

# 3. 認証が必要なエンドポイント（トークンを使用）
TOKEN="<上記で取得したaccess_token>"

# 現在のユーザー情報取得
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"

# ノート作成
curl -X POST http://localhost:8000/api/notes \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title": "メモ", "content": "重要な内容"}'

# ノート一覧取得
curl http://localhost:8000/api/notes \
  -H "Authorization: Bearer $TOKEN"

# 4. トークンリフレッシュ
REFRESH_TOKEN="<上記で取得したrefresh_token>"
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}"
*/
