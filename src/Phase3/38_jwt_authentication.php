<?php

declare(strict_types=1);

/**
 * Phase 3.5: RESTful API 開発 - JWT認証
 *
 * このファイルでは、JWT（JSON Web Token）を使った
 * API認証の仕組みについて学習します。
 */

echo "=== JWT認証 ===\n\n";

// =====================================
// 1. JWTとは
// =====================================

echo "--- 1. JWTとは ---\n\n";

/**
 * JWT (JSON Web Token)
 *
 * - コンパクトで安全な方法でJSON形式の情報を伝送
 * - 主にAPI認証で使用される
 * - ステートレス（サーバーでセッション管理不要）
 *
 * 構造（3つの部分をドット区切り）:
 * 1. ヘッダー (Header): トークンのタイプとアルゴリズム
 * 2. ペイロード (Payload): 実際のデータ（クレーム）
 * 3. 署名 (Signature): 改ざん検証用
 *
 * 例: xxxxx.yyyyy.zzzzz
 */

echo "JWTの特徴：\n";
echo "  ✅ ステートレス（サーバー側でセッション管理不要）\n";
echo "  ✅ スケーラブル（複数サーバーで共有可能）\n";
echo "  ✅ クロスドメイン対応\n";
echo "  ✅ 改ざん検証可能\n";
echo "  ⚠️  トークンの無効化が困難（有効期限で対応）\n";
echo "  ⚠️  ペイロードは暗号化されていない（機密情報は含めない）\n";

echo "\n";

// =====================================
// 2. シンプルなJWT実装
// =====================================

echo "--- 2. シンプルなJWT実装 ---\n\n";

/**
 * シンプルなJWTクラス
 *
 * 注意: これは学習用の簡易実装です。
 * 本番環境では firebase/php-jwt などの検証済みライブラリを使用してください。
 */
class SimpleJWT
{
    /**
     * JWTを生成
     *
     * @param array<string, mixed> $payload ペイロード（データ）
     * @param string $secret 秘密鍵
     * @param int $expiration 有効期限（秒）
     * @return string JWT
     */
    public static function encode(
        array $payload,
        string $secret,
        int $expiration = 3600
    ): string {
        // ヘッダー
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256', // HMAC SHA-256
        ];

        // ペイロードに有効期限を追加
        $payload['iat'] = time(); // 発行時刻 (Issued At)
        $payload['exp'] = time() + $expiration; // 有効期限 (Expiration)

        // Base64URL エンコード
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // 署名を生成
        $signature = hash_hmac(
            'sha256',
            "{$headerEncoded}.{$payloadEncoded}",
            $secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        // JWT を組み立て
        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * JWTをデコード
     *
     * @param string $jwt JWT
     * @param string $secret 秘密鍵
     * @return array<string, mixed>|null ペイロード（検証失敗時はnull）
     */
    public static function decode(string $jwt, string $secret): ?array
    {
        // JWT を分割
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // 署名を検証
        $signature = hash_hmac(
            'sha256',
            "{$headerEncoded}.{$payloadEncoded}",
            $secret,
            true
        );
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            return null; // 署名が一致しない
        }

        // ペイロードをデコード
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if ($payload === null) {
            return null;
        }

        // 有効期限をチェック
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // 有効期限切れ
        }

        return $payload;
    }

    /**
     * Base64URL エンコード
     *
     * @param string $data データ
     * @return string エンコード済み文字列
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL デコード
     *
     * @param string $data エンコード済みデータ
     * @return string デコード済み文字列
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// JWTの生成と検証の例
$secret = 'your-secret-key-keep-it-safe';

$payload = [
    'user_id' => 123,
    'username' => 'yamada',
    'role' => 'admin',
];

$jwt = SimpleJWT::encode($payload, $secret, 3600);
echo "生成されたJWT:\n{$jwt}\n\n";

$decoded = SimpleJWT::decode($jwt, $secret);
echo "デコード結果:\n";
print_r($decoded);

echo "\n";

// =====================================
// 3. JWT認証ミドルウェア
// =====================================

echo "--- 3. JWT認証ミドルウェア ---\n\n";

/**
 * JWT認証ミドルウェア
 */
class JWTAuthMiddleware
{
    public function __construct(
        private string $secret
    ) {}

    /**
     * リクエストを処理
     *
     * @param callable $next 次の処理
     * @return mixed
     */
    public function handle(callable $next): mixed
    {
        // Authorizationヘッダーを取得
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        // Bearer トークンを抽出
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'トークンが提供されていません',
            ];
        }

        $jwt = $matches[1];

        // JWTを検証
        $payload = SimpleJWT::decode($jwt, $this->secret);

        if ($payload === null) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'トークンが無効または期限切れです',
            ];
        }

        // ペイロードをリクエストに添付（グローバル変数で簡易実装）
        $GLOBALS['auth_user'] = $payload;

        // 次の処理を実行
        return $next();
    }
}

// JWT認証ミドルウェアの使用例
echo "JWT認証ミドルウェアの使用例：\n";

// トークンを生成
$token = SimpleJWT::encode(['user_id' => 456, 'username' => 'tanaka'], $secret);
echo "トークン: {$token}\n";

// シミュレーション: Authorizationヘッダーを設定
$_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$token}";

$middleware = new JWTAuthMiddleware($secret);
$result = $middleware->handle(function () {
    $user = $GLOBALS['auth_user'];
    return [
        'success' => true,
        'message' => "ようこそ、{$user['username']}さん！",
        'user' => $user,
    ];
});

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

echo "\n";

// =====================================
// 4. リフレッシュトークン
// =====================================

echo "--- 4. リフレッシュトークン ---\n\n";

/**
 * トークンマネージャー
 *
 * アクセストークンとリフレッシュトークンを管理
 * - アクセストークン: 短い有効期限（15分など）
 * - リフレッシュトークン: 長い有効期限（7日など）
 */
class TokenManager
{
    public function __construct(
        private string $secret
    ) {}

    /**
     * トークンペアを生成
     *
     * @param array<string, mixed> $payload ペイロード
     * @return array{access_token: string, refresh_token: string}
     */
    public function generateTokenPair(array $payload): array
    {
        // アクセストークン（15分）
        $accessToken = SimpleJWT::encode($payload, $this->secret, 900);

        // リフレッシュトークン用のペイロード
        $refreshPayload = [
            'user_id' => $payload['user_id'] ?? null,
            'type' => 'refresh',
        ];

        // リフレッシュトークン（7日）
        $refreshToken = SimpleJWT::encode($refreshPayload, $this->secret, 604800);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * リフレッシュトークンから新しいアクセストークンを生成
     *
     * @param string $refreshToken リフレッシュトークン
     * @param array<string, mixed> $userData ユーザーデータ
     * @return string|null 新しいアクセストークン
     */
    public function refresh(string $refreshToken, array $userData): ?string
    {
        // リフレッシュトークンを検証
        $payload = SimpleJWT::decode($refreshToken, $this->secret);

        if ($payload === null || ($payload['type'] ?? '') !== 'refresh') {
            return null;
        }

        // 新しいアクセストークンを生成
        return SimpleJWT::encode($userData, $this->secret, 900);
    }
}

// トークンペアの生成
$tokenManager = new TokenManager($secret);
$tokens = $tokenManager->generateTokenPair([
    'user_id' => 789,
    'username' => 'suzuki',
    'role' => 'user',
]);

echo "トークンペアの生成：\n";
echo "アクセストークン: " . substr($tokens['access_token'], 0, 50) . "...\n";
echo "リフレッシュトークン: " . substr($tokens['refresh_token'], 0, 50) . "...\n";

echo "\n";

// =====================================
// 5. 実践的なAPI認証フロー
// =====================================

echo "--- 5. 実践的なAPI認証フロー ---\n\n";

/**
 * 認証サービス
 */
class AuthService
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    /**
     * ログイン
     *
     * @param string $username ユーザー名
     * @param string $password パスワード
     * @return array<string, mixed> レスポンス
     */
    public function login(string $username, string $password): array
    {
        // データベースからユーザーを取得（ここではシミュレーション）
        $user = $this->findUserByUsername($username);

        if ($user === null) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'ユーザー名またはパスワードが正しくありません',
            ];
        }

        // パスワード検証
        if (!password_verify($password, $user['password'])) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'ユーザー名またはパスワードが正しくありません',
            ];
        }

        // トークンペアを生成
        $tokens = $this->tokenManager->generateTokenPair([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ]);

        return [
            'success' => true,
            'message' => 'ログインしました',
            'data' => [
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                ],
                'tokens' => $tokens,
            ],
        ];
    }

    /**
     * トークンをリフレッシュ
     *
     * @param string $refreshToken リフレッシュトークン
     * @return array<string, mixed> レスポンス
     */
    public function refreshToken(string $refreshToken): array
    {
        // リフレッシュトークンからユーザーIDを取得
        $payload = SimpleJWT::decode($refreshToken, $this->tokenManager->secret);

        if ($payload === null || !isset($payload['user_id'])) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'リフレッシュトークンが無効です',
            ];
        }

        // ユーザー情報を取得
        $user = $this->findUserById($payload['user_id']);

        if ($user === null) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'ユーザーが見つかりません',
            ];
        }

        // 新しいアクセストークンを生成
        $accessToken = $this->tokenManager->refresh($refreshToken, [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ]);

        if ($accessToken === null) {
            http_response_code(401);
            return [
                'success' => false,
                'message' => 'トークンのリフレッシュに失敗しました',
            ];
        }

        return [
            'success' => true,
            'message' => 'トークンをリフレッシュしました',
            'data' => [
                'access_token' => $accessToken,
            ],
        ];
    }

    /**
     * ユーザー名でユーザーを検索（シミュレーション）
     *
     * @param string $username
     * @return array<string, mixed>|null
     */
    private function findUserByUsername(string $username): ?array
    {
        // 実際はデータベースから取得
        $users = [
            [
                'id' => 1,
                'username' => 'yamada',
                'password' => password_hash('password123', PASSWORD_ARGON2ID),
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }

        return null;
    }

    /**
     * ユーザーIDでユーザーを検索（シミュレーション）
     *
     * @param int $userId
     * @return array<string, mixed>|null
     */
    private function findUserById(int $userId): ?array
    {
        $users = [
            [
                'id' => 1,
                'username' => 'yamada',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return $user;
            }
        }

        return null;
    }
}

// 認証フローの例
$authService = new AuthService($tokenManager);

echo "ログイン処理：\n";
$loginResponse = $authService->login('yamada', 'password123');
echo json_encode($loginResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// リフレッシュトークンを使って新しいアクセストークンを取得
if ($loginResponse['success']) {
    $refreshToken = $loginResponse['data']['tokens']['refresh_token'];
    echo "トークンリフレッシュ：\n";
    $refreshResponse = $authService->refreshToken($refreshToken);
    echo json_encode($refreshResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
}

echo "\n";

// =====================================
// まとめ
// =====================================

echo "=== まとめ ===\n\n";
echo "✅ JWTの構造（ヘッダー、ペイロード、署名）\n";
echo "✅ JWTの生成とデコード\n";
echo "✅ JWT認証ミドルウェアの実装\n";
echo "✅ アクセストークンとリフレッシュトークン\n";
echo "✅ 実践的なAPI認証フロー（ログイン、トークンリフレッシュ）\n";
echo "\n";

echo "ベストプラクティス：\n";
echo "  1. 本番環境では検証済みライブラリを使用（firebase/php-jwt など）\n";
echo "  2. 秘密鍵は環境変数で管理し、安全に保管\n";
echo "  3. アクセストークンは短い有効期限（15分程度）\n";
echo "  4. リフレッシュトークンは適度な有効期限（7日程度）\n";
echo "  5. HTTPSを使用してトークンを保護\n";
echo "  6. ペイロードに機密情報を含めない\n";
echo "  7. トークンのブラックリスト管理（ログアウト対応）\n";
echo "\n次は、これらを組み合わせて実践的なREST APIを実装します。\n";
