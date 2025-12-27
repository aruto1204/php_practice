<?php

declare(strict_types=1);

/**
 * Phase 3.3: セキュリティ - パスワードハッシュ化、セキュアなセッション管理、入力バリデーション
 *
 * このファイルでは、パスワードの安全な保存方法、セッションのセキュリティ対策、
 * 入力バリデーションとサニタイゼーションを学習します。
 *
 * 学習内容:
 * - パスワードのハッシュ化と検証
 * - password_hash()とpassword_verify()
 * - セキュアなセッション管理
 * - 入力バリデーションとサニタイゼーション
 */

echo "=== Phase 3.3: パスワード・セッション・バリデーション ===\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  パート1: パスワードハッシュ化\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "--- 1. パスワードハッシュ化の重要性 ---\n";
/**
 * パスワードを平文で保存することは、絶対に避けなければなりません。
 *
 * 理由:
 * - データベース漏洩時に全ユーザーのパスワードが流出
 * - 管理者でもユーザーのパスワードが見えてしまう
 * - 他のサービスで同じパスワードを使っている場合、被害が拡大
 */
echo "❌ 絶対にやってはいけないこと:\n";
echo "  - パスワードを平文で保存\n";
echo "  - MD5やSHA1でハッシュ化（高速すぎて総当たり攻撃に弱い）\n";
echo "  - 独自のハッシュアルゴリズム\n\n";

echo "✅ 正しい方法:\n";
echo "  - password_hash()を使用\n";
echo "  - Argon2id または bcrypt アルゴリズム\n";
echo "  - ソルト（salt）は自動的に生成される\n\n";

echo "--- 2. password_hash()の使用 ---\n";

// パスワードをハッシュ化
$plainPassword = 'MySecurePassword123!';

echo "平文パスワード: $plainPassword\n\n";

// PASSWORD_DEFAULT: 現在の推奨アルゴリズム（bcrypt）
$hashDefault = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "PASSWORD_DEFAULT (bcrypt):\n";
echo "  ハッシュ: $hashDefault\n";
echo "  長さ: " . strlen($hashDefault) . " 文字\n\n";

// PASSWORD_BCRYPT: bcrypt アルゴリズム
$hashBcrypt = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
echo "PASSWORD_BCRYPT (cost=12):\n";
echo "  ハッシュ: $hashBcrypt\n";
echo "  cost: ハッシュ化の計算コスト（高いほど安全だが処理時間がかかる）\n\n";

// PASSWORD_ARGON2ID: Argon2id アルゴリズム（最も安全、PHP 7.3+）
if (defined('PASSWORD_ARGON2ID')) {
    $hashArgon2id = password_hash($plainPassword, PASSWORD_ARGON2ID);
    echo "PASSWORD_ARGON2ID（推奨）:\n";
    echo "  ハッシュ: $hashArgon2id\n";
    echo "  アルゴリズム: Argon2id（最も安全）\n\n";
}

echo "--- 3. password_verify()による検証 ---\n";

// 正しいパスワードで検証
$inputPassword = 'MySecurePassword123!';
echo "入力パスワード: $inputPassword\n";

if (password_verify($inputPassword, $hashDefault)) {
    echo "✓ パスワードが一致しました\n\n";
} else {
    echo "✗ パスワードが一致しません\n\n";
}

// 間違ったパスワードで検証
$wrongPassword = 'WrongPassword';
echo "間違ったパスワード: $wrongPassword\n";

if (password_verify($wrongPassword, $hashDefault)) {
    echo "✓ パスワードが一致しました\n\n";
} else {
    echo "✗ パスワードが一致しません\n\n";
}

echo "--- 4. password_needs_rehash()によるハッシュの更新 ---\n";
/**
 * パスワードハッシュは、セキュリティ向上のため定期的に更新すべきです。
 * password_needs_rehash()は、ハッシュが古いかチェックします。
 */

echo "✅ ハッシュの更新確認\n";
$oldHash = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 10]);

// より高いcostで更新が必要かチェック
if (password_needs_rehash($oldHash, PASSWORD_BCRYPT, ['cost' => 12])) {
    echo "ハッシュの更新が必要です（costを10から12に上げる）\n";
    $newHash = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]);
    echo "新しいハッシュ: $newHash\n\n";
} else {
    echo "ハッシュは最新です\n\n";
}

echo "--- 5. password_get_info()によるハッシュ情報の取得 ---\n";

$info = password_get_info($hashDefault);
echo "ハッシュ情報:\n";
echo "  アルゴリズム: " . $info['algoName'] . "\n";
echo "  オプション: " . json_encode($info['options']) . "\n\n";

echo "--- 6. パスワードポリシーの実装 ---\n";

/**
 * パスワードポリシークラス
 */
class PasswordPolicy
{
    private const MIN_LENGTH = 8;
    private const MAX_LENGTH = 128;

    /**
     * パスワードをバリデーション
     */
    public static function validate(string $password): array
    {
        $errors = [];

        // 長さチェック
        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = sprintf('パスワードは%d文字以上にしてください', self::MIN_LENGTH);
        }

        if (strlen($password) > self::MAX_LENGTH) {
            $errors[] = sprintf('パスワードは%d文字以下にしてください', self::MAX_LENGTH);
        }

        // 複雑性チェック
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'パスワードには小文字を含めてください';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'パスワードには大文字を含めてください';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'パスワードには数字を含めてください';
        }

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $errors[] = 'パスワードには記号を含めてください';
        }

        // よくあるパスワードチェック（簡易版）
        $commonPasswords = ['password', '12345678', 'password123', 'qwerty', 'abc123'];
        if (in_array(strtolower($password), $commonPasswords, true)) {
            $errors[] = 'よくあるパスワードは使用できません';
        }

        return $errors;
    }

    /**
     * 安全なパスワードかチェック
     */
    public static function isSecure(string $password): bool
    {
        return empty(self::validate($password));
    }
}

echo "✅ パスワードポリシーのテスト\n";

$testPasswords = [
    'abc' => false,
    'password' => false,
    '12345678' => false,
    'Password1' => false,
    'Password1!' => true,
    'MySecureP@ssw0rd' => true,
];

foreach ($testPasswords as $testPassword => $expected) {
    $errors = PasswordPolicy::validate((string)$testPassword);
    $isSecure = empty($errors);
    $result = $isSecure === $expected ? '✓' : '✗';

    echo "$result パスワード: $testPassword → " . ($isSecure ? '安全' : '不安全') . "\n";

    if (!$isSecure) {
        foreach ($errors as $error) {
            echo "     - $error\n";
        }
    }
}
echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  パート2: セキュアなセッション管理\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "--- 1. セキュアなセッション設定 ---\n";

/**
 * セキュアなセッションマネージャー
 */
class SecureSessionManager
{
    private const SESSION_LIFETIME = 3600; // 1時間
    private const SESSION_REGENERATE_INTERVAL = 300; // 5分

    /**
     * セキュアなセッションを開始
     */
    public static function start(): void
    {
        // セッションクッキーのパラメータを設定
        session_set_cookie_params([
            'lifetime' => self::SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => true,      // HTTPS通信のみ（本番環境では必須）
            'httponly' => true,    // JavaScriptからアクセス不可
            'samesite' => 'Lax'    // CSRF対策
        ]);

        // セッション名を変更（デフォルトのPHPSESSIDは使わない）
        session_name('SECURE_SESSION');

        // セッションを開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッション固定化攻撃対策
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
            $_SESSION['created'] = time();
        }

        // 定期的にセッションIDを再生成
        if (isset($_SESSION['last_regenerate']) &&
            (time() - $_SESSION['last_regenerate']) > self::SESSION_REGENERATE_INTERVAL) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        } else {
            $_SESSION['last_regenerate'] = $_SESSION['last_regenerate'] ?? time();
        }

        // セッションタイムアウトチェック
        if (isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity']) > self::SESSION_LIFETIME) {
            self::destroy();
            return;
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * セッションを破棄
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
    }

    /**
     * ユーザーをログイン
     */
    public static function login(int $userId): void
    {
        // セッション固定化攻撃対策
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * ログイン状態を確認
     */
    public static function isLoggedIn(): bool
    {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return false;
        }

        // IPアドレスとUser-Agentのチェック（セッションハイジャック対策）
        if (isset($_SESSION['ip_address']) &&
            $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            self::destroy();
            return false;
        }

        if (isset($_SESSION['user_agent']) &&
            $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            self::destroy();
            return false;
        }

        return true;
    }
}

echo "✅ セキュアなセッション設定の例\n";
echo "session_set_cookie_params([\n";
echo "    'lifetime' => 3600,\n";
echo "    'path' => '/',\n";
echo "    'secure' => true,      // HTTPS通信のみ\n";
echo "    'httponly' => true,    // JavaScriptからアクセス不可\n";
echo "    'samesite' => 'Lax'    // CSRF対策\n";
echo "]);\n\n";

echo "セキュリティのポイント:\n";
echo "  1. secure属性: HTTPS通信のみでクッキーを送信\n";
echo "  2. httponly属性: XSS攻撃によるクッキー盗難を防ぐ\n";
echo "  3. samesite属性: CSRF攻撃を防ぐ\n";
echo "  4. セッションID再生成: セッション固定化攻撃を防ぐ\n";
echo "  5. タイムアウト: 非アクティブセッションを自動的に破棄\n";
echo "  6. IPアドレス/User-Agentチェック: セッションハイジャックを防ぐ\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  パート3: 入力バリデーションとサニタイゼーション\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "--- 1. バリデーションとサニタイゼーションの違い ---\n";
echo "バリデーション: 入力値が期待される形式か検証する\n";
echo "サニタイゼーション: 入力値から危険な文字を除去・変換する\n\n";

echo "重要な原則:\n";
echo "  1. ホワイトリスト方式（許可するものを定義）\n";
echo "  2. ブラックリスト方式は避ける（禁止するものを定義）\n";
echo "  3. バリデーション → サニタイゼーション の順で処理\n";
echo "  4. 出力時にもエスケープ処理を行う（多層防御）\n\n";

echo "--- 2. filter_var()による入力検証 ---\n";

/**
 * 入力バリデータークラス
 */
class InputValidator
{
    /**
     * メールアドレスのバリデーション
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * URLのバリデーション
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 整数のバリデーション
     */
    public static function validateInt(mixed $value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): ?int
    {
        $options = ['options' => ['min_range' => $min, 'max_range' => $max]];
        $result = filter_var($value, FILTER_VALIDATE_INT, $options);
        return $result !== false ? $result : null;
    }

    /**
     * IPアドレスのバリデーション
     */
    public static function validateIp(string $ip, bool $allowPrivate = false): bool
    {
        $flags = $allowPrivate ? 0 : FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }
}

echo "✅ filter_var()を使った入力検証\n\n";

// メールアドレスのバリデーション
$testEmails = [
    'user@example.com' => true,
    'invalid.email' => false,
    'user@' => false,
    '@example.com' => false,
];

echo "メールアドレスのバリデーション:\n";
foreach ($testEmails as $email => $expected) {
    $isValid = InputValidator::validateEmail($email);
    $result = $isValid === $expected ? '✓' : '✗';
    echo "$result $email → " . ($isValid ? '有効' : '無効') . "\n";
}
echo "\n";

// 整数のバリデーション
echo "整数のバリデーション（1-100の範囲）:\n";
$testInts = ['50', '150', 'abc', '0'];
foreach ($testInts as $value) {
    $validated = InputValidator::validateInt($value, 1, 100);
    echo "入力: $value → " . ($validated !== null ? "有効 ($validated)" : '無効') . "\n";
}
echo "\n";

echo "--- 3. 入力のサニタイゼーション ---\n";

/**
 * 入力サニタイザークラス
 */
class InputSanitizer
{
    /**
     * 文字列のサニタイズ
     */
    public static function sanitizeString(string $input): string
    {
        // 制御文字を削除
        $sanitized = filter_var($input, FILTER_SANITIZE_STRING);
        // トリム
        return trim($sanitized);
    }

    /**
     * メールアドレスのサニタイズ
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * URLのサニタイズ
     */
    public static function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * HTML出力用のエスケープ
     */
    public static function escapeHtml(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

echo "✅ サニタイゼーションの例\n";
$unsafeInput = '<script>alert("XSS")</script>user@example.com';
echo "入力: $unsafeInput\n";
echo "サニタイズ後: " . InputSanitizer::escapeHtml($unsafeInput) . "\n\n";

echo "--- 4. 包括的なバリデーションシステム ---\n";

/**
 * 包括的なバリデータークラス
 */
class Validator
{
    private array $errors = [];

    /**
     * 必須チェック
     */
    public function required(string $field, mixed $value): self
    {
        if ($value === null || $value === '') {
            $this->errors[$field][] = "$field は必須です";
        }
        return $this;
    }

    /**
     * 最小長チェック
     */
    public function minLength(string $field, string $value, int $min): self
    {
        if (mb_strlen($value) < $min) {
            $this->errors[$field][] = "$field は{$min}文字以上にしてください";
        }
        return $this;
    }

    /**
     * 最大長チェック
     */
    public function maxLength(string $field, string $value, int $max): self
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = "$field は{$max}文字以下にしてください";
        }
        return $this;
    }

    /**
     * パターンマッチ
     */
    public function pattern(string $field, string $value, string $pattern, string $message = ''): self
    {
        if (!preg_match($pattern, $value)) {
            $this->errors[$field][] = $message ?: "$field の形式が正しくありません";
        }
        return $this;
    }

    /**
     * エラーを取得
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * バリデーション成功か
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }
}

echo "✅ 包括的なバリデーションの例\n";
$validator = new Validator();

$username = 'ab';
$email = 'invalid-email';

$validator
    ->required('username', $username)
    ->minLength('username', $username, 3)
    ->maxLength('username', $username, 20)
    ->required('email', $email)
    ->pattern('email', $email, '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'メールアドレスの形式が正しくありません');

if (!$validator->isValid()) {
    echo "バリデーションエラー:\n";
    foreach ($validator->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
} else {
    echo "✓ バリデーション成功\n";
}
echo "\n";

echo "--- 5. まとめ：セキュリティのベストプラクティス ---\n";
echo "✅ パスワード:\n";
echo "  1. password_hash()とpassword_verify()を使用\n";
echo "  2. Argon2id または bcrypt を使用\n";
echo "  3. パスワードポリシーを実装（長さ、複雑性）\n";
echo "  4. 定期的にハッシュを更新\n\n";

echo "✅ セッション:\n";
echo "  1. secure、httponly、samesite属性を設定\n";
echo "  2. セッションIDを定期的に再生成\n";
echo "  3. タイムアウトを設定\n";
echo "  4. IPアドレス/User-Agentをチェック\n\n";

echo "✅ 入力バリデーション:\n";
echo "  1. ホワイトリスト方式で検証\n";
echo "  2. filter_var()を活用\n";
echo "  3. バリデーション → サニタイゼーション の順\n";
echo "  4. 出力時にもエスケープ\n\n";

echo "=== Phase 3.3: パスワード・セッション・バリデーション 完了 ===\n";
