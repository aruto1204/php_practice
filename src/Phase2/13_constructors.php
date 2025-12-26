<?php

declare(strict_types=1);

/**
 * Phase 2.1: コンストラクタとデストラクタ、コンストラクタプロモーション
 *
 * このファイルでは、PHPのコンストラクタとデストラクタ、
 * PHP 8のコンストラクタプロモーションを学習します。
 *
 * 学習内容:
 * 1. コンストラクタ（__construct）
 * 2. デストラクタ（__destruct）
 * 3. コンストラクタプロモーション（PHP 8.0+）
 * 4. readonlyプロパティ（PHP 8.1+）
 * 5. デフォルト引数とコンストラクタ
 */

echo "=== 1. コンストラクタの基本 ===" . PHP_EOL;

/**
 * 従来のコンストラクタを使用したUserクラス
 */
class User
{
    private string $name;
    private string $email;
    private int $age;

    /**
     * コンストラクタ
     *
     * @param string $name ユーザー名
     * @param string $email メールアドレス
     * @param int $age 年齢
     */
    public function __construct(string $name, string $email, int $age)
    {
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;

        echo "Userオブジェクトが作成されました: {$name}" . PHP_EOL;
    }

    /**
     * ユーザー情報を取得する
     *
     * @return string ユーザー情報
     */
    public function getInfo(): string
    {
        return "{$this->name} ({$this->email}) - {$this->age}歳";
    }

    /**
     * 名前を取得する
     *
     * @return string 名前
     */
    public function getName(): string
    {
        return $this->name;
    }
}

$user = new User("山田太郎", "yamada@example.com", 28);
echo $user->getInfo() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 2. コンストラクタプロモーション（PHP 8.0+） ===" . PHP_EOL;

/**
 * コンストラクタプロモーションを使用したProductクラス
 *
 * PHP 8.0から、コンストラクタの引数にアクセス修飾子を付けることで、
 * プロパティの宣言と初期化を同時に行うことができます。
 */
class Product
{
    /**
     * コンストラクタプロモーション
     *
     * @param string $name 商品名
     * @param float $price 価格
     * @param int $stock 在庫数
     */
    public function __construct(
        private string $name,
        private float $price,
        private int $stock = 0,  // デフォルト値を設定可能
    ) {
        echo "Productオブジェクトが作成されました: {$name}" . PHP_EOL;
    }

    /**
     * 商品情報を取得する
     *
     * @return string 商品情報
     */
    public function getInfo(): string
    {
        return "{$this->name} - " . number_format($this->price) . "円（在庫: {$this->stock}）";
    }

    /**
     * 価格を取得する
     *
     * @return float 価格
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * 在庫を追加する
     *
     * @param int $quantity 数量
     * @return void
     */
    public function addStock(int $quantity): void
    {
        $this->stock += $quantity;
    }
}

$product1 = new Product("ノートPC", 120000, 5);
echo $product1->getInfo() . PHP_EOL;

$product2 = new Product("マウス", 2500);  // stockはデフォルト値0
echo $product2->getInfo() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 3. readonlyプロパティ（PHP 8.1+） ===" . PHP_EOL;

/**
 * readonlyプロパティを使用したBookクラス
 *
 * readonlyプロパティは、コンストラクタで一度だけ値を設定でき、
 * その後は変更できません。
 */
class Book
{
    /**
     * コンストラクタ
     *
     * @param string $isbn ISBN（変更不可）
     * @param string $title タイトル（変更不可）
     * @param string $author 著者（変更不可）
     * @param float $price 価格
     */
    public function __construct(
        public readonly string $isbn,
        public readonly string $title,
        public readonly string $author,
        private float $price,
    ) {
    }

    /**
     * 価格を取得する
     *
     * @return float 価格
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * 価格を更新する（readonlyではないので変更可能）
     *
     * @param float $newPrice 新しい価格
     * @return void
     */
    public function updatePrice(float $newPrice): void
    {
        $this->price = $newPrice;
    }

    /**
     * 書籍情報を取得する
     *
     * @return string 書籍情報
     */
    public function getInfo(): string
    {
        return "{$this->title} / {$this->author} (ISBN: {$this->isbn}) - " . number_format($this->price) . "円";
    }
}

$book = new Book("978-4-123456-78-9", "PHPマスターガイド", "山田太郎", 3500);
echo $book->getInfo() . PHP_EOL;
echo "ISBN: {$book->isbn}" . PHP_EOL;  // readonlyプロパティは読み取り可能

// $book->isbn = "978-4-987654-32-1";  // エラー！readonlyプロパティは変更不可

$book->updatePrice(3200);
echo "価格更新後: " . $book->getInfo() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 4. デストラクタ ===" . PHP_EOL;

/**
 * デストラクタを持つDatabaseConnectionクラス
 */
class DatabaseConnection
{
    private string $connectionId;

    /**
     * コンストラクタ
     *
     * @param string $host ホスト
     * @param string $database データベース名
     */
    public function __construct(
        private string $host,
        private string $database,
    ) {
        $this->connectionId = uniqid("conn_");
        echo "[{$this->connectionId}] データベース接続を確立しました: {$host}/{$database}" . PHP_EOL;
    }

    /**
     * デストラクタ
     *
     * オブジェクトが破棄される際に自動的に呼ばれます
     */
    public function __destruct()
    {
        echo "[{$this->connectionId}] データベース接続を切断しました" . PHP_EOL;
    }

    /**
     * クエリを実行する（ダミー）
     *
     * @param string $query クエリ
     * @return void
     */
    public function query(string $query): void
    {
        echo "[{$this->connectionId}] クエリ実行: {$query}" . PHP_EOL;
    }
}

// スコープ内でオブジェクトを使用
{
    $db = new DatabaseConnection("localhost", "test_db");
    $db->query("SELECT * FROM users");
    // スコープを抜けるとデストラクタが呼ばれる
}

echo "スコープを抜けました" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 5. 名前付き引数とコンストラクタ ===" . PHP_EOL;

/**
 * 多くのオプションを持つEmailクラス
 */
class Email
{
    /**
     * コンストラクタ
     *
     * @param string $to 送信先
     * @param string $subject 件名
     * @param string $body 本文
     * @param string $from 送信元
     * @param string|null $cc CC
     * @param string|null $bcc BCC
     * @param bool $isHtml HTML形式かどうか
     * @param int $priority 優先度（1-5）
     */
    public function __construct(
        private string $to,
        private string $subject,
        private string $body,
        private string $from = "noreply@example.com",
        private ?string $cc = null,
        private ?string $bcc = null,
        private bool $isHtml = false,
        private int $priority = 3,
    ) {
    }

    /**
     * メール情報を表示する
     *
     * @return void
     */
    public function display(): void
    {
        echo "=== メール情報 ===" . PHP_EOL;
        echo "送信先: {$this->to}" . PHP_EOL;
        echo "送信元: {$this->from}" . PHP_EOL;
        echo "件名: {$this->subject}" . PHP_EOL;
        echo "本文: {$this->body}" . PHP_EOL;

        if ($this->cc !== null) {
            echo "CC: {$this->cc}" . PHP_EOL;
        }

        if ($this->bcc !== null) {
            echo "BCC: {$this->bcc}" . PHP_EOL;
        }

        echo "HTML形式: " . ($this->isHtml ? "はい" : "いいえ") . PHP_EOL;
        echo "優先度: {$this->priority}" . PHP_EOL;
    }
}

// 名前付き引数を使用して、必要な引数だけを指定
$email1 = new Email(
    to: "user@example.com",
    subject: "お知らせ",
    body: "こんにちは、これはテストメールです。",
);

$email1->display();

echo PHP_EOL;

// 順序を変えて指定することも可能
$email2 = new Email(
    body: "重要なお知らせがあります。",
    subject: "【重要】システムメンテナンスのお知らせ",
    to: "all@example.com",
    isHtml: true,
    priority: 1,
    cc: "manager@example.com",
);

$email2->display();

echo PHP_EOL;

// =============================================================================

echo "=== 6. バリデーション付きコンストラクタ ===" . PHP_EOL;

/**
 * バリデーションを含むPersonクラス
 */
class Person
{
    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param int $age 年齢
     * @param string $email メールアドレス
     * @throws InvalidArgumentException バリデーションエラー
     */
    public function __construct(
        private string $name,
        private int $age,
        private string $email,
    ) {
        // バリデーション
        if (empty($name)) {
            throw new InvalidArgumentException("名前は必須です");
        }

        if ($age < 0 || $age > 150) {
            throw new InvalidArgumentException("年齢は0〜150の範囲で指定してください");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("有効なメールアドレスを指定してください");
        }

        echo "Personオブジェクトが作成されました: {$name}" . PHP_EOL;
    }

    /**
     * 情報を取得する
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return "{$this->name} ({$this->age}歳) - {$this->email}";
    }
}

// 正常なケース
$person1 = new Person("田中一郎", 30, "tanaka@example.com");
echo $person1->getInfo() . PHP_EOL;

// エラーケースを試す
try {
    $person2 = new Person("", 30, "invalid-email");
} catch (InvalidArgumentException $e) {
    echo "エラー: {$e->getMessage()}" . PHP_EOL;
}

try {
    $person3 = new Person("山田太郎", 200, "yamada@example.com");
} catch (InvalidArgumentException $e) {
    echo "エラー: {$e->getMessage()}" . PHP_EOL;
}

try {
    $person4 = new Person("佐藤花子", 25, "invalid-email");
} catch (InvalidArgumentException $e) {
    echo "エラー: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================

echo "=== 7. ファクトリーメソッドパターン ===" . PHP_EOL;

/**
 * ファクトリーメソッドを持つDateRangeクラス
 */
class DateRange
{
    /**
     * コンストラクタ
     *
     * @param string $start 開始日
     * @param string $end 終了日
     */
    public function __construct(
        public readonly string $start,
        public readonly string $end,
    ) {
    }

    /**
     * 今日の日付範囲を作成する
     *
     * @return self
     */
    public static function today(): self
    {
        $date = date('Y-m-d');
        return new self($date, $date);
    }

    /**
     * 今週の日付範囲を作成する
     *
     * @return self
     */
    public static function thisWeek(): self
    {
        $start = date('Y-m-d', strtotime('monday this week'));
        $end = date('Y-m-d', strtotime('sunday this week'));
        return new self($start, $end);
    }

    /**
     * 今月の日付範囲を作成する
     *
     * @return self
     */
    public static function thisMonth(): self
    {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        return new self($start, $end);
    }

    /**
     * カスタム日付範囲を作成する
     *
     * @param string $start 開始日
     * @param string $end 終了日
     * @return self
     */
    public static function custom(string $start, string $end): self
    {
        return new self($start, $end);
    }

    /**
     * 日付範囲を表示する
     *
     * @return string 日付範囲
     */
    public function toString(): string
    {
        return "{$this->start} 〜 {$this->end}";
    }
}

// ファクトリーメソッドを使用してオブジェクトを作成
$today = DateRange::today();
echo "今日: {$today->toString()}" . PHP_EOL;

$thisWeek = DateRange::thisWeek();
echo "今週: {$thisWeek->toString()}" . PHP_EOL;

$thisMonth = DateRange::thisMonth();
echo "今月: {$thisMonth->toString()}" . PHP_EOL;

$custom = DateRange::custom('2025-01-01', '2025-12-31');
echo "カスタム: {$custom->toString()}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 8. Union型とコンストラクタ（PHP 8.0+） ===" . PHP_EOL;

/**
 * Union型を使用したConfigクラス
 */
class Config
{
    /**
     * コンストラクタ
     *
     * @param string $name 設定名
     * @param string|int|float|bool $value 設定値
     */
    public function __construct(
        private string $name,
        private string|int|float|bool $value,
    ) {
    }

    /**
     * 設定値を取得する
     *
     * @return string|int|float|bool 設定値
     */
    public function getValue(): string|int|float|bool
    {
        return $this->value;
    }

    /**
     * 設定情報を表示する
     *
     * @return void
     */
    public function display(): void
    {
        $type = gettype($this->value);
        $valueStr = is_bool($this->value) ? ($this->value ? 'true' : 'false') : (string)$this->value;
        echo "{$this->name} = {$valueStr} ({$type})" . PHP_EOL;
    }
}

$config1 = new Config("app_name", "MyApp");
$config1->display();

$config2 = new Config("max_users", 100);
$config2->display();

$config3 = new Config("tax_rate", 0.1);
$config3->display();

$config4 = new Config("debug_mode", true);
$config4->display();

echo PHP_EOL;

// =============================================================================

echo "=== 9. プロパティの初期化と型 ===" . PHP_EOL;

/**
 * さまざまなプロパティの初期化パターン
 */
class Settings
{
    // デフォルト値を持つプロパティ
    private string $language = "ja";
    private int $timeout = 30;
    private bool $cacheEnabled = true;

    // コンストラクタで初期化するプロパティ
    private array $options;

    /**
     * コンストラクタ
     *
     * @param array<string, mixed> $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * 言語を取得する
     *
     * @return string 言語
     */
    public function getLanguage(): string
    {
        return $this->options['language'] ?? $this->language;
    }

    /**
     * タイムアウトを取得する
     *
     * @return int タイムアウト
     */
    public function getTimeout(): int
    {
        return $this->options['timeout'] ?? $this->timeout;
    }

    /**
     * キャッシュが有効かチェックする
     *
     * @return bool キャッシュが有効な場合true
     */
    public function isCacheEnabled(): bool
    {
        return $this->options['cache_enabled'] ?? $this->cacheEnabled;
    }

    /**
     * すべての設定を表示する
     *
     * @return void
     */
    public function displayAll(): void
    {
        echo "言語: {$this->getLanguage()}" . PHP_EOL;
        echo "タイムアウト: {$this->getTimeout()}秒" . PHP_EOL;
        echo "キャッシュ: " . ($this->isCacheEnabled() ? "有効" : "無効") . PHP_EOL;
    }
}

$settings1 = new Settings();
echo "デフォルト設定:" . PHP_EOL;
$settings1->displayAll();

echo PHP_EOL;

$settings2 = new Settings([
    'language' => 'en',
    'timeout' => 60,
    'cache_enabled' => false,
]);
echo "カスタム設定:" . PHP_EOL;
$settings2->displayAll();

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "コンストラクタとデストラクタ、コンストラクタプロモーションを学習しました！" . PHP_EOL;
echo "次は exercises/06_oop_practice.php でOOPの実践演習を行います。" . PHP_EOL;
