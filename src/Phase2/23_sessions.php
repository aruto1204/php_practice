<?php

declare(strict_types=1);

/**
 * Phase 2.6: セッションとクッキー - セッション編
 *
 * セッションの基礎から実践的な使い方まで学習します。
 * セッションはサーバー側でユーザーの状態を保持する仕組みです。
 */

echo "=== Phase 2.6: セッション ===\n\n";

// =====================================
// 1. セッションの基礎
// =====================================

echo "--- 1. セッションの基礎 ---\n";

/**
 * セッションとは
 *
 * - HTTPはステートレスなプロトコル（状態を持たない）
 * - セッションを使うことで、複数のリクエスト間で状態を保持できる
 * - サーバー側にデータを保存し、クライアントにはセッションIDのみを渡す
 * - セッションIDはクッキーまたはURLパラメータで管理される
 */

// セッションの開始
// session_start() は必ずHTTPヘッダー送信前に呼び出す
if (!session_id()) {
    session_start();
}

echo "セッションID: " . session_id() . "\n";
echo "セッション名: " . session_name() . "\n";

// セッションにデータを保存
$_SESSION['username'] = 'takahashi';
$_SESSION['user_id'] = 123;
$_SESSION['login_time'] = time();

echo "セッションに保存されたデータ:\n";
print_r($_SESSION);

echo "\n";

// =====================================
// 2. セッション設定
// =====================================

echo "--- 2. セッション設定 ---\n";

/**
 * session_start() の前に設定を変更できる
 */

// セッション設定の取得
$currentSettings = [
    'save_path' => session_save_path(),
    'name' => session_name(),
    'cookie_lifetime' => session_get_cookie_params()['lifetime'],
    'cookie_path' => session_get_cookie_params()['path'],
    'cookie_domain' => session_get_cookie_params()['domain'],
    'cookie_secure' => session_get_cookie_params()['secure'],
    'cookie_httponly' => session_get_cookie_params()['httponly'],
    'cookie_samesite' => session_get_cookie_params()['samesite'] ?? 'なし',
];

echo "現在のセッション設定:\n";
foreach ($currentSettings as $key => $value) {
    $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
    echo "  {$key}: {$displayValue}\n";
}

echo "\n";

// =====================================
// 3. セキュアなセッション設定
// =====================================

echo "--- 3. セキュアなセッション設定 ---\n";

/**
 * セキュアなセッション設定関数
 *
 * @return void
 */
function startSecureSession(): void
{
    // 既にセッションが開始されている場合は何もしない
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // セッションクッキーのパラメータを設定
    session_set_cookie_params([
        'lifetime' => 0,              // ブラウザを閉じるまで有効
        'path' => '/',                // サイト全体で有効
        'domain' => '',               // 現在のドメイン
        'secure' => true,             // HTTPS接続のみ（本番環境）
        'httponly' => true,           // JavaScriptからアクセス不可（XSS対策）
        'samesite' => 'Strict',       // CSRF対策
    ]);

    // セッション名を変更（デフォルトのPHPSESSIDは推測されやすい）
    session_name('SECURE_SESSION_ID');

    // セッションIDの生成強度を上げる
    ini_set('session.entropy_length', '32');
    ini_set('session.entropy_file', '/dev/urandom');

    // セッションハイジャック対策
    ini_set('session.use_strict_mode', '1');     // 未初期化のセッションIDを拒否
    ini_set('session.use_cookies', '1');         // クッキーを使用
    ini_set('session.use_only_cookies', '1');    // URLパラメータのセッションIDを拒否
    ini_set('session.use_trans_sid', '0');       // 透過的セッションIDを無効化

    session_start();

    // セッションフィクセーション攻撃対策
    // ログイン時など重要な操作でセッションIDを再生成
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }

    // セッションハイジャック対策：IPアドレスとUser-Agentのチェック
    if (!isset($_SESSION['user_ip'])) {
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    } else {
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($_SESSION['user_ip'] !== $currentIp || $_SESSION['user_agent'] !== $currentAgent) {
            // セッションハイジャックの可能性
            session_unset();
            session_destroy();
            throw new RuntimeException('セッションが無効です');
        }
    }
}

echo "セキュアなセッション設定の例を関数として定義しました\n";
echo "startSecureSession() を呼び出すことで、セキュアなセッションを開始できます\n";

echo "\n";

// =====================================
// 4. セッションデータの操作
// =====================================

echo "--- 4. セッションデータの操作 ---\n";

/**
 * セッションデータを安全に取得
 *
 * @param string $key キー
 * @param mixed $default デフォルト値
 * @return mixed 値
 */
function getSession(string $key, mixed $default = null): mixed
{
    return $_SESSION[$key] ?? $default;
}

/**
 * セッションデータを設定
 *
 * @param string $key キー
 * @param mixed $value 値
 * @return void
 */
function setSession(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}

/**
 * セッションデータを削除
 *
 * @param string $key キー
 * @return void
 */
function unsetSession(string $key): void
{
    unset($_SESSION[$key]);
}

/**
 * セッションに値が存在するかチェック
 *
 * @param string $key キー
 * @return bool 存在する場合true
 */
function hasSession(string $key): bool
{
    return isset($_SESSION[$key]);
}

// 使用例
setSession('cart_items', ['item1', 'item2', 'item3']);
setSession('cart_total', 5000);

echo "カート商品数: " . count(getSession('cart_items', [])) . "\n";
echo "カート合計: " . getSession('cart_total', 0) . "円\n";
echo "ポイント: " . getSession('points', 0) . "pt（デフォルト値）\n";

if (hasSession('cart_items')) {
    echo "カートに商品があります\n";
}

// 特定のキーを削除
unsetSession('cart_total');
echo "cart_totalを削除しました\n";

echo "\n";

// =====================================
// 5. セッションの有効期限管理
// =====================================

echo "--- 5. セッションの有効期限管理 ---\n";

/**
 * セッションのタイムアウトをチェック
 *
 * @param int $maxLifetime 最大有効時間（秒）
 * @return bool タイムアウトしていない場合true
 */
function checkSessionTimeout(int $maxLifetime = 1800): bool
{
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }

    $elapsed = time() - $_SESSION['last_activity'];

    if ($elapsed > $maxLifetime) {
        // タイムアウト
        session_unset();
        session_destroy();
        return false;
    }

    // アクティビティ時刻を更新
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * セッションの絶対的な有効期限をチェック
 *
 * @param int $maxLifetime 最大有効時間（秒）
 * @return bool 有効期限内の場合true
 */
function checkSessionAbsoluteTimeout(int $maxLifetime = 7200): bool
{
    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
        return true;
    }

    $elapsed = time() - $_SESSION['created_at'];

    if ($elapsed > $maxLifetime) {
        // 絶対的なタイムアウト
        session_unset();
        session_destroy();
        return false;
    }

    return true;
}

// 使用例
$_SESSION['last_activity'] = time() - 1000; // 1000秒前
$_SESSION['created_at'] = time() - 3000;    // 3000秒前

if (checkSessionTimeout(1800)) {
    echo "セッションは有効です（非アクティブタイムアウト: 30分）\n";
} else {
    echo "セッションがタイムアウトしました\n";
}

if (checkSessionAbsoluteTimeout(7200)) {
    echo "セッションは有効です（絶対タイムアウト: 2時間）\n";
} else {
    echo "セッションの絶対的な有効期限が切れました\n";
}

echo "\n";

// =====================================
// 6. セッションIDの再生成
// =====================================

echo "--- 6. セッションIDの再生成 ---\n";

/**
 * セッションIDを再生成（セッションフィクセーション対策）
 *
 * ログイン成功時、権限変更時などに実行
 *
 * @return void
 */
function regenerateSessionId(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        // 古いセッションIDを削除し、新しいIDを生成
        session_regenerate_id(true);
    }
}

$oldSessionId = session_id();
echo "旧セッションID: {$oldSessionId}\n";

regenerateSessionId();

$newSessionId = session_id();
echo "新セッションID: {$newSessionId}\n";
echo "セッションIDが再生成されました\n";

echo "\n";

// =====================================
// 7. フラッシュメッセージ
// =====================================

echo "--- 7. フラッシュメッセージ ---\n";

/**
 * フラッシュメッセージクラス
 *
 * 一度だけ表示するメッセージを管理
 */
class FlashMessage
{
    private const SESSION_KEY = '_flash_messages';

    /**
     * フラッシュメッセージを設定
     *
     * @param string $type メッセージタイプ（success, error, warning, info）
     * @param string $message メッセージ
     * @return void
     */
    public static function set(string $type, string $message): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $_SESSION[self::SESSION_KEY][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * すべてのフラッシュメッセージを取得して削除
     *
     * @return array フラッシュメッセージの配列
     */
    public static function get(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $messages;
    }

    /**
     * 特定のタイプのフラッシュメッセージを取得
     *
     * @param string $type メッセージタイプ
     * @return array フラッシュメッセージの配列
     */
    public static function getByType(string $type): array
    {
        $allMessages = self::get();
        return array_filter($allMessages, fn($msg) => $msg['type'] === $type);
    }

    /**
     * フラッシュメッセージが存在するかチェック
     *
     * @return bool 存在する場合true
     */
    public static function has(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }
}

// 使用例
FlashMessage::set('success', 'ログインに成功しました');
FlashMessage::set('info', 'プロフィールを更新しました');
FlashMessage::set('warning', 'パスワードの有効期限が近づいています');

if (FlashMessage::has()) {
    echo "フラッシュメッセージがあります:\n";
    foreach (FlashMessage::get() as $flash) {
        echo "  [{$flash['type']}] {$flash['message']}\n";
    }
}

// 2回目の取得では空になる
if (!FlashMessage::has()) {
    echo "フラッシュメッセージは既に表示されました\n";
}

echo "\n";

// =====================================
// 8. セッションの破棄
// =====================================

echo "--- 8. セッションの破棄 ---\n";

/**
 * セッションを完全に破棄
 *
 * ログアウト時に使用
 *
 * @return void
 */
function destroySession(): void
{
    // セッションが開始されていない場合は開始
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // セッション変数をすべて削除
    $_SESSION = [];

    // セッションクッキーを削除
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // セッションを破棄
    session_destroy();
}

echo "セッション破棄の例:\n";
echo "destroySession() を呼び出すと、セッションが完全に破棄されます\n";

echo "\n";

// =====================================
// 9. セッションハンドラのカスタマイズ
// =====================================

echo "--- 9. セッションハンドラのカスタマイズ ---\n";

/**
 * データベースセッションハンドラ
 *
 * セッションデータをデータベースに保存
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;

    public function __construct(private string $dbPath)
    {
    }

    /**
     * セッションを開く
     */
    public function open(string $path, string $name): bool
    {
        try {
            $this->pdo = new PDO("sqlite:{$this->dbPath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // テーブルを作成
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    id TEXT PRIMARY KEY,
                    data TEXT,
                    last_access INTEGER
                )
            ");

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * セッションを閉じる
     */
    public function close(): bool
    {
        $this->pdo = null;
        return true;
    }

    /**
     * セッションデータを読み込む
     */
    public function read(string $id): string|false
    {
        try {
            $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetchColumn();
            return $result !== false ? $result : '';
        } catch (PDOException $e) {
            return '';
        }
    }

    /**
     * セッションデータを書き込む
     */
    public function write(string $id, string $data): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT OR REPLACE INTO sessions (id, data, last_access)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$id, $data, time()]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * セッションを破棄
     */
    public function destroy(string $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 古いセッションをガベージコレクション
     */
    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < ?");
            $stmt->execute([time() - $max_lifetime]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }
}

echo "DatabaseSessionHandler クラスを定義しました\n";
echo "使用例:\n";
echo "  \$handler = new DatabaseSessionHandler('sessions.db');\n";
echo "  session_set_save_handler(\$handler, true);\n";
echo "  session_start();\n";

echo "\n";

// =====================================
// 10. ベストプラクティス
// =====================================

echo "--- 10. セッションのベストプラクティス ---\n";

echo "
【セキュリティのベストプラクティス】

1. セッション設定
   - session_set_cookie_params() でセキュアな設定を行う
   - httponly=true でXSS対策
   - secure=true でHTTPS接続のみに限定（本番環境）
   - samesite='Strict' でCSRF対策

2. セッションID
   - session_regenerate_id() でログイン時にIDを再生成
   - デフォルトのセッション名（PHPSESSID）を変更
   - use_strict_mode=1 で未初期化のセッションIDを拒否

3. セッションハイジャック対策
   - IPアドレスとUser-Agentをチェック
   - タイムアウトを設定
   - HTTPS通信を使用

4. セッション固定化攻撃対策
   - ログイン成功時にsession_regenerate_id()を実行
   - 権限変更時にセッションIDを再生成

5. データ保護
   - 機密情報はセッションに保存しない（またはサーバー側で暗号化）
   - 必要最小限のデータのみ保存

【パフォーマンスのベストプラクティス】

1. セッションデータのサイズ
   - 大きなデータはセッションに保存しない
   - 必要に応じてデータベースやキャッシュを使用

2. セッションの有効期限
   - 適切なタイムアウト時間を設定
   - ガベージコレクションを定期的に実行

3. セッションストレージ
   - 大規模アプリケーションではRedisやMemcachedを使用
   - データベースセッションハンドラでスケーラビリティ向上
";

echo "=== Phase 2.6: セッション - 完了 ===\n";
