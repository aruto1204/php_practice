<?php

declare(strict_types=1);

/**
 * Phase 2.4: エラーハンドリングと例外処理 - 実践演習
 *
 * この演習では、エラーハンドリングと例外処理を実践的に学習します。
 *
 * 演習内容:
 * 1. バリデーションシステム（カスタム例外の活用）
 * 2. ファイル処理システム（エラーハンドリングとリソース管理）
 * 3. データベースシステム（例外チェーンと適切なエラー処理）
 */

echo "=== Phase 2.4: エラーハンドリングと例外処理 - 実践演習 ===\n\n";

// ============================================================
// 演習1: バリデーションシステム
// ============================================================

echo "--- 演習1: バリデーションシステム ---\n\n";

/**
 * バリデーション基底例外
 */
abstract class ValidationException extends Exception
{
    /**
     * コンストラクタ
     *
     * @param string $field フィールド名
     * @param string $message エラーメッセージ
     */
    public function __construct(
        protected string $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * フィールド名を取得
     *
     * @return string フィールド名
     */
    public function getField(): string
    {
        return $this->field;
    }
}

/**
 * 必須フィールドエラー
 */
class RequiredFieldException extends ValidationException
{
    public function __construct(string $field)
    {
        parent::__construct($field, "{$field} は必須です");
    }
}

/**
 * 最小文字数エラー
 */
class MinLengthException extends ValidationException
{
    public function __construct(string $field, int $minLength)
    {
        parent::__construct(
            $field,
            "{$field} は{$minLength}文字以上である必要があります"
        );
    }
}

/**
 * 最大文字数エラー
 */
class MaxLengthException extends ValidationException
{
    public function __construct(string $field, int $maxLength)
    {
        parent::__construct(
            $field,
            "{$field} は{$maxLength}文字以内である必要があります"
        );
    }
}

/**
 * メールアドレス形式エラー
 */
class InvalidEmailException extends ValidationException
{
    public function __construct(string $field)
    {
        parent::__construct(
            $field,
            "{$field} のメールアドレス形式が正しくありません"
        );
    }
}

/**
 * 数値範囲エラー
 */
class OutOfRangeException extends ValidationException
{
    public function __construct(string $field, int $min, int $max)
    {
        parent::__construct(
            $field,
            "{$field} は{$min}から{$max}の範囲である必要があります"
        );
    }
}

/**
 * バリデーター
 */
class Validator
{
    /** @var array<string> バリデーションエラーのリスト */
    private array $errors = [];

    /**
     * 必須チェック
     *
     * @param string $field フィールド名
     * @param mixed $value 値
     * @throws RequiredFieldException
     */
    public function required(string $field, mixed $value): void
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            throw new RequiredFieldException($field);
        }
    }

    /**
     * 最小文字数チェック
     *
     * @param string $field フィールド名
     * @param string $value 値
     * @param int $minLength 最小文字数
     * @throws MinLengthException
     */
    public function minLength(string $field, string $value, int $minLength): void
    {
        if (mb_strlen($value) < $minLength) {
            throw new MinLengthException($field, $minLength);
        }
    }

    /**
     * 最大文字数チェック
     *
     * @param string $field フィールド名
     * @param string $value 値
     * @param int $maxLength 最大文字数
     * @throws MaxLengthException
     */
    public function maxLength(string $field, string $value, int $maxLength): void
    {
        if (mb_strlen($value) > $maxLength) {
            throw new MaxLengthException($field, $maxLength);
        }
    }

    /**
     * メールアドレス形式チェック
     *
     * @param string $field フィールド名
     * @param string $value 値
     * @throws InvalidEmailException
     */
    public function email(string $field, string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($field);
        }
    }

    /**
     * 数値範囲チェック
     *
     * @param string $field フィールド名
     * @param int $value 値
     * @param int $min 最小値
     * @param int $max 最大値
     * @throws OutOfRangeException
     */
    public function range(string $field, int $value, int $min, int $max): void
    {
        if ($value < $min || $value > $max) {
            throw new OutOfRangeException($field, $min, $max);
        }
    }

    /**
     * 複数のバリデーションを実行
     *
     * @param array<array{callable, array}> $validations バリデーションのリスト
     * @return array<string> エラーメッセージのリスト
     */
    public function validateAll(array $validations): array
    {
        $this->errors = [];

        foreach ($validations as [$validator, $args]) {
            try {
                $validator(...$args);
            } catch (ValidationException $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        return $this->errors;
    }

    /**
     * エラーがあるかチェック
     *
     * @return bool エラーがある場合true
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * エラーメッセージを取得
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

/**
 * ユーザー登録フォーム
 */
class UserRegistrationForm
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * ユーザー登録データを検証
     *
     * @param array $data フォームデータ
     * @return array 検証結果
     */
    public function validate(array $data): array
    {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $age = $data['age'] ?? 0;
        $password = $data['password'] ?? '';

        $errors = $this->validator->validateAll([
            // 名前の検証
            [$this->validator->required(...), ['名前', $name]],
            [$this->validator->minLength(...), ['名前', $name, 2]],
            [$this->validator->maxLength(...), ['名前', $name, 50]],

            // メールアドレスの検証
            [$this->validator->required(...), ['メールアドレス', $email]],
            [$this->validator->email(...), ['メールアドレス', $email]],

            // 年齢の検証
            [$this->validator->required(...), ['年齢', $age]],
            [$this->validator->range(...), ['年齢', (int)$age, 18, 120]],

            // パスワードの検証
            [$this->validator->required(...), ['パスワード', $password]],
            [$this->validator->minLength(...), ['パスワード', $password, 8]],
        ]);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}

echo "【ユーザー登録フォームのバリデーション】\n\n";

$form = new UserRegistrationForm();

// テストケース1: 正常なデータ
$validData = [
    'name' => '山田太郎',
    'email' => 'yamada@example.com',
    'age' => 25,
    'password' => 'securePassword123',
];

$result = $form->validate($validData);
echo "テスト1（正常なデータ）:\n";
if ($result['valid']) {
    echo "✅ バリデーション成功\n";
} else {
    echo "❌ バリデーション失敗:\n";
    foreach ($result['errors'] as $error) {
        echo "  - {$error}\n";
    }
}
echo "\n";

// テストケース2: エラーがあるデータ
$invalidData = [
    'name' => 'A', // 短すぎる
    'email' => 'invalid-email', // 形式が不正
    'age' => 15, // 範囲外
    'password' => 'short', // 短すぎる
];

$result = $form->validate($invalidData);
echo "テスト2（エラーがあるデータ）:\n";
if ($result['valid']) {
    echo "✅ バリデーション成功\n";
} else {
    echo "❌ バリデーション失敗:\n";
    foreach ($result['errors'] as $error) {
        echo "  - {$error}\n";
    }
}
echo "\n";

// ============================================================
// 演習2: ファイル処理システム
// ============================================================

echo "\n--- 演習2: ファイル処理システム ---\n\n";

/**
 * ファイル例外
 */
class FileException extends Exception {}

/**
 * ファイルが見つからない例外
 */
class FileNotFoundException extends FileException
{
    public function __construct(string $filename)
    {
        parent::__construct("ファイルが見つかりません: {$filename}");
    }
}

/**
 * ファイル読み込みエラー例外
 */
class FileReadException extends FileException
{
    public function __construct(string $filename, string $reason = '')
    {
        $message = "ファイルの読み込みに失敗しました: {$filename}";
        if ($reason) {
            $message .= " ({$reason})";
        }
        parent::__construct($message);
    }
}

/**
 * ファイル書き込みエラー例外
 */
class FileWriteException extends FileException
{
    public function __construct(string $filename, string $reason = '')
    {
        $message = "ファイルの書き込みに失敗しました: {$filename}";
        if ($reason) {
            $message .= " ({$reason})";
        }
        parent::__construct($message);
    }
}

/**
 * ファイルマネージャー
 */
class FileManager
{
    /**
     * ファイルを読み込む
     *
     * @param string $filename ファイル名
     * @return string ファイルの内容
     * @throws FileNotFoundException
     * @throws FileReadException
     */
    public function read(string $filename): string
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        if (!is_readable($filename)) {
            throw new FileReadException($filename, '読み込み権限がありません');
        }

        $content = file_get_contents($filename);

        if ($content === false) {
            throw new FileReadException($filename);
        }

        return $content;
    }

    /**
     * ファイルに書き込む
     *
     * @param string $filename ファイル名
     * @param string $content 内容
     * @throws FileWriteException
     */
    public function write(string $filename, string $content): void
    {
        $dir = dirname($filename);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new FileWriteException($filename, 'ディレクトリを作成できません');
            }
        }

        if (file_exists($filename) && !is_writable($filename)) {
            throw new FileWriteException($filename, '書き込み権限がありません');
        }

        $result = file_put_contents($filename, $content);

        if ($result === false) {
            throw new FileWriteException($filename);
        }
    }

    /**
     * ファイルを安全に読み込む（エラーハンドラを使用）
     *
     * @param string $filename ファイル名
     * @return string ファイルの内容
     * @throws FileException
     */
    public function safeRead(string $filename): string
    {
        // エラーを例外に変換するハンドラ
        set_error_handler(function (int $errno, string $errstr) use ($filename): bool {
            throw new FileReadException($filename, $errstr);
        });

        try {
            if (!file_exists($filename)) {
                throw new FileNotFoundException($filename);
            }

            $content = file_get_contents($filename);

            if ($content === false) {
                throw new FileReadException($filename);
            }

            return $content;
        } finally {
            // エラーハンドラを元に戻す
            restore_error_handler();
        }
    }

    /**
     * JSON ファイルを読み込む
     *
     * @param string $filename ファイル名
     * @return array JSONデータ
     * @throws FileException
     * @throws RuntimeException JSON のパースに失敗した場合
     */
    public function readJson(string $filename): array
    {
        $content = $this->read($filename);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "JSONのパースに失敗しました: " . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * JSON ファイルに書き込む
     *
     * @param string $filename ファイル名
     * @param array $data データ
     * @throws FileException
     */
    public function writeJson(string $filename, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException(
                "JSONのエンコードに失敗しました: " . json_last_error_msg()
            );
        }

        $this->write($filename, $json);
    }
}

echo "【ファイルマネージャーのテスト】\n\n";

$fileManager = new FileManager();
$testFile = '/tmp/test_file.txt';
$testJsonFile = '/tmp/test_data.json';

// テスト1: ファイル書き込みと読み込み
try {
    echo "テスト1: ファイルの書き込みと読み込み\n";
    $fileManager->write($testFile, "Hello, World!\n");
    echo "✅ ファイルを書き込みました: {$testFile}\n";

    $content = $fileManager->read($testFile);
    echo "✅ ファイルを読み込みました: {$content}";
} catch (FileException $e) {
    echo "❌ エラー: {$e->getMessage()}\n";
}

echo "\n";

// テスト2: 存在しないファイルの読み込み
try {
    echo "テスト2: 存在しないファイルの読み込み\n";
    $fileManager->read('/nonexistent/file.txt');
} catch (FileNotFoundException $e) {
    echo "✅ 期待通りの例外: {$e->getMessage()}\n";
} catch (FileException $e) {
    echo "❌ 予期しないエラー: {$e->getMessage()}\n";
}

echo "\n";

// テスト3: JSON ファイルの書き込みと読み込み
try {
    echo "テスト3: JSONファイルの書き込みと読み込み\n";
    $data = [
        'name' => '太郎',
        'age' => 25,
        'hobbies' => ['読書', 'プログラミング', '旅行'],
    ];

    $fileManager->writeJson($testJsonFile, $data);
    echo "✅ JSONファイルを書き込みました\n";

    $loadedData = $fileManager->readJson($testJsonFile);
    echo "✅ JSONファイルを読み込みました\n";
    echo "データ: " . print_r($loadedData, true);
} catch (FileException | RuntimeException $e) {
    echo "❌ エラー: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// 演習3: データベースシステム
// ============================================================

echo "\n--- 演習3: データベースシステム ---\n\n";

/**
 * データベース例外
 */
class DatabaseException extends Exception {}

/**
 * 接続エラー例外
 */
class ConnectionException extends DatabaseException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct("データベース接続エラー: {$message}", 0, $previous);
    }
}

/**
 * クエリエラー例外
 */
class QueryException extends DatabaseException
{
    public function __construct(
        private string $query,
        string $message,
        ?\Throwable $previous = null
    ) {
        parent::__construct("クエリ実行エラー: {$message}", 0, $previous);
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}

/**
 * レコードが見つからない例外
 */
class RecordNotFoundException extends DatabaseException
{
    public function __construct(string $table, int|string $id)
    {
        parent::__construct("レコードが見つかりません: {$table} (ID: {$id})");
    }
}

/**
 * シンプルなデータベースクラス（SQLiteを使用）
 */
class Database
{
    private ?\PDO $connection = null;

    /**
     * コンストラクタ
     *
     * @param string $dsn データソース名
     */
    public function __construct(
        private string $dsn,
    ) {}

    /**
     * データベースに接続
     *
     * @throws ConnectionException
     */
    public function connect(): void
    {
        if ($this->connection !== null) {
            return;
        }

        try {
            $this->connection = new PDO($this->dsn);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), $e);
        }
    }

    /**
     * クエリを実行
     *
     * @param string $query SQLクエリ
     * @param array $params パラメータ
     * @return array 結果
     * @throws QueryException
     */
    public function query(string $query, array $params = []): array
    {
        $this->connect();

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new QueryException($query, $e->getMessage(), $e);
        }
    }

    /**
     * 挿入を実行
     *
     * @param string $query SQLクエリ
     * @param array $params パラメータ
     * @return int 最後に挿入されたID
     * @throws QueryException
     */
    public function insert(string $query, array $params = []): int
    {
        $this->connect();

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return (int)$this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new QueryException($query, $e->getMessage(), $e);
        }
    }

    /**
     * レコードを検索
     *
     * @param string $table テーブル名
     * @param int $id ID
     * @return array レコード
     * @throws RecordNotFoundException
     */
    public function find(string $table, int $id): array
    {
        $results = $this->query("SELECT * FROM {$table} WHERE id = ?", [$id]);

        if (empty($results)) {
            throw new RecordNotFoundException($table, $id);
        }

        return $results[0];
    }

    /**
     * 接続をクローズ
     */
    public function close(): void
    {
        $this->connection = null;
    }
}

echo "【データベースシステムのテスト】\n\n";

$db = new Database('sqlite::memory:');

try {
    // テーブルを作成
    echo "テスト1: テーブルの作成\n";
    $db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');
    echo "✅ テーブルを作成しました\n\n";

    // レコードを挿入
    echo "テスト2: レコードの挿入\n";
    $id = $db->insert(
        'INSERT INTO users (name, email) VALUES (?, ?)',
        ['太郎', 'taro@example.com']
    );
    echo "✅ レコードを挿入しました (ID: {$id})\n\n";

    // レコードを検索
    echo "テスト3: レコードの検索\n";
    $user = $db->find('users', $id);
    echo "✅ レコードを取得しました\n";
    echo "名前: {$user['name']}, メール: {$user['email']}\n\n";

    // 存在しないレコードを検索
    echo "テスト4: 存在しないレコードの検索\n";
    try {
        $db->find('users', 999);
    } catch (RecordNotFoundException $e) {
        echo "✅ 期待通りの例外: {$e->getMessage()}\n\n";
    }

    // 不正なクエリ
    echo "テスト5: 不正なクエリ\n";
    try {
        $db->query('SELECT * FROM nonexistent_table');
    } catch (QueryException $e) {
        echo "✅ 期待通りの例外: {$e->getMessage()}\n";
        echo "クエリ: {$e->getQuery()}\n";

        // 元の例外を取得
        $previous = $e->getPrevious();
        if ($previous) {
            echo "原因: " . $previous->getMessage() . "\n";
        }
    }

} catch (DatabaseException $e) {
    echo "❌ データベースエラー: {$e->getMessage()}\n";
} finally {
    $db->close();
    echo "\n接続をクローズしました\n";
}

echo "\n";

// ============================================================
// まとめ
// ============================================================

echo "\n=== 演習のまとめ ===\n\n";

echo "学習した内容:\n";
echo "1. カスタム例外クラスの階層構造（ValidationException, FileException, DatabaseException）\n";
echo "2. 複数のバリデーションを実行し、エラーを収集する方法\n";
echo "3. ファイル操作でのエラーハンドリングとリソース管理（finally）\n";
echo "4. エラーを例外に変換する set_error_handler の活用\n";
echo "5. 例外チェーンで元の例外を保持する方法\n";
echo "6. 適切な例外クラスを定義して用途別に使い分ける\n\n";

echo "ベストプラクティス:\n";
echo "- 例外クラスは継承階層で整理する\n";
echo "- 例外メッセージは具体的で分かりやすく\n";
echo "- finally でリソース（ファイル、DB接続）を必ず解放\n";
echo "- 例外チェーンで原因を追跡できるようにする\n";
echo "- ビジネスロジックに応じた適切な例外を定義する\n\n";

echo "=== すべての演習が完了しました ===\n";
