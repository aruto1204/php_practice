<?php

declare(strict_types=1);

/**
 * Phase 2.1: オブジェクト指向プログラミング - 基礎
 *
 * このファイルでは、PHPのクラスとオブジェクトの基礎を学習します。
 *
 * 学習内容:
 * 1. クラスとオブジェクトの基本
 * 2. プロパティとメソッド
 * 3. アクセス修飾子（public, private, protected）
 * 4. $thisキーワード
 * 5. メソッドチェーン
 */

echo "=== 1. クラスとオブジェクトの基本 ===" . PHP_EOL;

/**
 * シンプルなUserクラス
 */
class User
{
    /**
     * ユーザー名
     */
    public string $name;

    /**
     * メールアドレス
     */
    public string $email;

    /**
     * 年齢
     */
    public int $age;
}

// オブジェクトの作成
$user1 = new User();
$user1->name = "山田太郎";
$user1->email = "yamada@example.com";
$user1->age = 28;

echo "ユーザー1:" . PHP_EOL;
echo "名前: {$user1->name}" . PHP_EOL;
echo "メール: {$user1->email}" . PHP_EOL;
echo "年齢: {$user1->age}" . PHP_EOL;

// 複数のオブジェクトを作成
$user2 = new User();
$user2->name = "佐藤花子";
$user2->email = "sato@example.com";
$user2->age = 25;

echo PHP_EOL . "ユーザー2:" . PHP_EOL;
echo "名前: {$user2->name}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 2. メソッドの追加 ===" . PHP_EOL;

/**
 * メソッドを持つPersonクラス
 */
class Person
{
    public string $name;
    public int $age;

    /**
     * 自己紹介メッセージを取得する
     *
     * @return string 自己紹介メッセージ
     */
    public function introduce(): string
    {
        return "こんにちは、{$this->name}です。{$this->age}歳です。";
    }

    /**
     * 成人かどうかを判定する
     *
     * @return bool 成人の場合true
     */
    public function isAdult(): bool
    {
        return $this->age >= 20;
    }

    /**
     * 年齢グループを取得する
     *
     * @return string 年齢グループ
     */
    public function getAgeGroup(): string
    {
        return match (true) {
            $this->age < 20 => "未成年",
            $this->age < 30 => "20代",
            $this->age < 40 => "30代",
            $this->age < 50 => "40代",
            default => "50代以上",
        };
    }
}

$person = new Person();
$person->name = "田中一郎";
$person->age = 32;

echo $person->introduce() . PHP_EOL;
echo "成人? " . ($person->isAdult() ? "はい" : "いいえ") . PHP_EOL;
echo "年齢グループ: {$person->getAgeGroup()}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 3. アクセス修飾子 ===" . PHP_EOL;

/**
 * アクセス修飾子を使用したBankAccountクラス
 */
class BankAccount
{
    /**
     * 口座番号（公開）
     */
    public string $accountNumber;

    /**
     * 口座名義（公開）
     */
    public string $accountHolder;

    /**
     * 残高（非公開）
     */
    private float $balance;

    /**
     * 残高を取得する
     *
     * @return float 残高
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * 残高を設定する
     *
     * @param float $amount 金額
     * @return void
     */
    public function setBalance(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("残高は0以上である必要があります");
        }
        $this->balance = $amount;
    }

    /**
     * 入金する
     *
     * @param float $amount 入金額
     * @return void
     */
    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("入金額は正の数である必要があります");
        }
        $this->balance += $amount;
    }

    /**
     * 出金する
     *
     * @param float $amount 出金額
     * @return bool 出金成功の場合true
     */
    public function withdraw(float $amount): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("出金額は正の数である必要があります");
        }

        if ($amount > $this->balance) {
            echo "エラー: 残高不足です" . PHP_EOL;
            return false;
        }

        $this->balance -= $amount;
        return true;
    }
}

$account = new BankAccount();
$account->accountNumber = "1234567890";
$account->accountHolder = "山田太郎";
$account->setBalance(100000);

echo "口座番号: {$account->accountNumber}" . PHP_EOL;
echo "名義: {$account->accountHolder}" . PHP_EOL;
echo "残高: " . number_format($account->getBalance()) . "円" . PHP_EOL;

// 入金
$account->deposit(50000);
echo "50,000円を入金しました" . PHP_EOL;
echo "残高: " . number_format($account->getBalance()) . "円" . PHP_EOL;

// 出金
if ($account->withdraw(30000)) {
    echo "30,000円を出金しました" . PHP_EOL;
    echo "残高: " . number_format($account->getBalance()) . "円" . PHP_EOL;
}

// 残高不足の出金を試みる
$account->withdraw(200000);

echo PHP_EOL;

// =============================================================================

echo "=== 4. protected修飾子 ===" . PHP_EOL;

/**
 * 基底クラス - 動物
 */
class Animal
{
    /**
     * 名前（protectedなので継承先でアクセス可能）
     */
    protected string $name;

    /**
     * 種類（protectedなので継承先でアクセス可能）
     */
    protected string $species;

    /**
     * 名前を設定する
     *
     * @param string $name 名前
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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

    /**
     * 情報を表示する
     *
     * @return string 動物の情報
     */
    public function getInfo(): string
    {
        return "{$this->name}（{$this->species}）";
    }
}

/**
 * 犬クラス（Animalを継承）
 */
class Dog extends Animal
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // protectedプロパティにアクセス可能
        $this->species = "犬";
    }

    /**
     * 吠える
     *
     * @return string 吠え声
     */
    public function bark(): string
    {
        return "{$this->name}が「ワンワン！」と吠えました";
    }
}

$dog = new Dog();
$dog->setName("ポチ");
echo $dog->getInfo() . PHP_EOL;
echo $dog->bark() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 5. メソッドチェーン ===" . PHP_EOL;

/**
 * メソッドチェーンを実装したQueryBuilderクラス
 */
class QueryBuilder
{
    private string $table = "";
    private array $columns = [];
    private array $conditions = [];
    private string $orderBy = "";
    private int $limit = 0;

    /**
     * テーブルを指定する
     *
     * @param string $table テーブル名
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * カラムを指定する
     *
     * @param string ...$columns カラム名
     * @return self
     */
    public function select(string ...$columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * WHERE条件を追加する
     *
     * @param string $condition 条件
     * @return self
     */
    public function where(string $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * ORDER BYを設定する
     *
     * @param string $column カラム名
     * @param string $direction 方向（ASC/DESC）
     * @return self
     */
    public function orderBy(string $column, string $direction = "ASC"): self
    {
        $this->orderBy = "{$column} {$direction}";
        return $this;
    }

    /**
     * LIMITを設定する
     *
     * @param int $limit 取得件数
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * SQLクエリを構築する
     *
     * @return string SQLクエリ
     */
    public function build(): string
    {
        $columns = empty($this->columns) ? "*" : implode(", ", $this->columns);
        $query = "SELECT {$columns} FROM {$this->table}";

        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }

        if (!empty($this->orderBy)) {
            $query .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit > 0) {
            $query .= " LIMIT {$this->limit}";
        }

        return $query;
    }
}

// メソッドチェーンを使用したクエリ構築
$query = (new QueryBuilder())
    ->table("users")
    ->select("id", "name", "email")
    ->where("age >= 20")
    ->where("status = 'active'")
    ->orderBy("created_at", "DESC")
    ->limit(10)
    ->build();

echo "構築されたクエリ:" . PHP_EOL;
echo $query . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 6. thisキーワードの理解 ===" . PHP_EOL;

/**
 * カウンタークラス
 */
class Counter
{
    private int $count = 0;

    /**
     * カウントを増やす
     *
     * @param int $amount 増加量
     * @return void
     */
    public function increment(int $amount = 1): void
    {
        // $thisは現在のオブジェクトインスタンスを参照
        $this->count += $amount;
    }

    /**
     * カウントを減らす
     *
     * @param int $amount 減少量
     * @return void
     */
    public function decrement(int $amount = 1): void
    {
        $this->count -= $amount;
    }

    /**
     * カウントを取得する
     *
     * @return int カウント
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * カウントをリセットする
     *
     * @return self メソッドチェーン用
     */
    public function reset(): self
    {
        $this->count = 0;
        return $this;
    }

    /**
     * 現在の状態を表示する
     *
     * @return void
     */
    public function display(): void
    {
        echo "現在のカウント: {$this->count}" . PHP_EOL;
    }
}

$counter = new Counter();
$counter->increment(5);
$counter->display();

$counter->increment(3);
$counter->display();

$counter->decrement(2);
$counter->display();

$counter->reset()->display();

echo PHP_EOL;

// =============================================================================

echo "=== 7. プロパティとメソッドの組み合わせ ===" . PHP_EOL;

/**
 * 商品クラス
 */
class Product
{
    public string $name;
    public float $price;
    public int $stock;
    private float $taxRate = 0.1;

    /**
     * 税込価格を取得する
     *
     * @return float 税込価格
     */
    public function getPriceWithTax(): float
    {
        return $this->price * (1 + $this->taxRate);
    }

    /**
     * 在庫があるかチェックする
     *
     * @return bool 在庫がある場合true
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * 在庫を減らす
     *
     * @param int $quantity 数量
     * @return bool 成功の場合true
     */
    public function reduceStock(int $quantity): bool
    {
        if ($quantity > $this->stock) {
            echo "エラー: 在庫不足です（在庫: {$this->stock}）" . PHP_EOL;
            return false;
        }

        $this->stock -= $quantity;
        return true;
    }

    /**
     * 在庫を追加する
     *
     * @param int $quantity 数量
     * @return void
     */
    public function addStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("追加する数量は正の数である必要があります");
        }
        $this->stock += $quantity;
    }

    /**
     * 商品情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        $priceWithTax = number_format($this->getPriceWithTax());
        $stockStatus = $this->isInStock() ? "在庫あり（{$this->stock}個）" : "在庫なし";

        echo "商品名: {$this->name}" . PHP_EOL;
        echo "価格: " . number_format($this->price) . "円（税込: {$priceWithTax}円）" . PHP_EOL;
        echo "在庫状況: {$stockStatus}" . PHP_EOL;
    }
}

$product = new Product();
$product->name = "ノートPC";
$product->price = 120000;
$product->stock = 5;

$product->displayInfo();

echo PHP_EOL . "3個購入を試みます..." . PHP_EOL;
if ($product->reduceStock(3)) {
    echo "購入成功！" . PHP_EOL;
    $product->displayInfo();
}

echo PHP_EOL . "10個購入を試みます..." . PHP_EOL;
$product->reduceStock(10);

echo PHP_EOL . "在庫を10個追加..." . PHP_EOL;
$product->addStock(10);
$product->displayInfo();

echo PHP_EOL;

// =============================================================================

echo "=== 8. オブジェクトの比較 ===" . PHP_EOL;

$user1 = new User();
$user1->name = "山田太郎";
$user1->email = "yamada@example.com";
$user1->age = 28;

$user2 = new User();
$user2->name = "山田太郎";
$user2->email = "yamada@example.com";
$user2->age = 28;

$user3 = $user1;  // 同じオブジェクトへの参照

// == はプロパティの値が同じかを比較
echo "user1 == user2: " . var_export($user1 == $user2, true) . PHP_EOL;

// === は同じオブジェクトインスタンスかを比較
echo "user1 === user2: " . var_export($user1 === $user2, true) . PHP_EOL;
echo "user1 === user3: " . var_export($user1 === $user3, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 9. オブジェクトのクローン ===" . PHP_EOL;

$original = new Product();
$original->name = "キーボード";
$original->price = 8000;
$original->stock = 10;

// オブジェクトをクローン
$copy = clone $original;

echo "オリジナル:" . PHP_EOL;
$original->displayInfo();

echo PHP_EOL . "コピー（クローン）:" . PHP_EOL;
$copy->displayInfo();

// コピーを変更してもオリジナルは影響を受けない
$copy->name = "マウス";
$copy->price = 2500;

echo PHP_EOL . "コピーを変更後:" . PHP_EOL;
echo "オリジナル: {$original->name}" . PHP_EOL;
echo "コピー: {$copy->name}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "クラスとオブジェクトの基礎を学習しました！" . PHP_EOL;
echo "次は 13_constructors.php でコンストラクタとデストラクタを学習します。" . PHP_EOL;
