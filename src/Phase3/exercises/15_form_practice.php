<?php

declare(strict_types=1);

/**
 * Phase 3.4: フォーム処理とバリデーション - 演習課題
 *
 * このファイルでは、学習したフォーム処理とバリデーションを実践します。
 *
 * 課題:
 * 1. お問い合わせフォームの作成
 * 2. ユーザー登録フォームの実装
 */

echo "=== Phase 3.4: フォーム処理とバリデーション演習課題 ===\n\n";

// データベース接続
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../../../database/php_learning.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ データベースに接続しました\n\n";
} catch (PDOException $e) {
    die("接続エラー: " . $e->getMessage() . "\n");
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  課題1: お問い合わせフォームの作成\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// お問い合わせテーブル作成
try {
    $pdo->exec("DROP TABLE IF EXISTS contact_messages");
    $pdo->exec("
        CREATE TABLE contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            priority TEXT DEFAULT 'normal',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ お問い合わせテーブルを作成しました\n\n";
} catch (PDOException $e) {
    die("テーブル作成エラー: " . $e->getMessage() . "\n");
}

/**
 * バリデータークラス
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $customMessages = [];
    private array $attributeNames = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function setCustomMessages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    public function setAttributeNames(array $names): self
    {
        $this->attributeNames = $names;
        return $this;
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        if (str_contains($rule, ':')) {
            [$ruleName, $parameter] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        $passed = match ($ruleName) {
            'required' => $this->validateRequired($value),
            'email' => $this->validateEmail($value),
            'min' => $this->validateMin($value, (int)$parameter),
            'max' => $this->validateMax($value, (int)$parameter),
            'in' => $this->validateIn($value, $parameter),
            'alphanumeric' => $this->validateAlphanumeric($value),
            'regex' => $this->validateRegex($value, $parameter),
            'confirmed' => $this->validateConfirmed($field, $value),
            default => true
        };

        if (!$passed) {
            $this->addError($field, $ruleName, $parameter);
        }
    }

    private function validateRequired(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    private function validateEmail(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(mixed $value, int $min): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return mb_strlen((string)$value) >= $min;
    }

    private function validateMax(mixed $value, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return mb_strlen((string)$value) <= $max;
    }

    private function validateIn(mixed $value, ?string $list): bool
    {
        if ($value === null || $value === '' || $list === null) {
            return true;
        }
        $allowedValues = explode(',', $list);
        return in_array($value, $allowedValues, true);
    }

    private function validateAlphanumeric(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return preg_match('/^[a-zA-Z0-9_]+$/', (string)$value) === 1;
    }

    private function validateRegex(mixed $value, ?string $pattern): bool
    {
        if ($value === null || $value === '' || $pattern === null) {
            return true;
        }
        return preg_match($pattern, (string)$value) === 1;
    }

    private function validateConfirmed(string $field, mixed $value): bool
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        return $value === $confirmValue;
    }

    private function addError(string $field, string $rule, ?string $parameter): void
    {
        $message = $this->getErrorMessage($field, $rule, $parameter);
        $this->errors[$field][] = $message;
    }

    private function getErrorMessage(string $field, string $rule, ?string $parameter): string
    {
        $customKey = "$field.$rule";
        if (isset($this->customMessages[$customKey])) {
            return $this->replaceMessagePlaceholders($this->customMessages[$customKey], $field, $parameter);
        }

        $attribute = $this->attributeNames[$field] ?? $field;
        $defaultMessages = [
            'required' => ':attribute は必須です',
            'email' => ':attribute はメールアドレス形式で入力してください',
            'min' => ':attribute は:min文字以上にしてください',
            'max' => ':attribute は:max文字以下にしてください',
            'in' => ':attribute は:values のいずれかを選択してください',
            'alphanumeric' => ':attribute は英数字とアンダースコアのみ使用できます',
            'regex' => ':attribute の形式が正しくありません',
            'confirmed' => ':attribute の確認が一致しません',
        ];

        $message = $defaultMessages[$rule] ?? ':attribute が無効です';
        return $this->replaceMessagePlaceholders($message, $field, $parameter);
    }

    private function replaceMessagePlaceholders(string $message, string $field, ?string $parameter): string
    {
        $attribute = $this->attributeNames[$field] ?? $field;
        $replacements = [':attribute' => $attribute, ':field' => $field];

        if ($parameter !== null) {
            $replacements[':min'] = $parameter;
            $replacements[':max'] = $parameter;
            $replacements[':values'] = $parameter;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function getAllErrors(): array
    {
        $all = [];
        foreach ($this->errors as $fieldErrors) {
            $all = array_merge($all, $fieldErrors);
        }
        return $all;
    }
}

/**
 * お問い合わせフォーム処理クラス
 */
class ContactForm
{
    public function __construct(private PDO $pdo) {}

    /**
     * お問い合わせを送信
     */
    public function submit(array $data): array
    {
        // バリデーション
        $validator = new Validator($data, [
            'name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'subject' => 'required|min:3|max:100',
            'message' => 'required|min:10|max:1000',
            'priority' => 'in:low,normal,high',
        ]);

        $validator->setAttributeNames([
            'name' => '名前',
            'email' => 'メールアドレス',
            'subject' => '件名',
            'message' => 'メッセージ',
            'priority' => '優先度',
        ]);

        if (!$validator->validate()) {
            return ['success' => false, 'errors' => $validator->getAllErrors()];
        }

        // データベースに保存
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, priority)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['subject'],
                $data['message'],
                $data['priority'] ?? 'normal'
            ]);

            return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['送信中にエラーが発生しました']];
        }
    }
}

echo "--- テスト1: 正常なお問い合わせ送信 ---\n";

$contactForm = new ContactForm($pdo);

$data = [
    'name' => '山田太郎',
    'email' => 'yamada@example.com',
    'subject' => 'サービスについての問い合わせ',
    'message' => 'サービスの詳細について教えてください。料金プランや機能について知りたいです。',
    'priority' => 'normal'
];

$result = $contactForm->submit($data);

if ($result['success']) {
    echo "✓ お問い合わせを送信しました（ID: {$result['id']}）\n";
} else {
    echo "✗ 送信エラー:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

echo "--- テスト2: バリデーションエラー ---\n";

$invalidData = [
    'name' => 'a',  // 短すぎる
    'email' => 'invalid-email',  // 形式が正しくない
    'subject' => '',  // 必須
    'message' => '短い',  // 短すぎる
    'priority' => 'urgent'  // 許可されていない値
];

$result = $contactForm->submit($invalidData);

if ($result['success']) {
    echo "✓ お問い合わせを送信しました\n";
} else {
    echo "✗ バリデーションエラー（予期した動作）:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  課題2: ユーザー登録フォームの実装\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ユーザーテーブル作成
try {
    $pdo->exec("DROP TABLE IF EXISTS form_users");
    $pdo->exec("
        CREATE TABLE form_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            age INTEGER,
            gender TEXT,
            interests TEXT,
            terms_accepted INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ ユーザーテーブルを作成しました\n\n";
} catch (PDOException $e) {
    die("テーブル作成エラー: " . $e->getMessage() . "\n");
}

/**
 * ユーザー登録フォーム処理クラス
 */
class UserRegistrationForm
{
    public function __construct(private PDO $pdo) {}

    /**
     * ユーザーを登録
     */
    public function register(array $data): array
    {
        // バリデーション
        $validator = new Validator($data, [
            'username' => 'required|min:3|max:20|alphanumeric',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'full_name' => 'required|min:2|max:50',
            'age' => 'required',
            'gender' => 'required|in:male,female,other',
            'terms' => 'required',
        ]);

        $validator->setAttributeNames([
            'username' => 'ユーザー名',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'full_name' => '氏名',
            'age' => '年齢',
            'gender' => '性別',
            'terms' => '利用規約',
        ]);

        $validator->setCustomMessages([
            'terms.required' => '利用規約に同意してください',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません',
        ]);

        if (!$validator->validate()) {
            return ['success' => false, 'errors' => $validator->getAllErrors()];
        }

        // パスワードの複雑性チェック
        $passwordErrors = $this->validatePasswordComplexity($data['password']);
        if (!empty($passwordErrors)) {
            return ['success' => false, 'errors' => $passwordErrors];
        }

        // 年齢チェック
        $age = filter_var($data['age'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 13, 'max_range' => 120]]);
        if ($age === false) {
            return ['success' => false, 'errors' => ['年齢は13歳以上120歳以下で入力してください']];
        }

        // ユーザー名の重複チェック
        if ($this->usernameExists($data['username'])) {
            return ['success' => false, 'errors' => ['このユーザー名は既に使用されています']];
        }

        // メールアドレスの重複チェック
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'errors' => ['このメールアドレスは既に登録されています']];
        }

        // データベースに保存
        try {
            $passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
            $interests = isset($data['interests']) && is_array($data['interests'])
                ? implode(',', $data['interests'])
                : '';

            $stmt = $this->pdo->prepare("
                INSERT INTO form_users (username, email, password_hash, full_name, age, gender, interests, terms_accepted)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");

            $stmt->execute([
                $data['username'],
                $data['email'],
                $passwordHash,
                $data['full_name'],
                $age,
                $data['gender'],
                $interests
            ]);

            return ['success' => true, 'user_id' => (int)$this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['登録中にエラーが発生しました']];
        }
    }

    /**
     * パスワードの複雑性をチェック
     */
    private function validatePasswordComplexity(string $password): array
    {
        $errors = [];

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

    /**
     * ユーザー名が既に存在するかチェック
     */
    private function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM form_users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }

    /**
     * メールアドレスが既に存在するかチェック
     */
    private function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM form_users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
}

echo "--- テスト3: 正常なユーザー登録 ---\n";

$registrationForm = new UserRegistrationForm($pdo);

$userData = [
    'username' => 'testuser123',
    'email' => 'testuser@example.com',
    'password' => 'SecurePass123!',
    'password_confirmation' => 'SecurePass123!',
    'full_name' => '田中花子',
    'age' => '25',
    'gender' => 'female',
    'interests' => ['programming', 'reading', 'music'],
    'terms' => 'on'
];

$result = $registrationForm->register($userData);

if ($result['success']) {
    echo "✓ ユーザー登録成功（ID: {$result['user_id']}）\n";
} else {
    echo "✗ 登録エラー:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

echo "--- テスト4: パスワード確認の不一致 ---\n";

$mismatchData = [
    'username' => 'testuser456',
    'email' => 'test2@example.com',
    'password' => 'SecurePass123!',
    'password_confirmation' => 'DifferentPass123!',  // 不一致
    'full_name' => '佐藤次郎',
    'age' => '30',
    'gender' => 'male',
    'terms' => 'on'
];

$result = $registrationForm->register($mismatchData);

if ($result['success']) {
    echo "✓ ユーザー登録成功\n";
} else {
    echo "✗ バリデーションエラー（予期した動作）:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

echo "--- テスト5: 重複ユーザー名での登録 ---\n";

$duplicateData = [
    'username' => 'testuser123',  // 既に存在
    'email' => 'another@example.com',
    'password' => 'SecurePass123!',
    'password_confirmation' => 'SecurePass123!',
    'full_name' => '鈴木三郎',
    'age' => '28',
    'gender' => 'other',
    'terms' => 'on'
];

$result = $registrationForm->register($duplicateData);

if ($result['success']) {
    echo "✓ ユーザー登録成功\n";
} else {
    echo "✗ 登録エラー（予期した動作）:\n";
    foreach ($result['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

// 登録されたユーザーを確認
echo "--- 登録済みユーザー一覧 ---\n";
$stmt = $pdo->query("SELECT username, email, full_name, age, gender FROM form_users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "ユーザー: {$user['username']} ({$user['email']})\n";
    echo "  氏名: {$user['full_name']}, 年齢: {$user['age']}, 性別: {$user['gender']}\n";
}
echo "\n";

// クリーンアップ
$pdo->exec("DROP TABLE IF EXISTS contact_messages");
$pdo->exec("DROP TABLE IF EXISTS form_users");
echo "✓ テストテーブルを削除しました\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  まとめ\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "実装したフォーム処理機能:\n";
echo "  ✓ 包括的なバリデーション（required、min、max、email、in、confirmed）\n";
echo "  ✓ カスタムエラーメッセージ\n";
echo "  ✓ フィールド名の日本語表示\n";
echo "  ✓ パスワード複雑性チェック\n";
echo "  ✓ 重複チェック（ユーザー名、メールアドレス）\n";
echo "  ✓ データのサニタイゼーション\n";
echo "  ✓ セキュアなパスワードハッシュ化\n";
echo "  ✓ データベースへの安全な保存\n\n";

echo "=== Phase 3.4: フォーム処理とバリデーション演習課題 完了 ===\n";
