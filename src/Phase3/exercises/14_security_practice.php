<?php

declare(strict_types=1);

/**
 * Phase 3.3: セキュリティ - 演習課題
 *
 * このファイルでは、学習したセキュリティ対策を実践します。
 *
 * 課題:
 * 1. セキュアなユーザー登録・ログインシステム
 * 2. 包括的な入力バリデーションシステム
 * 3. セキュアなフォーム処理
 */

echo "=== Phase 3.3: セキュリティ演習課題 ===\n\n";

// データベース接続
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../../../database/php_learning.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ データベースに接続しました\n\n";
} catch (PDOException $e) {
    die("接続エラー: " . $e->getMessage() . "\n");
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  課題1: セキュアなユーザー登録・ログインシステム\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// テーブル作成
try {
    $pdo->exec("DROP TABLE IF EXISTS secure_users");
    $pdo->exec("
        CREATE TABLE secure_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            last_login_at TEXT,
            failed_login_attempts INTEGER DEFAULT 0,
            locked_until TEXT
        )
    ");
    echo "✓ ユーザーテーブルを作成しました\n\n";
} catch (PDOException $e) {
    die("テーブル作成エラー: " . $e->getMessage() . "\n");
}

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

        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = sprintf('パスワードは%d文字以上にしてください', self::MIN_LENGTH);
        }

        if (strlen($password) > self::MAX_LENGTH) {
            $errors[] = sprintf('パスワードは%d文字以下にしてください', self::MAX_LENGTH);
        }

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

        return $errors;
    }
}

/**
 * ユーザー認証クラス
 */
class UserAuthentication
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15分（秒単位）

    public function __construct(private PDO $pdo) {}

    /**
     * ユーザーを登録
     */
    public function register(string $username, string $email, string $password): array
    {
        // 入力のバリデーション
        $errors = [];

        // ユーザー名のバリデーション
        if (strlen($username) < 3 || strlen($username) > 20) {
            $errors[] = 'ユーザー名は3文字以上20文字以下にしてください';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'ユーザー名は英数字とアンダースコアのみ使用できます';
        }

        // メールアドレスのバリデーション
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'メールアドレスの形式が正しくありません';
        }

        // パスワードのバリデーション
        $passwordErrors = PasswordPolicy::validate($password);
        $errors = array_merge($errors, $passwordErrors);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            // ユーザー名の重複チェック
            $stmt = $this->pdo->prepare("SELECT id FROM secure_users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'errors' => ['このユーザー名は既に使用されています']];
            }

            // メールアドレスの重複チェック
            $stmt = $this->pdo->prepare("SELECT id FROM secure_users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'errors' => ['このメールアドレスは既に使用されています']];
            }

            // パスワードをハッシュ化
            $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

            // ユーザーを登録
            $stmt = $this->pdo->prepare("
                INSERT INTO secure_users (username, email, password_hash)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $email, $passwordHash]);

            return ['success' => true, 'user_id' => (int)$this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['登録中にエラーが発生しました']];
        }
    }

    /**
     * ユーザーをログイン
     */
    public function login(string $username, string $password): array
    {
        try {
            // ユーザーを取得
            $stmt = $this->pdo->prepare("
                SELECT id, username, password_hash, failed_login_attempts, locked_until
                FROM secure_users
                WHERE username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'error' => 'ユーザー名またはパスワードが正しくありません'];
            }

            // アカウントロックチェック
            if ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
                $remainingMinutes = ceil((strtotime($user['locked_until']) - time()) / 60);
                return [
                    'success' => false,
                    'error' => "アカウントがロックされています。{$remainingMinutes}分後に再試行してください"
                ];
            }

            // パスワード検証
            if (!password_verify($password, $user['password_hash'])) {
                // 失敗回数をインクリメント
                $this->incrementFailedAttempts((int)$user['id'], (int)$user['failed_login_attempts']);

                return ['success' => false, 'error' => 'ユーザー名またはパスワードが正しくありません'];
            }

            // ログイン成功
            $this->resetFailedAttempts((int)$user['id']);
            $this->updateLastLogin((int)$user['id']);

            // パスワードハッシュの更新が必要かチェック
            if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
                $newHash = password_hash($password, PASSWORD_ARGON2ID);
                $stmt = $this->pdo->prepare("UPDATE secure_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $user['id']]);
            }

            return [
                'success' => true,
                'user_id' => (int)$user['id'],
                'username' => $user['username']
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'ログイン中にエラーが発生しました'];
        }
    }

    /**
     * 失敗回数をインクリメント
     */
    private function incrementFailedAttempts(int $userId, int $currentAttempts): void
    {
        $newAttempts = $currentAttempts + 1;

        // 最大失敗回数に達したらアカウントをロック
        if ($newAttempts >= self::MAX_FAILED_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_DURATION);
            $stmt = $this->pdo->prepare("
                UPDATE secure_users
                SET failed_login_attempts = ?, locked_until = ?
                WHERE id = ?
            ");
            $stmt->execute([$newAttempts, $lockedUntil, $userId]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE secure_users
                SET failed_login_attempts = ?
                WHERE id = ?
            ");
            $stmt->execute([$newAttempts, $userId]);
        }
    }

    /**
     * 失敗回数をリセット
     */
    private function resetFailedAttempts(int $userId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE secure_users
            SET failed_login_attempts = 0, locked_until = NULL
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    /**
     * 最終ログイン時刻を更新
     */
    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE secure_users
            SET last_login_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
}

echo "--- テスト1: ユーザー登録 ---\n";
$auth = new UserAuthentication($pdo);

// 正常な登録
echo "✅ 正常なユーザー登録:\n";
$result = $auth->register('testuser', 'test@example.com', 'SecurePass123!');
if ($result['success']) {
    echo "  ✓ ユーザー登録成功（ID: {$result['user_id']}）\n";
} else {
    echo "  ✗ エラー: " . implode(', ', $result['errors']) . "\n";
}
echo "\n";

// 無効なパスワードでの登録
echo "❌ 無効なパスワードでの登録:\n";
$result = $auth->register('testuser2', 'test2@example.com', 'weak');
if ($result['success']) {
    echo "  ✓ ユーザー登録成功\n";
} else {
    echo "  ✗ 登録失敗（予期した動作）:\n";
    foreach ($result['errors'] as $error) {
        echo "     - $error\n";
    }
}
echo "\n";

// 重複ユーザー名での登録
echo "❌ 重複ユーザー名での登録:\n";
$result = $auth->register('testuser', 'another@example.com', 'SecurePass123!');
if ($result['success']) {
    echo "  ✓ ユーザー登録成功\n";
} else {
    echo "  ✗ 登録失敗（予期した動作）: " . implode(', ', $result['errors']) . "\n";
}
echo "\n";

echo "--- テスト2: ユーザーログイン ---\n";

// 正常なログイン
echo "✅ 正常なログイン:\n";
$result = $auth->login('testuser', 'SecurePass123!');
if ($result['success']) {
    echo "  ✓ ログイン成功（ユーザー: {$result['username']}）\n";
} else {
    echo "  ✗ ログイン失敗: {$result['error']}\n";
}
echo "\n";

// 間違ったパスワードでのログイン
echo "❌ 間違ったパスワードでのログイン:\n";
for ($i = 1; $i <= 3; $i++) {
    echo "  試行 $i:\n";
    $result = $auth->login('testuser', 'WrongPassword');
    if ($result['success']) {
        echo "    ✓ ログイン成功\n";
    } else {
        echo "    ✗ ログイン失敗（予期した動作）: {$result['error']}\n";
    }
}
echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  課題2: 包括的な入力バリデーションシステム\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

/**
 * 包括的なバリデータークラス
 */
class FormValidator
{
    private array $data = [];
    private array $errors = [];
    private array $rules = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * バリデーションルールを定義
     */
    public function rules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * バリデーションを実行
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * ルールを適用
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        // パラメータ付きルール（例: min:3）
        if (str_contains($rule, ':')) {
            [$ruleName, $parameter] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        match ($ruleName) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'min' => $this->validateMin($field, $value, (int)$parameter),
            'max' => $this->validateMax($field, $value, (int)$parameter),
            'numeric' => $this->validateNumeric($field, $value),
            'alpha' => $this->validateAlpha($field, $value),
            'alphanumeric' => $this->validateAlphanumeric($field, $value),
            'url' => $this->validateUrl($field, $value),
            'regex' => $this->validateRegex($field, $value, $parameter),
            'in' => $this->validateIn($field, $value, $parameter),
            default => null
        };
    }

    private function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->addError($field, "$field は必須です");
        }
    }

    private function validateEmail(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "$field はメールアドレス形式で入力してください");
        }
    }

    private function validateMin(string $field, mixed $value, int $min): void
    {
        if ($value !== null && mb_strlen((string)$value) < $min) {
            $this->addError($field, "$field は{$min}文字以上にしてください");
        }
    }

    private function validateMax(string $field, mixed $value, int $max): void
    {
        if ($value !== null && mb_strlen((string)$value) > $max) {
            $this->addError($field, "$field は{$max}文字以下にしてください");
        }
    }

    private function validateNumeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "$field は数値で入力してください");
        }
    }

    private function validateAlpha(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !ctype_alpha((string)$value)) {
            $this->addError($field, "$field は英字のみで入力してください");
        }
    }

    private function validateAlphanumeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !ctype_alnum((string)$value)) {
            $this->addError($field, "$field は英数字のみで入力してください");
        }
    }

    private function validateUrl(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "$field はURL形式で入力してください");
        }
    }

    private function validateRegex(string $field, mixed $value, ?string $pattern): void
    {
        if ($pattern !== null && $value !== null && $value !== '' && !preg_match($pattern, (string)$value)) {
            $this->addError($field, "$field の形式が正しくありません");
        }
    }

    private function validateIn(string $field, mixed $value, ?string $list): void
    {
        if ($list === null) {
            return;
        }

        $allowedValues = explode(',', $list);
        if ($value !== null && $value !== '' && !in_array($value, $allowedValues, true)) {
            $this->addError($field, "$field は" . implode('、', $allowedValues) . "のいずれかを選択してください");
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}

echo "--- テスト3: フォームバリデーション ---\n";

// テストデータ1: 正常なデータ
echo "✅ 正常なフォームデータ:\n";
$formData1 = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => '25',
    'website' => 'https://example.com',
    'gender' => 'male'
];

$validator1 = new FormValidator($formData1);
$validator1->rules([
    'name' => 'required|min:3|max:50',
    'email' => 'required|email',
    'age' => 'required|numeric',
    'website' => 'url',
    'gender' => 'in:male,female,other'
]);

if ($validator1->validate()) {
    echo "  ✓ バリデーション成功\n";
} else {
    echo "  ✗ バリデーションエラー:\n";
    foreach ($validator1->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "     - $error\n";
        }
    }
}
echo "\n";

// テストデータ2: 不正なデータ
echo "❌ 不正なフォームデータ:\n";
$formData2 = [
    'name' => 'ab',  // 短すぎる
    'email' => 'invalid-email',  // 形式が正しくない
    'age' => 'twenty',  // 数値ではない
    'website' => 'not-a-url',  // URL形式ではない
    'gender' => 'unknown'  // 許可されていない値
];

$validator2 = new FormValidator($formData2);
$validator2->rules([
    'name' => 'required|min:3|max:50',
    'email' => 'required|email',
    'age' => 'required|numeric',
    'website' => 'url',
    'gender' => 'in:male,female,other'
]);

if ($validator2->validate()) {
    echo "  ✓ バリデーション成功\n";
} else {
    echo "  ✗ バリデーションエラー（予期した動作）:\n";
    foreach ($validator2->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "     - $error\n";
        }
    }
}
echo "\n";

// クリーンアップ
$pdo->exec("DROP TABLE IF EXISTS secure_users");
echo "✓ テストテーブルを削除しました\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  まとめ\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "実装したセキュリティ機能:\n";
echo "  ✓ パスワードのArgon2idハッシュ化\n";
echo "  ✓ パスワードポリシー（長さ、複雑性）\n";
echo "  ✓ ログイン失敗回数制限とアカウントロック\n";
echo "  ✓ 包括的な入力バリデーション\n";
echo "  ✓ SQLインジェクション対策（プリペアドステートメント）\n";
echo "  ✓ メールアドレス・ユーザー名の重複チェック\n";
echo "  ✓ パスワードハッシュの自動更新\n\n";

echo "=== Phase 3.3: セキュリティ演習課題 完了 ===\n";
