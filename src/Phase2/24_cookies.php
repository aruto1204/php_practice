<?php

declare(strict_types=1);

/**
 * Phase 2.6: セッションとクッキー - クッキー編
 *
 * クッキーの基礎から実践的な使い方まで学習します。
 * クッキーはクライアント側でデータを保持する仕組みです。
 */

echo "=== Phase 2.6: クッキー ===\n\n";

// =====================================
// 1. クッキーの基礎
// =====================================

echo "--- 1. クッキーの基礎 ---\n";

/**
 * クッキーとは
 *
 * - クライアント（ブラウザ）側にデータを保存する仕組み
 * - HTTPヘッダーを通じてサーバーとクライアント間でデータをやり取り
 * - 最大4KB程度のデータを保存可能
 * - 有効期限を設定できる
 * - ドメインやパスで適用範囲を制限できる
 *
 * セッションとの違い:
 * - セッション: サーバー側にデータを保存、セッションIDのみクライアントに送信
 * - クッキー: クライアント側にデータを保存、すべてのデータがクライアントに存在
 */

echo "クッキーの基本概念:\n";
echo "  - クッキーはブラウザに保存される\n";
echo "  - 各リクエストでサーバーに送信される\n";
echo "  - JavaScriptからアクセス可能（httponly=false の場合）\n";
echo "  - セキュリティリスクがあるため、機密情報は保存しない\n";

echo "\n";

// =====================================
// 2. クッキーの設定
// =====================================

echo "--- 2. クッキーの設定 ---\n";

/**
 * setcookie() の基本構文
 *
 * setcookie(
 *     string $name,           // クッキー名
 *     string $value = "",     // 値
 *     int $expires = 0,       // 有効期限（Unixタイムスタンプ）
 *     string $path = "",      // パス
 *     string $domain = "",    // ドメイン
 *     bool $secure = false,   // HTTPS接続のみ
 *     bool $httponly = false  // HTTPのみ（JavaScript無効）
 * ): bool
 *
 * PHP 7.3+ では連想配列でも設定可能:
 * setcookie($name, $value, [
 *     'expires' => time() + 3600,
 *     'path' => '/',
 *     'domain' => '',
 *     'secure' => true,
 *     'httponly' => true,
 *     'samesite' => 'Strict'
 * ]);
 */

echo "クッキー設定の例:\n";
echo "\n";

// 注意: この例ではクッキーを実際には設定しません（HTTPヘッダーが既に送信されているため）
// 実際のアプリケーションでは、HTMLやechoの前にsetcookie()を呼び出す必要があります

echo "// 基本的なクッキーの設定（ブラウザを閉じるまで有効）\n";
echo "setcookie('username', 'takahashi');\n\n";

echo "// 有効期限付きクッキー（1時間後に期限切れ）\n";
echo "setcookie('session_token', 'abc123', time() + 3600);\n\n";

echo "// セキュアなクッキー（PHP 7.3+）\n";
echo "setcookie('secure_token', 'xyz789', [\n";
echo "    'expires' => time() + 86400,  // 24時間\n";
echo "    'path' => '/',\n";
echo "    'domain' => '',\n";
echo "    'secure' => true,              // HTTPS接続のみ\n";
echo "    'httponly' => true,            // JavaScriptからアクセス不可\n";
echo "    'samesite' => 'Strict'         // CSRF対策\n";
echo "]);\n\n";

echo "\n";

// =====================================
// 3. クッキーの取得
// =====================================

echo "--- 3. クッキーの取得 ---\n";

/**
 * クッキーは $_COOKIE スーパーグローバル変数で取得
 */

// 例として、仮のクッキーデータを設定
$_COOKIE['username'] = 'takahashi';
$_COOKIE['theme'] = 'dark';
$_COOKIE['language'] = 'ja';

echo "設定されているクッキー:\n";
foreach ($_COOKIE as $name => $value) {
    echo "  {$name}: {$value}\n";
}

echo "\n";

/**
 * クッキーの値を安全に取得
 *
 * @param string $name クッキー名
 * @param mixed $default デフォルト値
 * @return mixed クッキーの値
 */
function getCookie(string $name, mixed $default = null): mixed
{
    return $_COOKIE[$name] ?? $default;
}

/**
 * クッキーが存在するかチェック
 *
 * @param string $name クッキー名
 * @return bool 存在する場合true
 */
function hasCookie(string $name): bool
{
    return isset($_COOKIE[$name]);
}

// 使用例
echo "ユーザー名: " . getCookie('username', 'ゲスト') . "\n";
echo "テーマ: " . getCookie('theme', 'light') . "\n";
echo "言語: " . getCookie('language', 'en') . "\n";
echo "ポイント: " . getCookie('points', 0) . "（デフォルト値）\n";

if (hasCookie('username')) {
    echo "ユーザー名のクッキーが存在します\n";
}

echo "\n";

// =====================================
// 4. クッキーの削除
// =====================================

echo "--- 4. クッキーの削除 ---\n";

/**
 * クッキーを削除するには、過去の時刻を設定
 */

echo "クッキーの削除例:\n";
echo "setcookie('username', '', time() - 3600);\n";
echo "または\n";
echo "setcookie('username', '', [\n";
echo "    'expires' => time() - 3600,\n";
echo "    'path' => '/',\n";
echo "]);\n";

echo "\n";

/**
 * クッキーを削除
 *
 * @param string $name クッキー名
 * @param string $path パス
 * @param string $domain ドメイン
 * @return void
 */
function deleteCookie(string $name, string $path = '/', string $domain = ''): void
{
    // 注意: 実際のアプリケーションでは、HTTPヘッダー送信前に呼び出す
    // setcookie($name, '', [
    //     'expires' => time() - 3600,
    //     'path' => $path,
    //     'domain' => $domain,
    // ]);

    // スクリプト内でも $_COOKIE から削除
    unset($_COOKIE[$name]);
}

echo "deleteCookie() 関数を定義しました\n";

echo "\n";

// =====================================
// 5. セキュアなクッキー管理
// =====================================

echo "--- 5. セキュアなクッキー管理 ---\n";

/**
 * セキュアなクッキーを設定
 *
 * @param string $name クッキー名
 * @param string $value 値
 * @param int $lifetime 有効期限（秒）
 * @return void
 */
function setSecureCookie(
    string $name,
    string $value,
    int $lifetime = 0
): void {
    $options = [
        'expires' => $lifetime > 0 ? time() + $lifetime : 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,      // HTTPS接続のみ（本番環境）
        'httponly' => true,    // JavaScriptからアクセス不可
        'samesite' => 'Strict', // CSRF対策
    ];

    // 開発環境ではsecureをfalseに
    if (getenv('APP_ENV') === 'development') {
        $options['secure'] = false;
    }

    // 実際のアプリケーションでは、HTTPヘッダー送信前に呼び出す
    // setcookie($name, $value, $options);
}

echo "setSecureCookie() 関数を定義しました\n";
echo "\n";

echo "セキュリティのポイント:\n";
echo "  1. httponly=true: XSS攻撃対策\n";
echo "  2. secure=true: HTTPS接続のみ（本番環境）\n";
echo "  3. samesite='Strict': CSRF攻撃対策\n";
echo "  4. 適切な有効期限の設定\n";
echo "  5. 機密情報は暗号化または保存しない\n";

echo "\n";

// =====================================
// 6. クッキーの暗号化
// =====================================

echo "--- 6. クッキーの暗号化 ---\n";

/**
 * クッキー暗号化クラス
 */
class EncryptedCookie
{
    private const CIPHER = 'aes-256-gcm';

    public function __construct(
        private string $key
    ) {
        // キーの長さをチェック
        if (strlen($key) !== 32) {
            throw new InvalidArgumentException('暗号化キーは32バイトである必要があります');
        }
    }

    /**
     * 値を暗号化
     *
     * @param string $value 暗号化する値
     * @return string 暗号化された値（Base64エンコード済み）
     */
    public function encrypt(string $value): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $value,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new RuntimeException('暗号化に失敗しました');
        }

        // IV + Tag + 暗号化データを結合してBase64エンコード
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * 値を復号化
     *
     * @param string $encrypted 暗号化された値（Base64エンコード済み）
     * @return string 復号化された値
     */
    public function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted, true);

        if ($data === false) {
            throw new RuntimeException('Base64デコードに失敗しました');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $tagLength = 16;

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, $tagLength);
        $ciphertext = substr($data, $ivLength + $tagLength);

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new RuntimeException('復号化に失敗しました');
        }

        return $decrypted;
    }

    /**
     * 暗号化されたクッキーを設定
     *
     * @param string $name クッキー名
     * @param string $value 値
     * @param int $lifetime 有効期限（秒）
     * @return void
     */
    public function set(string $name, string $value, int $lifetime = 0): void
    {
        $encrypted = $this->encrypt($value);

        // 実際のアプリケーションでは、HTTPヘッダー送信前に呼び出す
        // setcookie($name, $encrypted, [
        //     'expires' => $lifetime > 0 ? time() + $lifetime : 0,
        //     'path' => '/',
        //     'httponly' => true,
        //     'secure' => true,
        //     'samesite' => 'Strict',
        // ]);

        // デモ用に $_COOKIE に設定
        $_COOKIE[$name] = $encrypted;
    }

    /**
     * 暗号化されたクッキーを取得
     *
     * @param string $name クッキー名
     * @param mixed $default デフォルト値
     * @return mixed 復号化された値
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        try {
            return $this->decrypt($_COOKIE[$name]);
        } catch (RuntimeException $e) {
            return $default;
        }
    }
}

// 使用例
$key = random_bytes(32); // 実際のアプリケーションでは環境変数などから取得
$encryptedCookie = new EncryptedCookie($key);

// 暗号化してクッキーに保存
$encryptedCookie->set('user_data', 'sensitive_information', 3600);

// 復号化して取得
$userData = $encryptedCookie->get('user_data');
echo "復号化されたデータ: {$userData}\n";

echo "\n";

// =====================================
// 7. クッキーの署名
// =====================================

echo "--- 7. クッキーの署名 ---\n";

/**
 * クッキー署名クラス
 *
 * クッキーの改ざんを検出
 */
class SignedCookie
{
    public function __construct(
        private string $secret
    ) {
    }

    /**
     * 値に署名を追加
     *
     * @param string $value 値
     * @return string 署名付きの値
     */
    private function sign(string $value): string
    {
        $signature = hash_hmac('sha256', $value, $this->secret);
        return base64_encode($value) . '.' . $signature;
    }

    /**
     * 署名を検証
     *
     * @param string $signedValue 署名付きの値
     * @return string|null 検証成功時は元の値、失敗時はnull
     */
    private function verify(string $signedValue): ?string
    {
        $parts = explode('.', $signedValue);

        if (count($parts) !== 2) {
            return null;
        }

        [$encodedValue, $signature] = $parts;
        $value = base64_decode($encodedValue, true);

        if ($value === false) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', $value, $this->secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        return $value;
    }

    /**
     * 署名付きクッキーを設定
     *
     * @param string $name クッキー名
     * @param string $value 値
     * @param int $lifetime 有効期限（秒）
     * @return void
     */
    public function set(string $name, string $value, int $lifetime = 0): void
    {
        $signed = $this->sign($value);

        // デモ用に $_COOKIE に設定
        $_COOKIE[$name] = $signed;
    }

    /**
     * 署名付きクッキーを取得
     *
     * @param string $name クッキー名
     * @param mixed $default デフォルト値
     * @return mixed 検証成功時は値、失敗時はデフォルト値
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        $value = $this->verify($_COOKIE[$name]);
        return $value !== null ? $value : $default;
    }
}

// 使用例
$signedCookie = new SignedCookie('secret_key_12345');

// 署名付きクッキーを設定
$signedCookie->set('preferences', 'theme:dark;lang:ja', 86400);

// 検証して取得
$preferences = $signedCookie->get('preferences');
echo "検証されたデータ: {$preferences}\n";

// 改ざんされたクッキー
$_COOKIE['preferences'] = str_replace('dark', 'light', $_COOKIE['preferences']);
$tamperedPreferences = $signedCookie->get('preferences', 'デフォルト値');
echo "改ざん検出後のデータ: {$tamperedPreferences}\n";

echo "\n";

// =====================================
// 8. クッキーの用途別実装
// =====================================

echo "--- 8. クッキーの用途別実装 ---\n";

/**
 * Remember Me（ログイン保持）機能
 */
class RememberMeCookie
{
    private const COOKIE_NAME = 'remember_me';
    private const LIFETIME = 2592000; // 30日

    public function __construct(
        private string $secret
    ) {
    }

    /**
     * Remember Meトークンを生成
     *
     * @param int $userId ユーザーID
     * @return string トークン
     */
    public function generateToken(int $userId): string
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        // データベースに保存するべき情報
        // - selector
        // - hash($validator)
        // - user_id
        // - expires_at

        $token = $selector . ':' . $validator;
        return base64_encode($token);
    }

    /**
     * Remember Meクッキーを設定
     *
     * @param string $token トークン
     * @return void
     */
    public function set(string $token): void
    {
        // 実際のアプリケーションでは、HTTPヘッダー送信前に呼び出す
        // setcookie(self::COOKIE_NAME, $token, [
        //     'expires' => time() + self::LIFETIME,
        //     'path' => '/',
        //     'httponly' => true,
        //     'secure' => true,
        //     'samesite' => 'Strict',
        // ]);

        $_COOKIE[self::COOKIE_NAME] = $token;
    }

    /**
     * Remember Meトークンを取得
     *
     * @return string|null トークン
     */
    public function get(): ?string
    {
        return $_COOKIE[self::COOKIE_NAME] ?? null;
    }

    /**
     * Remember Meクッキーを削除
     *
     * @return void
     */
    public function delete(): void
    {
        unset($_COOKIE[self::COOKIE_NAME]);
    }
}

echo "RememberMeCookie クラスを定義しました\n";

/**
 * ユーザー設定クッキー
 */
class PreferencesCookie
{
    private const COOKIE_NAME = 'user_preferences';

    /**
     * 設定を保存
     *
     * @param array $preferences 設定
     * @return void
     */
    public function save(array $preferences): void
    {
        $json = json_encode($preferences, JSON_UNESCAPED_UNICODE);

        // デモ用に $_COOKIE に設定
        $_COOKIE[self::COOKIE_NAME] = $json;
    }

    /**
     * 設定を取得
     *
     * @return array 設定
     */
    public function load(): array
    {
        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return [];
        }

        $preferences = json_decode($_COOKIE[self::COOKIE_NAME], true);
        return is_array($preferences) ? $preferences : [];
    }

    /**
     * 特定の設定を取得
     *
     * @param string $key キー
     * @param mixed $default デフォルト値
     * @return mixed 値
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $preferences = $this->load();
        return $preferences[$key] ?? $default;
    }

    /**
     * 特定の設定を更新
     *
     * @param string $key キー
     * @param mixed $value 値
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $preferences = $this->load();
        $preferences[$key] = $value;
        $this->save($preferences);
    }
}

// 使用例
$prefs = new PreferencesCookie();
$prefs->set('theme', 'dark');
$prefs->set('language', 'ja');
$prefs->set('notifications', true);

echo "保存された設定:\n";
echo "  テーマ: " . $prefs->get('theme') . "\n";
echo "  言語: " . $prefs->get('language') . "\n";
echo "  通知: " . ($prefs->get('notifications') ? '有効' : '無効') . "\n";

echo "\n";

// =====================================
// 9. クッキーとセッションの使い分け
// =====================================

echo "--- 9. クッキーとセッションの使い分け ---\n";

echo "
【クッキーを使うべき場合】

1. ユーザー設定・プリファレンス
   - テーマ、言語、表示設定など
   - ログイン前でも保持したい情報

2. トラッキング・分析
   - アクセス解析
   - A/Bテスト
   - 広告トラッキング

3. Remember Me機能
   - ログイン状態の長期保持
   - 次回訪問時の自動ログイン

4. カート情報（非ログイン時）
   - ログイン前のショッピングカート
   - 一時的な選択内容

【セッションを使うべき場合】

1. 認証情報
   - ログイン状態
   - ユーザーID
   - 権限情報

2. 一時的な状態管理
   - フォームの入力途中データ
   - ウィザード形式の入力
   - フラッシュメッセージ

3. セキュリティが重要なデータ
   - 個人情報
   - 支払い情報
   - 機密データ

4. 大きなデータ
   - クッキーは4KB制限
   - セッションはサーバーメモリの許す限り

【併用する場合】

- セッションIDをクッキーに保存
- Remember Meトークンをクッキーに保存し、認証後はセッションで管理
- 設定情報はクッキー、状態管理はセッション
";

echo "\n";

// =====================================
// 10. ベストプラクティス
// =====================================

echo "--- 10. クッキーのベストプラクティス ---\n";

echo "
【セキュリティのベストプラクティス】

1. 機密情報の保護
   - パスワードや個人情報をクッキーに保存しない
   - 必要な場合は暗号化
   - 署名を使って改ざんを検出

2. クッキー属性の設定
   - httponly=true でXSS対策
   - secure=true でHTTPS接続のみ（本番環境）
   - samesite='Strict' または 'Lax' でCSRF対策
   - 適切な path と domain の設定

3. 有効期限の管理
   - 必要最小限の期間を設定
   - セッション管理には短い期限
   - Remember Me機能には長めの期限

4. 検証とサニタイズ
   - クッキーの値は常に検証
   - 予期しない値を拒否
   - エスケープ処理を忘れずに

【パフォーマンスのベストプラクティス】

1. クッキーサイズの最小化
   - 必要最小限のデータのみ保存
   - 大きなデータはサーバー側に保存

2. クッキー数の削減
   - 関連する設定は1つのクッキーにまとめる
   - 不要なクッキーは削除

3. 適切なドメイン・パス設定
   - 必要なページでのみ送信されるよう設定
   - サブドメイン間での共有を最小限に
";

echo "=== Phase 2.6: クッキー - 完了 ===\n";
