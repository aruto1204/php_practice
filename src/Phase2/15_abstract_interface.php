<?php

declare(strict_types=1);

/**
 * Phase 2.2: 抽象クラスとインターフェース
 *
 * このファイルでは、PHPの抽象クラスとインターフェースを学習します。
 *
 * 学習内容:
 * 1. 抽象クラス（abstract class）
 * 2. 抽象メソッド（abstract method）
 * 3. インターフェース（interface）
 * 4. 複数のインターフェースの実装
 * 5. インターフェースの継承
 * 6. 抽象クラスとインターフェースの使い分け
 */

echo "=== 1. 抽象クラスの基本 ===" . PHP_EOL;

/**
 * 抽象クラス - データベース接続
 *
 * 抽象クラスは直接インスタンス化できません。
 * サブクラスで抽象メソッドを実装する必要があります。
 */
abstract class DatabaseConnection
{
    protected string $host;
    protected string $database;
    protected bool $connected = false;

    /**
     * コンストラクタ
     *
     * @param string $host ホスト
     * @param string $database データベース名
     */
    public function __construct(string $host, string $database)
    {
        $this->host = $host;
        $this->database = $database;
    }

    /**
     * 接続する（抽象メソッド - サブクラスで実装が必須）
     *
     * @return bool 成功の場合true
     */
    abstract public function connect(): bool;

    /**
     * 切断する（抽象メソッド - サブクラスで実装が必須）
     *
     * @return void
     */
    abstract public function disconnect(): void;

    /**
     * クエリを実行する（抽象メソッド - サブクラスで実装が必須）
     *
     * @param string $query クエリ
     * @return array<mixed> 結果
     */
    abstract public function query(string $query): array;

    /**
     * 接続されているかチェックする（具象メソッド）
     *
     * @return bool 接続されている場合true
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * 接続情報を取得する（具象メソッド）
     *
     * @return string 接続情報
     */
    public function getConnectionInfo(): string
    {
        return "{$this->host}/{$this->database}";
    }
}

/**
 * MySQL接続クラス（DatabaseConnectionを継承）
 */
class MySQLConnection extends DatabaseConnection
{
    /**
     * 接続する
     *
     * @return bool 成功の場合true
     */
    public function connect(): bool
    {
        echo "[MySQL] {$this->getConnectionInfo()} に接続しました" . PHP_EOL;
        $this->connected = true;
        return true;
    }

    /**
     * 切断する
     *
     * @return void
     */
    public function disconnect(): void
    {
        echo "[MySQL] 接続を切断しました" . PHP_EOL;
        $this->connected = false;
    }

    /**
     * クエリを実行する
     *
     * @param string $query クエリ
     * @return array<mixed> 結果
     */
    public function query(string $query): array
    {
        echo "[MySQL] クエリ実行: {$query}" . PHP_EOL;
        return ["id" => 1, "name" => "テストデータ"];
    }
}

/**
 * PostgreSQL接続クラス（DatabaseConnectionを継承）
 */
class PostgreSQLConnection extends DatabaseConnection
{
    /**
     * 接続する
     *
     * @return bool 成功の場合true
     */
    public function connect(): bool
    {
        echo "[PostgreSQL] {$this->getConnectionInfo()} に接続しました" . PHP_EOL;
        $this->connected = true;
        return true;
    }

    /**
     * 切断する
     *
     * @return void
     */
    public function disconnect(): void
    {
        echo "[PostgreSQL] 接続を切断しました" . PHP_EOL;
        $this->connected = false;
    }

    /**
     * クエリを実行する
     *
     * @param string $query クエリ
     * @return array<mixed> 結果
     */
    public function query(string $query): array
    {
        echo "[PostgreSQL] クエリ実行: {$query}" . PHP_EOL;
        return ["id" => 2, "name" => "PostgreSQLデータ"];
    }
}

// 抽象クラスの使用例
// $db = new DatabaseConnection("localhost", "test"); // エラー！抽象クラスはインスタンス化できない

$mysql = new MySQLConnection("localhost", "test_db");
$mysql->connect();
$result = $mysql->query("SELECT * FROM users");
echo "結果: " . print_r($result, true) . PHP_EOL;
$mysql->disconnect();

echo PHP_EOL;

$pgsql = new PostgreSQLConnection("localhost", "test_db");
$pgsql->connect();
$pgsql->query("SELECT * FROM users");
$pgsql->disconnect();

echo PHP_EOL;

// =============================================================================

echo "=== 2. インターフェースの基本 ===" . PHP_EOL;

/**
 * ロガーインターフェース
 *
 * インターフェースは、クラスが実装すべきメソッドの契約を定義します。
 */
interface LoggerInterface
{
    /**
     * ログを記録する
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @return void
     */
    public function log(string $level, string $message): void;

    /**
     * 情報ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function info(string $message): void;

    /**
     * エラーログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function error(string $message): void;
}

/**
 * ファイルロガー（LoggerInterfaceを実装）
 */
class FileLogger implements LoggerInterface
{
    /**
     * コンストラクタ
     *
     * @param string $filePath ファイルパス
     */
    public function __construct(
        private string $filePath,
    ) {
    }

    /**
     * ログを記録する
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @return void
     */
    public function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[FileLogger:{$this->filePath}] [{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    }

    /**
     * 情報ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    /**
     * エラーログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }
}

/**
 * コンソールロガー（LoggerInterfaceを実装）
 */
class ConsoleLogger implements LoggerInterface
{
    /**
     * ログを記録する
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @return void
     */
    public function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[Console] [{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    }

    /**
     * 情報ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    /**
     * エラーログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }
}

// インターフェースの使用例
$fileLogger = new FileLogger('/var/log/app.log');
$fileLogger->info('アプリケーションを開始しました');
$fileLogger->error('エラーが発生しました');

echo PHP_EOL;

$consoleLogger = new ConsoleLogger();
$consoleLogger->info('処理を実行中...');
$consoleLogger->error('処理に失敗しました');

echo PHP_EOL;

// =============================================================================

echo "=== 3. 複数のインターフェースの実装 ===" . PHP_EOL;

/**
 * シリアライズ可能インターフェース
 */
interface Serializable
{
    /**
     * 配列に変換する
     *
     * @return array<string, mixed> 配列
     */
    public function toArray(): array;

    /**
     * JSON文字列に変換する
     *
     * @return string JSON文字列
     */
    public function toJson(): string;
}

/**
 * バリデータブルインターフェース
 */
interface Validatable
{
    /**
     * バリデーションを実行する
     *
     * @return bool バリデーション成功の場合true
     */
    public function validate(): bool;

    /**
     * エラーメッセージを取得する
     *
     * @return array<string> エラーメッセージ
     */
    public function getErrors(): array;
}

/**
 * ユーザークラス（複数のインターフェースを実装）
 */
class User implements Serializable, Validatable
{
    /**
     * @var array<string> エラーメッセージ
     */
    private array $errors = [];

    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param string $email メールアドレス
     * @param int $age 年齢
     */
    public function __construct(
        private string $name,
        private string $email,
        private int $age,
    ) {
    }

    /**
     * 配列に変換する
     *
     * @return array<string, mixed> 配列
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
        ];
    }

    /**
     * JSON文字列に変換する
     *
     * @return string JSON文字列
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * バリデーションを実行する
     *
     * @return bool バリデーション成功の場合true
     */
    public function validate(): bool
    {
        $this->errors = [];

        if (empty($this->name)) {
            $this->errors[] = "名前は必須です";
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "有効なメールアドレスを入力してください";
        }

        if ($this->age < 0 || $this->age > 150) {
            $this->errors[] = "年齢は0〜150の範囲で入力してください";
        }

        return empty($this->errors);
    }

    /**
     * エラーメッセージを取得する
     *
     * @return array<string> エラーメッセージ
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

$user = new User("山田太郎", "yamada@example.com", 28);

if ($user->validate()) {
    echo "バリデーション成功！" . PHP_EOL;
    echo "配列: " . print_r($user->toArray(), true) . PHP_EOL;
    echo "JSON: " . $user->toJson() . PHP_EOL;
} else {
    echo "バリデーションエラー:" . PHP_EOL;
    foreach ($user->getErrors() as $error) {
        echo "- {$error}" . PHP_EOL;
    }
}

echo PHP_EOL;

$invalidUser = new User("", "invalid-email", 200);
if (!$invalidUser->validate()) {
    echo "バリデーションエラー:" . PHP_EOL;
    foreach ($invalidUser->getErrors() as $error) {
        echo "- {$error}" . PHP_EOL;
    }
}

echo PHP_EOL;

// =============================================================================

echo "=== 4. インターフェースの継承 ===" . PHP_EOL;

/**
 * 基本的なリポジトリインターフェース
 */
interface RepositoryInterface
{
    /**
     * IDで検索する
     *
     * @param int $id ID
     * @return array<string, mixed>|null データ
     */
    public function findById(int $id): ?array;

    /**
     * すべて取得する
     *
     * @return array<int, array<string, mixed>> データ配列
     */
    public function findAll(): array;
}

/**
 * 書き込み可能なリポジトリインターフェース（RepositoryInterfaceを継承）
 */
interface WritableRepositoryInterface extends RepositoryInterface
{
    /**
     * 保存する
     *
     * @param array<string, mixed> $data データ
     * @return int 保存されたID
     */
    public function save(array $data): int;

    /**
     * 削除する
     *
     * @param int $id ID
     * @return bool 成功の場合true
     */
    public function delete(int $id): bool;
}

/**
 * ユーザーリポジトリ（WritableRepositoryInterfaceを実装）
 */
class UserRepository implements WritableRepositoryInterface
{
    /**
     * @var array<int, array<string, mixed>> ユーザーデータ
     */
    private array $users = [];

    /**
     * @var int 次のID
     */
    private int $nextId = 1;

    /**
     * IDで検索する
     *
     * @param int $id ID
     * @return array<string, mixed>|null データ
     */
    public function findById(int $id): ?array
    {
        return $this->users[$id] ?? null;
    }

    /**
     * すべて取得する
     *
     * @return array<int, array<string, mixed>> データ配列
     */
    public function findAll(): array
    {
        return array_values($this->users);
    }

    /**
     * 保存する
     *
     * @param array<string, mixed> $data データ
     * @return int 保存されたID
     */
    public function save(array $data): int
    {
        $id = $this->nextId++;
        $this->users[$id] = array_merge(['id' => $id], $data);
        return $id;
    }

    /**
     * 削除する
     *
     * @param int $id ID
     * @return bool 成功の場合true
     */
    public function delete(int $id): bool
    {
        if (!isset($this->users[$id])) {
            return false;
        }

        unset($this->users[$id]);
        return true;
    }
}

$repository = new UserRepository();

// データを保存
$id1 = $repository->save(['name' => '山田太郎', 'email' => 'yamada@example.com']);
$id2 = $repository->save(['name' => '佐藤花子', 'email' => 'sato@example.com']);

echo "保存されたID: {$id1}, {$id2}" . PHP_EOL;

// すべて取得
echo "すべてのユーザー:" . PHP_EOL;
foreach ($repository->findAll() as $user) {
    echo "- ID{$user['id']}: {$user['name']} ({$user['email']})" . PHP_EOL;
}

echo PHP_EOL;

// IDで検索
$user = $repository->findById($id1);
if ($user !== null) {
    echo "ID{$id1}のユーザー: {$user['name']}" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================

echo "=== 5. 抽象クラスとインターフェースの組み合わせ ===" . PHP_EOL;

/**
 * キャッシュインターフェース
 */
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): bool;
    public function clear(): void;
}

/**
 * 抽象キャッシュクラス（CacheInterfaceを実装）
 */
abstract class AbstractCache implements CacheInterface
{
    /**
     * キーのプレフィックスを取得する
     *
     * @param string $key キー
     * @return string プレフィックス付きキー
     */
    protected function getPrefixedKey(string $key): string
    {
        return "cache:" . $key;
    }

    /**
     * ログを出力する
     *
     * @param string $message メッセージ
     * @return void
     */
    protected function log(string $message): void
    {
        echo "[" . static::class . "] {$message}" . PHP_EOL;
    }

    /**
     * キャッシュをクリアする（デフォルト実装）
     *
     * @return void
     */
    public function clear(): void
    {
        $this->log("キャッシュをクリアしました");
    }
}

/**
 * メモリキャッシュ（AbstractCacheを継承）
 */
class MemoryCache extends AbstractCache
{
    /**
     * @var array<string, mixed> キャッシュデータ
     */
    private array $cache = [];

    /**
     * 取得する
     *
     * @param string $key キー
     * @return mixed 値
     */
    public function get(string $key): mixed
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $value = $this->cache[$prefixedKey] ?? null;
        $this->log("取得: {$key} = " . var_export($value, true));
        return $value;
    }

    /**
     * 設定する
     *
     * @param string $key キー
     * @param mixed $value 値
     * @param int $ttl 有効期限（秒）
     * @return void
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $this->cache[$prefixedKey] = $value;
        $this->log("設定: {$key} = " . var_export($value, true) . " (TTL: {$ttl}秒)");
    }

    /**
     * 削除する
     *
     * @param string $key キー
     * @return bool 成功の場合true
     */
    public function delete(string $key): bool
    {
        $prefixedKey = $this->getPrefixedKey($key);
        if (isset($this->cache[$prefixedKey])) {
            unset($this->cache[$prefixedKey]);
            $this->log("削除: {$key}");
            return true;
        }
        return false;
    }

    /**
     * キャッシュをクリアする
     *
     * @return void
     */
    public function clear(): void
    {
        $this->cache = [];
        parent::clear();
    }
}

$cache = new MemoryCache();
$cache->set('user:1', ['name' => '山田太郎', 'age' => 28]);
$cache->set('user:2', ['name' => '佐藤花子', 'age' => 25], 7200);

$user1 = $cache->get('user:1');
$user2 = $cache->get('user:2');

$cache->delete('user:1');
$cache->get('user:1');

$cache->clear();

echo PHP_EOL;

// =============================================================================

echo "=== 6. タイプヒンティングでの活用 ===" . PHP_EOL;

/**
 * 通知サービスインターフェース
 */
interface NotificationServiceInterface
{
    public function send(string $to, string $message): bool;
}

/**
 * メール通知サービス
 */
class EmailNotificationService implements NotificationServiceInterface
{
    public function send(string $to, string $message): bool
    {
        echo "[Email] To: {$to}, Message: {$message}" . PHP_EOL;
        return true;
    }
}

/**
 * SMS通知サービス
 */
class SmsNotificationService implements NotificationServiceInterface
{
    public function send(string $to, string $message): bool
    {
        echo "[SMS] To: {$to}, Message: {$message}" . PHP_EOL;
        return true;
    }
}

/**
 * 通知マネージャー
 */
class NotificationManager
{
    /**
     * コンストラクタ
     *
     * @param NotificationServiceInterface $service 通知サービス
     */
    public function __construct(
        private NotificationServiceInterface $service,
    ) {
    }

    /**
     * 通知を送信する
     *
     * @param string $to 送信先
     * @param string $message メッセージ
     * @return void
     */
    public function notify(string $to, string $message): void
    {
        echo "=== 通知を送信します ===" . PHP_EOL;
        $this->service->send($to, $message);
    }
}

// メール通知
$emailManager = new NotificationManager(new EmailNotificationService());
$emailManager->notify('user@example.com', 'こんにちは！');

echo PHP_EOL;

// SMS通知
$smsManager = new NotificationManager(new SmsNotificationService());
$smsManager->notify('090-1234-5678', '重要なお知らせです');

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "抽象クラスとインターフェースを学習しました！" . PHP_EOL;
echo "次は 16_traits_static.php でトレイトと静的メソッドを学習します。" . PHP_EOL;
