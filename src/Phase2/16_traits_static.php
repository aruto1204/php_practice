<?php

declare(strict_types=1);

/**
 * Phase 2.2: トレイトと静的メソッド・プロパティ
 *
 * このファイルでは、PHPのトレイトと静的メンバーを学習します。
 *
 * 学習内容:
 * 1. トレイト（trait）の基本
 * 2. 複数のトレイトの使用
 * 3. トレイトのメソッド競合解決
 * 4. 静的メソッド（static method）
 * 5. 静的プロパティ（static property）
 * 6. 遅延静的束縛（Late Static Bindings）
 */

echo "=== 1. トレイトの基本 ===" . PHP_EOL;

/**
 * タイムスタンプトレイト
 *
 * トレイトは、クラスに機能を追加するための再利用可能なコードの集まりです。
 */
trait Timestampable
{
    private string $createdAt;
    private string $updatedAt;

    /**
     * タイムスタンプを初期化する
     *
     * @return void
     */
    public function initializeTimestamps(): void
    {
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * 更新日時を更新する
     *
     * @return void
     */
    public function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * 作成日時を取得する
     *
     * @return string 作成日時
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * 更新日時を取得する
     *
     * @return string 更新日時
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}

/**
 * 記事クラス（Timestampableトレイトを使用）
 */
class Article
{
    use Timestampable;

    /**
     * コンストラクタ
     *
     * @param string $title タイトル
     * @param string $content 内容
     */
    public function __construct(
        private string $title,
        private string $content,
    ) {
        $this->initializeTimestamps();
    }

    /**
     * 内容を更新する
     *
     * @param string $content 新しい内容
     * @return void
     */
    public function updateContent(string $content): void
    {
        $this->content = $content;
        $this->touch();  // トレイトのメソッドを使用
    }

    /**
     * 情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        echo "タイトル: {$this->title}" . PHP_EOL;
        echo "作成日時: {$this->getCreatedAt()}" . PHP_EOL;
        echo "更新日時: {$this->getUpdatedAt()}" . PHP_EOL;
    }
}

$article = new Article("PHPの学習", "PHPは素晴らしい言語です");
$article->displayInfo();

sleep(1);  // 1秒待機

echo PHP_EOL . "内容を更新..." . PHP_EOL;
$article->updateContent("PHPは素晴らしいWeb開発言語です");
$article->displayInfo();

echo PHP_EOL;

// =============================================================================

echo "=== 2. 複数のトレイト ===" . PHP_EOL;

/**
 * ソフトデリートトレイト
 */
trait SoftDeletable
{
    private ?string $deletedAt = null;

    /**
     * ソフトデリートする
     *
     * @return void
     */
    public function softDelete(): void
    {
        $this->deletedAt = date('Y-m-d H:i:s');
    }

    /**
     * 復元する
     *
     * @return void
     */
    public function restore(): void
    {
        $this->deletedAt = null;
    }

    /**
     * 削除済みかチェックする
     *
     * @return bool 削除済みの場合true
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * 削除日時を取得する
     *
     * @return string|null 削除日時
     */
    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }
}

/**
 * UUIDトレイト
 */
trait HasUuid
{
    private string $uuid;

    /**
     * UUIDを初期化する
     *
     * @return void
     */
    public function initializeUuid(): void
    {
        $this->uuid = $this->generateUuid();
    }

    /**
     * UUIDを生成する
     *
     * @return string UUID
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * UUIDを取得する
     *
     * @return string UUID
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}

/**
 * ユーザークラス（複数のトレイトを使用）
 */
class User
{
    use Timestampable, SoftDeletable, HasUuid;

    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param string $email メールアドレス
     */
    public function __construct(
        private string $name,
        private string $email,
    ) {
        $this->initializeTimestamps();
        $this->initializeUuid();
    }

    /**
     * 情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        echo "UUID: {$this->getUuid()}" . PHP_EOL;
        echo "名前: {$this->name}" . PHP_EOL;
        echo "メール: {$this->email}" . PHP_EOL;
        echo "作成日時: {$this->getCreatedAt()}" . PHP_EOL;
        echo "削除済み: " . ($this->isDeleted() ? "はい" : "いいえ") . PHP_EOL;

        if ($this->isDeleted() && $this->getDeletedAt() !== null) {
            echo "削除日時: {$this->getDeletedAt()}" . PHP_EOL;
        }
    }
}

$user = new User("山田太郎", "yamada@example.com");
$user->displayInfo();

echo PHP_EOL . "ユーザーを削除..." . PHP_EOL;
$user->softDelete();
$user->displayInfo();

echo PHP_EOL . "ユーザーを復元..." . PHP_EOL;
$user->restore();
$user->displayInfo();

echo PHP_EOL;

// =============================================================================

echo "=== 3. トレイトのメソッド競合解決 ===" . PHP_EOL;

/**
 * ログトレイトA
 */
trait LoggerA
{
    public function log(string $message): void
    {
        echo "[LoggerA] {$message}" . PHP_EOL;
    }
}

/**
 * ログトレイトB
 */
trait LoggerB
{
    public function log(string $message): void
    {
        echo "[LoggerB] {$message}" . PHP_EOL;
    }
}

/**
 * サービスクラス（メソッド競合を解決）
 */
class Service
{
    use LoggerA, LoggerB {
        LoggerA::log insteadof LoggerB;  // LoggerAのlogを使用
        LoggerB::log as logB;  // LoggerBのlogをlogBとして使用
    }

    /**
     * 実行する
     *
     * @return void
     */
    public function execute(): void
    {
        $this->log("通常のログ");  // LoggerA::log
        $this->logB("LoggerBのログ");  // LoggerB::log
    }
}

$service = new Service();
$service->execute();

echo PHP_EOL;

// =============================================================================

echo "=== 4. 静的メソッド ===" . PHP_EOL;

/**
 * 文字列ヘルパークラス
 */
class StringHelper
{
    /**
     * 文字列を大文字に変換する
     *
     * @param string $str 文字列
     * @return string 大文字の文字列
     */
    public static function toUpper(string $str): string
    {
        return strtoupper($str);
    }

    /**
     * 文字列を小文字に変換する
     *
     * @param string $str 文字列
     * @return string 小文字の文字列
     */
    public static function toLower(string $str): string
    {
        return strtolower($str);
    }

    /**
     * 文字列をキャメルケースに変換する
     *
     * @param string $str 文字列
     * @return string キャメルケースの文字列
     */
    public static function toCamelCase(string $str): string
    {
        $str = str_replace(['-', '_'], ' ', $str);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return lcfirst($str);
    }

    /**
     * 文字列をスネークケースに変換する
     *
     * @param string $str 文字列
     * @return string スネークケースの文字列
     */
    public static function toSnakeCase(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1_$2', $str);
        return strtolower($str ?? '');
    }
}

// 静的メソッドの呼び出し
echo "大文字: " . StringHelper::toUpper("hello world") . PHP_EOL;
echo "小文字: " . StringHelper::toLower("HELLO WORLD") . PHP_EOL;
echo "キャメルケース: " . StringHelper::toCamelCase("hello_world_test") . PHP_EOL;
echo "スネークケース: " . StringHelper::toSnakeCase("helloWorldTest") . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 5. 静的プロパティ ===" . PHP_EOL;

/**
 * カウンタークラス
 */
class Counter
{
    /**
     * @var int カウント（静的プロパティ）
     */
    private static int $count = 0;

    /**
     * インスタンスID
     */
    private int $instanceId;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        self::$count++;
        $this->instanceId = self::$count;
        echo "Counterインスタンス#{$this->instanceId}が作成されました" . PHP_EOL;
    }

    /**
     * カウントを取得する
     *
     * @return int カウント
     */
    public static function getCount(): int
    {
        return self::$count;
    }

    /**
     * カウントをリセットする
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$count = 0;
        echo "カウントをリセットしました" . PHP_EOL;
    }

    /**
     * インスタンスIDを取得する
     *
     * @return int インスタンスID
     */
    public function getInstanceId(): int
    {
        return $this->instanceId;
    }
}

echo "現在のカウント: " . Counter::getCount() . PHP_EOL;

$counter1 = new Counter();
$counter2 = new Counter();
$counter3 = new Counter();

echo "現在のカウント: " . Counter::getCount() . PHP_EOL;

Counter::reset();
echo "現在のカウント: " . Counter::getCount() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 6. シングルトンパターン ===" . PHP_EOL;

/**
 * データベースクラス（シングルトン）
 */
class Database
{
    /**
     * @var Database|null インスタンス
     */
    private static ?Database $instance = null;

    /**
     * @var int 接続数カウンター
     */
    private static int $connectionCount = 0;

    /**
     * コンストラクタ（private）
     */
    private function __construct()
    {
        self::$connectionCount++;
        echo "Database接続を確立しました（接続数: " . self::$connectionCount . "）" . PHP_EOL;
    }

    /**
     * インスタンスを取得する
     *
     * @return Database インスタンス
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * クエリを実行する
     *
     * @param string $query クエリ
     * @return void
     */
    public function query(string $query): void
    {
        echo "クエリ実行: {$query}" . PHP_EOL;
    }

    /**
     * クローンを防ぐ
     */
    private function __clone()
    {
    }
}

// シングルトンの使用
$db1 = Database::getInstance();
$db1->query("SELECT * FROM users");

$db2 = Database::getInstance();
$db2->query("SELECT * FROM posts");

// $db1 と $db2 は同じインスタンス
echo "\$db1 === \$db2: " . var_export($db1 === $db2, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 7. 遅延静的束縛（Late Static Bindings） ===" . PHP_EOL;

/**
 * 基底モデルクラス
 */
class Model
{
    protected static string $table = 'models';

    /**
     * テーブル名を取得する（self使用）
     *
     * @return string テーブル名
     */
    public static function getTableSelf(): string
    {
        return self::$table;  // 常にModelクラスの$tableを返す
    }

    /**
     * テーブル名を取得する（static使用）
     *
     * @return string テーブル名
     */
    public static function getTableStatic(): string
    {
        return static::$table;  // 呼び出されたクラスの$tableを返す
    }

    /**
     * すべて取得する
     *
     * @return string メッセージ
     */
    public static function all(): string
    {
        return "SELECT * FROM " . static::$table;
    }
}

/**
 * ユーザーモデルクラス
 */
class UserModel extends Model
{
    protected static string $table = 'users';
}

/**
 * 投稿モデルクラス
 */
class PostModel extends Model
{
    protected static string $table = 'posts';
}

echo "Model::getTableSelf(): " . Model::getTableSelf() . PHP_EOL;
echo "Model::getTableStatic(): " . Model::getTableStatic() . PHP_EOL;

echo PHP_EOL;

echo "UserModel::getTableSelf(): " . UserModel::getTableSelf() . PHP_EOL;
echo "UserModel::getTableStatic(): " . UserModel::getTableStatic() . PHP_EOL;

echo PHP_EOL;

echo "PostModel::all(): " . PostModel::all() . PHP_EOL;
echo "UserModel::all(): " . UserModel::all() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 8. トレイトと静的メソッドの組み合わせ ===" . PHP_EOL;

/**
 * バリデータブルトレイト
 */
trait Validatable
{
    /**
     * @var array<string> エラーメッセージ
     */
    private static array $errors = [];

    /**
     * エラーを追加する
     *
     * @param string $error エラーメッセージ
     * @return void
     */
    protected static function addError(string $error): void
    {
        self::$errors[] = $error;
    }

    /**
     * エラーをクリアする
     *
     * @return void
     */
    protected static function clearErrors(): void
    {
        self::$errors = [];
    }

    /**
     * エラーを取得する
     *
     * @return array<string> エラーメッセージ
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * エラーがあるかチェックする
     *
     * @return bool エラーがある場合true
     */
    public static function hasErrors(): bool
    {
        return !empty(self::$errors);
    }
}

/**
 * フォームバリデータークラス
 */
class FormValidator
{
    use Validatable;

    /**
     * メールアドレスをバリデートする
     *
     * @param string $email メールアドレス
     * @return bool バリデーション成功の場合true
     */
    public static function validateEmail(string $email): bool
    {
        self::clearErrors();

        if (empty($email)) {
            self::addError("メールアドレスは必須です");
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::addError("有効なメールアドレスを入力してください");
            return false;
        }

        return true;
    }

    /**
     * パスワードをバリデートする
     *
     * @param string $password パスワード
     * @return bool バリデーション成功の場合true
     */
    public static function validatePassword(string $password): bool
    {
        self::clearErrors();

        if (strlen($password) < 8) {
            self::addError("パスワードは8文字以上である必要があります");
            return false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            self::addError("パスワードには大文字を含める必要があります");
            return false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            self::addError("パスワードには数字を含める必要があります");
            return false;
        }

        return true;
    }
}

// バリデーションの実行
if (FormValidator::validateEmail("yamada@example.com")) {
    echo "メールアドレスは有効です" . PHP_EOL;
} else {
    echo "エラー:" . PHP_EOL;
    foreach (FormValidator::getErrors() as $error) {
        echo "- {$error}" . PHP_EOL;
    }
}

echo PHP_EOL;

if (!FormValidator::validatePassword("weak")) {
    echo "パスワードのバリデーションエラー:" . PHP_EOL;
    foreach (FormValidator::getErrors() as $error) {
        echo "- {$error}" . PHP_EOL;
    }
}

echo PHP_EOL;

if (FormValidator::validatePassword("StrongPass123")) {
    echo "パスワードは有効です" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "トレイトと静的メソッド・プロパティを学習しました！" . PHP_EOL;
echo "次は exercises/07_oop_advanced_practice.php でOOP応用の実践演習を行います。" . PHP_EOL;
