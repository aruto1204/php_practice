<?php

declare(strict_types=1);

/**
 * Phase 3.5: RESTful API 開発 - REST API の基礎
 *
 * このファイルでは、RESTful APIの基本概念、HTTPメソッド、
 * ステータスコード、JSONレスポンスについて学習します。
 */

echo "=== REST API の基礎 ===\n\n";

// =====================================
// 1. RESTとは
// =====================================

echo "--- 1. RESTとは ---\n\n";

/**
 * REST (REpresentational State Transfer)
 *
 * - アーキテクチャスタイルの一種
 * - HTTPプロトコルを活用したWebサービス設計の考え方
 * - リソース（データ）をURIで識別し、HTTPメソッドで操作
 *
 * RESTの6原則：
 * 1. クライアント・サーバー分離
 * 2. ステートレス（状態を持たない）
 * 3. キャッシュ可能
 * 4. 統一インターフェース
 * 5. 階層化システム
 * 6. コードオンデマンド（オプション）
 */

// リソースとURIの例
$resourceExamples = [
    'ユーザー一覧' => 'GET /api/users',
    '特定のユーザー' => 'GET /api/users/123',
    'ユーザー作成' => 'POST /api/users',
    'ユーザー更新' => 'PUT /api/users/123',
    'ユーザー削除' => 'DELETE /api/users/123',
];

echo "リソースとURIの例：\n";
foreach ($resourceExamples as $description => $uri) {
    echo "  {$description}: {$uri}\n";
}

echo "\n";

// =====================================
// 2. HTTPメソッド
// =====================================

echo "--- 2. HTTPメソッド ---\n\n";

/**
 * HTTPメソッドの種類と用途
 *
 * GET    - リソースの取得（安全、冪等）
 * POST   - リソースの作成（安全でない、冪等でない）
 * PUT    - リソースの完全更新（安全でない、冪等）
 * PATCH  - リソースの部分更新（安全でない、冪等でない場合もある）
 * DELETE - リソースの削除（安全でない、冪等）
 *
 * 用語：
 * - 安全（Safe）: サーバーの状態を変更しない
 * - 冪等（Idempotent）: 何度実行しても同じ結果になる
 */

/**
 * HTTPメソッドを取得する
 *
 * @return string HTTPメソッド
 */
function getHttpMethod(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * HTTPメソッドに応じた処理を振り分ける例
 */
class HttpMethodHandler
{
    /**
     * リクエストを処理する
     *
     * @param string $method HTTPメソッド
     * @return string 処理内容
     */
    public function handle(string $method): string
    {
        return match ($method) {
            'GET' => $this->handleGet(),
            'POST' => $this->handlePost(),
            'PUT' => $this->handlePut(),
            'PATCH' => $this->handlePatch(),
            'DELETE' => $this->handleDelete(),
            default => $this->handleUnsupported(),
        };
    }

    private function handleGet(): string
    {
        return 'GET: リソースを取得します';
    }

    private function handlePost(): string
    {
        return 'POST: 新しいリソースを作成します';
    }

    private function handlePut(): string
    {
        return 'PUT: リソースを完全に更新します';
    }

    private function handlePatch(): string
    {
        return 'PATCH: リソースを部分的に更新します';
    }

    private function handleDelete(): string
    {
        return 'DELETE: リソースを削除します';
    }

    private function handleUnsupported(): string
    {
        return 'エラー: サポートされていないメソッドです';
    }
}

$handler = new HttpMethodHandler();
echo "GETリクエスト: " . $handler->handle('GET') . "\n";
echo "POSTリクエスト: " . $handler->handle('POST') . "\n";
echo "PUTリクエスト: " . $handler->handle('PUT') . "\n";
echo "DELETEリクエスト: " . $handler->handle('DELETE') . "\n";

echo "\n";

// =====================================
// 3. HTTPステータスコード
// =====================================

echo "--- 3. HTTPステータスコード ---\n\n";

/**
 * HTTPステータスコードの分類
 *
 * 1xx - 情報レスポンス
 * 2xx - 成功レスポンス
 * 3xx - リダイレクト
 * 4xx - クライアントエラー
 * 5xx - サーバーエラー
 *
 * よく使うステータスコード：
 * 200 OK               - リクエスト成功
 * 201 Created          - リソース作成成功
 * 204 No Content       - 成功したがレスポンスボディなし
 * 400 Bad Request      - リクエストが不正
 * 401 Unauthorized     - 認証が必要
 * 403 Forbidden        - アクセス権限なし
 * 404 Not Found        - リソースが見つからない
 * 422 Unprocessable Entity - バリデーションエラー
 * 500 Internal Server Error - サーバー内部エラー
 */

/**
 * ステータスコードマネージャー
 */
class HttpStatus
{
    // 成功
    public const OK = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;

    // クライアントエラー
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const UNPROCESSABLE_ENTITY = 422;

    // サーバーエラー
    public const INTERNAL_SERVER_ERROR = 500;

    /**
     * ステータスコードを設定する（実際のHTTPレスポンス用）
     *
     * @param int $code ステータスコード
     */
    public static function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * ステータスコードの説明を取得
     *
     * @param int $code ステータスコード
     * @return string 説明
     */
    public static function getDescription(int $code): string
    {
        return match ($code) {
            self::OK => '200 OK - リクエスト成功',
            self::CREATED => '201 Created - リソース作成成功',
            self::NO_CONTENT => '204 No Content - 成功（レスポンスボディなし）',
            self::BAD_REQUEST => '400 Bad Request - リクエストが不正',
            self::UNAUTHORIZED => '401 Unauthorized - 認証が必要',
            self::FORBIDDEN => '403 Forbidden - アクセス権限なし',
            self::NOT_FOUND => '404 Not Found - リソースが見つからない',
            self::METHOD_NOT_ALLOWED => '405 Method Not Allowed - メソッドが許可されていない',
            self::UNPROCESSABLE_ENTITY => '422 Unprocessable Entity - バリデーションエラー',
            self::INTERNAL_SERVER_ERROR => '500 Internal Server Error - サーバー内部エラー',
            default => "{$code} Unknown",
        };
    }
}

echo "主要なHTTPステータスコード：\n";
echo HttpStatus::getDescription(HttpStatus::OK) . "\n";
echo HttpStatus::getDescription(HttpStatus::CREATED) . "\n";
echo HttpStatus::getDescription(HttpStatus::BAD_REQUEST) . "\n";
echo HttpStatus::getDescription(HttpStatus::UNAUTHORIZED) . "\n";
echo HttpStatus::getDescription(HttpStatus::NOT_FOUND) . "\n";
echo HttpStatus::getDescription(HttpStatus::UNPROCESSABLE_ENTITY) . "\n";

echo "\n";

// =====================================
// 4. JSONレスポンス
// =====================================

echo "--- 4. JSONレスポンス ---\n\n";

/**
 * JSON形式でレスポンスを返す
 *
 * REST APIでは通常、JSONフォーマットでデータを返す
 * - Content-Type: application/json ヘッダーを設定
 * - json_encode() でPHP配列をJSON文字列に変換
 * - JSON_UNESCAPED_UNICODE でマルチバイト文字をそのまま出力
 * - JSON_PRETTY_PRINT で整形された出力（開発時）
 */

/**
 * JSON レスポンスクラス
 */
class JsonResponse
{
    /**
     * JSONレスポンスを送信する
     *
     * @param mixed $data データ
     * @param int $statusCode ステータスコード
     * @param bool $prettyPrint 整形するか
     */
    public static function send(
        mixed $data,
        int $statusCode = HttpStatus::OK,
        bool $prettyPrint = false
    ): void {
        // ステータスコードを設定
        HttpStatus::setStatusCode($statusCode);

        // Content-Typeヘッダーを設定
        header('Content-Type: application/json; charset=utf-8');

        // JSONエンコードオプション
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }

        // JSON出力
        echo json_encode($data, $options);
    }

    /**
     * 成功レスポンス
     *
     * @param mixed $data データ
     * @param string $message メッセージ
     * @param int $statusCode ステータスコード
     */
    public static function success(
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

        self::send($response, $statusCode, true);
    }

    /**
     * エラーレスポンス
     *
     * @param string $message エラーメッセージ
     * @param int $statusCode ステータスコード
     * @param array<string, mixed>|null $errors エラー詳細
     */
    public static function error(
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

        self::send($response, $statusCode, true);
    }
}

// 成功レスポンスの例
echo "成功レスポンスの例：\n";
$user = [
    'id' => 1,
    'name' => '山田太郎',
    'email' => 'yamada@example.com',
];
echo json_encode([
    'success' => true,
    'message' => 'ユーザーを取得しました',
    'data' => $user,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

echo "\n\n";

// エラーレスポンスの例
echo "エラーレスポンスの例：\n";
echo json_encode([
    'success' => false,
    'message' => 'バリデーションエラー',
    'errors' => [
        'email' => ['メールアドレスの形式が正しくありません'],
        'password' => ['パスワードは8文字以上で入力してください'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

echo "\n\n";

// =====================================
// 5. リクエストボディの取得
// =====================================

echo "--- 5. リクエストボディの取得 ---\n\n";

/**
 * JSONリクエストボディを取得する
 *
 * POST、PUT、PATCHリクエストでJSON形式のデータを受け取る場合
 * - php://input から生のリクエストボディを取得
 * - json_decode() でPHP配列に変換
 */

/**
 * リクエストクラス
 */
class ApiRequest
{
    /**
     * JSONリクエストボディを取得
     *
     * @return array<string, mixed>|null パース済みのデータ
     */
    public static function getJsonBody(): ?array
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
     * Content-Typeがapplication/jsonかチェック
     *
     * @return bool
     */
    public static function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($contentType, 'application/json');
    }

    /**
     * パラメータを取得（GETまたはJSONボディ）
     *
     * @return array<string, mixed>
     */
    public static function getParams(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            return $_GET;
        }

        if (self::isJson()) {
            return self::getJsonBody() ?? [];
        }

        return $_POST;
    }
}

// JSONリクエストボディの例（シミュレーション）
$jsonInput = '{"name": "山田太郎", "email": "yamada@example.com", "age": 30}';
$parsedData = json_decode($jsonInput, true);

echo "JSONリクエストボディの例：\n";
echo "入力: {$jsonInput}\n";
echo "パース後: ";
print_r($parsedData);

echo "\n";

// =====================================
// 6. CORS（Cross-Origin Resource Sharing）
// =====================================

echo "--- 6. CORS（Cross-Origin Resource Sharing） ---\n\n";

/**
 * CORS（オリジン間リソース共有）
 *
 * ブラウザからAPIを呼び出す場合、CORSヘッダーが必要
 * - Access-Control-Allow-Origin: リクエストを許可するオリジン
 * - Access-Control-Allow-Methods: 許可するHTTPメソッド
 * - Access-Control-Allow-Headers: 許可するヘッダー
 */

/**
 * CORSヘッダーを設定するクラス
 */
class CorsHeaders
{
    /**
     * CORSヘッダーを設定
     *
     * @param string $allowOrigin 許可するオリジン（'*' で全て許可）
     * @param array<int, string> $allowMethods 許可するメソッド
     * @param array<int, string> $allowHeaders 許可するヘッダー
     */
    public static function set(
        string $allowOrigin = '*',
        array $allowMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowHeaders = ['Content-Type', 'Authorization']
    ): void {
        header("Access-Control-Allow-Origin: {$allowOrigin}");
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowHeaders));
        header('Access-Control-Max-Age: 86400'); // 24時間キャッシュ
    }

    /**
     * プリフライトリクエスト（OPTIONS）を処理
     */
    public static function handlePreflight(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::set();
            http_response_code(204);
            exit;
        }
    }
}

echo "CORSヘッダーの例：\n";
echo "Access-Control-Allow-Origin: *\n";
echo "Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS\n";
echo "Access-Control-Allow-Headers: Content-Type, Authorization\n";

echo "\n";

// =====================================
// 7. REST API設計のベストプラクティス
// =====================================

echo "--- 7. REST API設計のベストプラクティス ---\n\n";

$bestPractices = [
    '1. 複数形の名詞を使う' => '/api/users（○） vs /api/user（×）',
    '2. 階層構造を表現' => '/api/users/123/posts（ユーザー123の投稿）',
    '3. フィルタリングにクエリパラメータを使う' => '/api/users?status=active&sort=created_at',
    '4. バージョニング' => '/api/v1/users（URLパス）または Accept: application/vnd.api.v1+json（ヘッダー）',
    '5. ページネーション' => '/api/users?page=2&per_page=20',
    '6. 適切なステータスコード' => '成功時は2xx、エラー時は4xx/5xx',
    '7. 一貫性のあるレスポンス形式' => '{"success": bool, "message": string, "data": object}',
    '8. エラーメッセージは明確に' => '{"errors": {"email": ["メールアドレスは必須です"]}}',
];

echo "REST API設計のベストプラクティス：\n";
foreach ($bestPractices as $practice => $example) {
    echo "{$practice}\n  例: {$example}\n\n";
}

// =====================================
// まとめ
// =====================================

echo "=== まとめ ===\n\n";
echo "✅ RESTの基本原則（ステートレス、統一インターフェース）\n";
echo "✅ HTTPメソッド（GET、POST、PUT、DELETE）の使い分け\n";
echo "✅ HTTPステータスコードの適切な使用\n";
echo "✅ JSONレスポンスの生成とエラーハンドリング\n";
echo "✅ リクエストボディ（JSON）の取得\n";
echo "✅ CORSヘッダーの設定\n";
echo "✅ REST API設計のベストプラクティス\n";
echo "\n次は、ルーティングとJWT認証について学びます。\n";
