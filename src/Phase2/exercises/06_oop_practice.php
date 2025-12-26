<?php

declare(strict_types=1);

/**
 * Phase 2.1: オブジェクト指向プログラミングの演習課題
 *
 * このファイルでは、OOPの基礎を使った実践的な演習を行います。
 */

echo "=== オブジェクト指向プログラミングの演習課題 ===" . PHP_EOL . PHP_EOL;

// =============================================================================
// 1. Userクラスの実装
// =============================================================================

echo "=== 1. Userクラスの実装 ===" . PHP_EOL;

/**
 * ユーザークラス
 */
class User
{
    private string $createdAt;
    private string $updatedAt;
    private bool $isActive;

    /**
     * コンストラクタ
     *
     * @param int $id ユーザーID
     * @param string $username ユーザー名
     * @param string $email メールアドレス
     * @param string $passwordHash パスワードハッシュ
     * @throws InvalidArgumentException バリデーションエラー
     */
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private string $passwordHash,
    ) {
        // バリデーション
        if (empty($username)) {
            throw new InvalidArgumentException("ユーザー名は必須です");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("有効なメールアドレスを指定してください");
        }

        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        $this->isActive = true;
    }

    /**
     * ユーザーIDを取得する
     *
     * @return int ユーザーID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * ユーザー名を取得する
     *
     * @return string ユーザー名
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * メールアドレスを取得する
     *
     * @return string メールアドレス
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * メールアドレスを更新する
     *
     * @param string $email 新しいメールアドレス
     * @return void
     * @throws InvalidArgumentException バリデーションエラー
     */
    public function updateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("有効なメールアドレスを指定してください");
        }

        $this->email = $email;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * パスワードを確認する
     *
     * @param string $password パスワード
     * @return bool パスワードが一致する場合true
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * パスワードを更新する
     *
     * @param string $currentPassword 現在のパスワード
     * @param string $newPassword 新しいパスワード
     * @return bool 更新成功の場合true
     */
    public function updatePassword(string $currentPassword, string $newPassword): bool
    {
        if (!$this->verifyPassword($currentPassword)) {
            return false;
        }

        $this->passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }

    /**
     * アカウントを無効化する
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * アカウントを有効化する
     *
     * @return void
     */
    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * アカウントが有効かチェックする
     *
     * @return bool 有効な場合true
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * ユーザー情報を配列で取得する
     *
     * @return array<string, mixed> ユーザー情報
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * ユーザー情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        $status = $this->isActive ? "有効" : "無効";
        echo "ID: {$this->id}" . PHP_EOL;
        echo "ユーザー名: {$this->username}" . PHP_EOL;
        echo "メール: {$this->email}" . PHP_EOL;
        echo "ステータス: {$status}" . PHP_EOL;
        echo "作成日時: {$this->createdAt}" . PHP_EOL;
        echo "更新日時: {$this->updatedAt}" . PHP_EOL;
    }
}

// Userクラスのテスト
$user = new User(
    id: 1,
    username: "yamada_taro",
    email: "yamada@example.com",
    passwordHash: password_hash("password123", PASSWORD_DEFAULT),
);

$user->displayInfo();

echo PHP_EOL . "パスワード確認: " . ($user->verifyPassword("password123") ? "成功" : "失敗") . PHP_EOL;
echo "パスワード確認（間違い）: " . ($user->verifyPassword("wrong") ? "成功" : "失敗") . PHP_EOL;

echo PHP_EOL . "メールアドレスを更新..." . PHP_EOL;
$user->updateEmail("yamada_new@example.com");
echo "新しいメール: {$user->getEmail()}" . PHP_EOL;

echo PHP_EOL . "アカウントを無効化..." . PHP_EOL;
$user->deactivate();
echo "アクティブ? " . ($user->isActive() ? "はい" : "いいえ") . PHP_EOL;

echo PHP_EOL;

// =============================================================================
// 2. Productクラスの実装
// =============================================================================

echo "=== 2. Productクラスの実装 ===" . PHP_EOL;

/**
 * 商品クラス
 */
class Product
{
    private const TAX_RATE = 0.1;
    private int $viewCount = 0;
    private string $createdAt;

    /**
     * コンストラクタ
     *
     * @param int $id 商品ID
     * @param string $name 商品名
     * @param string $description 説明
     * @param float $price 価格
     * @param int $stock 在庫数
     * @param string $category カテゴリー
     * @throws InvalidArgumentException バリデーションエラー
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private float $price,
        private int $stock,
        private string $category,
    ) {
        if (empty($name)) {
            throw new InvalidArgumentException("商品名は必須です");
        }

        if ($price < 0) {
            throw new InvalidArgumentException("価格は0以上である必要があります");
        }

        if ($stock < 0) {
            throw new InvalidArgumentException("在庫数は0以上である必要があります");
        }

        $this->createdAt = date('Y-m-d H:i:s');
    }

    /**
     * 商品IDを取得する
     *
     * @return int 商品ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 商品名を取得する
     *
     * @return string 商品名
     */
    public function getName(): string
    {
        return $this->name;
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
     * 税込価格を取得する
     *
     * @return float 税込価格
     */
    public function getPriceWithTax(): float
    {
        return $this->price * (1 + self::TAX_RATE);
    }

    /**
     * 在庫数を取得する
     *
     * @return int 在庫数
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * 在庫があるかチェックする
     *
     * @param int $quantity 数量
     * @return bool 在庫がある場合true
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * 在庫を減らす
     *
     * @param int $quantity 数量
     * @return bool 成功の場合true
     */
    public function reduceStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
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
     * 価格を更新する
     *
     * @param float $newPrice 新しい価格
     * @return void
     */
    public function updatePrice(float $newPrice): void
    {
        if ($newPrice < 0) {
            throw new InvalidArgumentException("価格は0以上である必要があります");
        }

        $this->price = $newPrice;
    }

    /**
     * 割引価格を計算する
     *
     * @param float $discountRate 割引率（0.0 〜 1.0）
     * @return float 割引後の価格
     */
    public function getDiscountedPrice(float $discountRate): float
    {
        if ($discountRate < 0 || $discountRate > 1) {
            throw new InvalidArgumentException("割引率は0.0〜1.0の範囲で指定してください");
        }

        return $this->price * (1 - $discountRate);
    }

    /**
     * 閲覧数を増やす
     *
     * @return void
     */
    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }

    /**
     * 閲覧数を取得する
     *
     * @return int 閲覧数
     */
    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    /**
     * 商品情報を配列で取得する
     *
     * @return array<string, mixed> 商品情報
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'price_with_tax' => $this->getPriceWithTax(),
            'stock' => $this->stock,
            'category' => $this->category,
            'view_count' => $this->viewCount,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * 商品情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        $priceWithTax = number_format($this->getPriceWithTax());
        $stockStatus = $this->hasStock() ? "在庫あり（{$this->stock}個）" : "在庫切れ";

        echo "=== 商品情報 ===" . PHP_EOL;
        echo "ID: {$this->id}" . PHP_EOL;
        echo "商品名: {$this->name}" . PHP_EOL;
        echo "説明: {$this->description}" . PHP_EOL;
        echo "価格: " . number_format($this->price) . "円（税込: {$priceWithTax}円）" . PHP_EOL;
        echo "カテゴリー: {$this->category}" . PHP_EOL;
        echo "在庫: {$stockStatus}" . PHP_EOL;
        echo "閲覧数: {$this->viewCount}" . PHP_EOL;
    }
}

// Productクラスのテスト
$product = new Product(
    id: 1,
    name: "ワイヤレスマウス",
    description: "快適な操作感のワイヤレスマウス",
    price: 2500,
    stock: 20,
    category: "PC周辺機器",
);

$product->displayInfo();

echo PHP_EOL . "閲覧数を増やす..." . PHP_EOL;
$product->incrementViewCount();
$product->incrementViewCount();
$product->incrementViewCount();
echo "閲覧数: {$product->getViewCount()}" . PHP_EOL;

echo PHP_EOL . "20%割引価格: " . number_format($product->getDiscountedPrice(0.2)) . "円" . PHP_EOL;

echo PHP_EOL . "5個購入を試みる..." . PHP_EOL;
if ($product->reduceStock(5)) {
    echo "購入成功！残り在庫: {$product->getStock()}個" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 3. BankAccountクラスの実装
// =============================================================================

echo "=== 3. BankAccountクラスの実装 ===" . PHP_EOL;

/**
 * 銀行口座クラス
 */
class BankAccount
{
    private array $transactionHistory = [];

    /**
     * コンストラクタ
     *
     * @param string $accountNumber 口座番号
     * @param string $accountHolder 口座名義
     * @param float $balance 残高
     */
    public function __construct(
        public readonly string $accountNumber,
        public readonly string $accountHolder,
        private float $balance = 0,
    ) {
        $this->addTransaction("口座開設", $balance);
    }

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
        $this->addTransaction("入金", $amount);
    }

    /**
     * 出金する
     *
     * @param float $amount 出金額
     * @return bool 成功の場合true
     */
    public function withdraw(float $amount): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("出金額は正の数である必要があります");
        }

        if ($amount > $this->balance) {
            echo "エラー: 残高不足です（残高: " . number_format($this->balance) . "円）" . PHP_EOL;
            return false;
        }

        $this->balance -= $amount;
        $this->addTransaction("出金", -$amount);
        return true;
    }

    /**
     * 送金する
     *
     * @param BankAccount $toAccount 送金先
     * @param float $amount 送金額
     * @return bool 成功の場合true
     */
    public function transfer(BankAccount $toAccount, float $amount): bool
    {
        if (!$this->withdraw($amount)) {
            return false;
        }

        $toAccount->deposit($amount);
        $this->addTransaction("送金", -$amount, $toAccount->accountNumber);
        return true;
    }

    /**
     * 取引履歴を追加する
     *
     * @param string $type 取引種別
     * @param float $amount 金額
     * @param string|null $note 備考
     * @return void
     */
    private function addTransaction(string $type, float $amount, ?string $note = null): void
    {
        $this->transactionHistory[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'amount' => $amount,
            'balance' => $this->balance,
            'note' => $note,
        ];
    }

    /**
     * 取引履歴を取得する
     *
     * @return array<int, array<string, mixed>> 取引履歴
     */
    public function getTransactionHistory(): array
    {
        return $this->transactionHistory;
    }

    /**
     * 取引履歴を表示する
     *
     * @param int $limit 表示件数（0の場合すべて）
     * @return void
     */
    public function displayTransactionHistory(int $limit = 0): void
    {
        echo "=== 取引履歴 ===" . PHP_EOL;

        $history = $limit > 0
            ? array_slice($this->transactionHistory, -$limit)
            : $this->transactionHistory;

        foreach ($history as $transaction) {
            $amountStr = number_format(abs($transaction['amount']));
            $sign = $transaction['amount'] >= 0 ? '+' : '-';
            $balanceStr = number_format($transaction['balance']);

            echo "[{$transaction['timestamp']}] {$transaction['type']}: {$sign}{$amountStr}円（残高: {$balanceStr}円）";

            if ($transaction['note'] !== null) {
                echo " - {$transaction['note']}";
            }

            echo PHP_EOL;
        }
    }

    /**
     * 口座情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        echo "=== 口座情報 ===" . PHP_EOL;
        echo "口座番号: {$this->accountNumber}" . PHP_EOL;
        echo "名義: {$this->accountHolder}" . PHP_EOL;
        echo "残高: " . number_format($this->balance) . "円" . PHP_EOL;
    }
}

// BankAccountクラスのテスト
$account1 = new BankAccount("1234567890", "山田太郎", 100000);
$account2 = new BankAccount("0987654321", "佐藤花子", 50000);

$account1->displayInfo();
echo PHP_EOL;

echo "50,000円を入金..." . PHP_EOL;
$account1->deposit(50000);
echo "残高: " . number_format($account1->getBalance()) . "円" . PHP_EOL;

echo PHP_EOL . "30,000円を出金..." . PHP_EOL;
$account1->withdraw(30000);
echo "残高: " . number_format($account1->getBalance()) . "円" . PHP_EOL;

echo PHP_EOL . "佐藤花子さんに20,000円を送金..." . PHP_EOL;
$account1->transfer($account2, 20000);
echo "山田太郎の残高: " . number_format($account1->getBalance()) . "円" . PHP_EOL;
echo "佐藤花子の残高: " . number_format($account2->getBalance()) . "円" . PHP_EOL;

echo PHP_EOL;
$account1->displayTransactionHistory(5);

echo PHP_EOL;

// =============================================================================
// 4. ShoppingCartクラスの実装
// =============================================================================

echo "=== 4. ShoppingCartクラスの実装 ===" . PHP_EOL;

/**
 * カートアイテムクラス
 */
class CartItem
{
    /**
     * コンストラクタ
     *
     * @param Product $product 商品
     * @param int $quantity 数量
     */
    public function __construct(
        private Product $product,
        private int $quantity,
    ) {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("数量は正の数である必要があります");
        }
    }

    /**
     * 商品を取得する
     *
     * @return Product 商品
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * 数量を取得する
     *
     * @return int 数量
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * 数量を設定する
     *
     * @param int $quantity 数量
     * @return void
     */
    public function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("数量は正の数である必要があります");
        }

        $this->quantity = $quantity;
    }

    /**
     * 小計を取得する
     *
     * @return float 小計
     */
    public function getSubtotal(): float
    {
        return $this->product->getPriceWithTax() * $this->quantity;
    }
}

/**
 * ショッピングカートクラス
 */
class ShoppingCart
{
    /**
     * @var array<int, CartItem> カートアイテム
     */
    private array $items = [];

    /**
     * 商品をカートに追加する
     *
     * @param Product $product 商品
     * @param int $quantity 数量
     * @return bool 成功の場合true
     */
    public function addItem(Product $product, int $quantity = 1): bool
    {
        if (!$product->hasStock($quantity)) {
            echo "エラー: 在庫不足です" . PHP_EOL;
            return false;
        }

        $productId = $product->getId();

        if (isset($this->items[$productId])) {
            $newQuantity = $this->items[$productId]->getQuantity() + $quantity;

            if (!$product->hasStock($newQuantity)) {
                echo "エラー: 在庫不足です" . PHP_EOL;
                return false;
            }

            $this->items[$productId]->setQuantity($newQuantity);
        } else {
            $this->items[$productId] = new CartItem($product, $quantity);
        }

        return true;
    }

    /**
     * 商品をカートから削除する
     *
     * @param int $productId 商品ID
     * @return bool 成功の場合true
     */
    public function removeItem(int $productId): bool
    {
        if (!isset($this->items[$productId])) {
            return false;
        }

        unset($this->items[$productId]);
        return true;
    }

    /**
     * カートをクリアする
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * カートアイテムを取得する
     *
     * @return array<int, CartItem> カートアイテム
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * アイテム数を取得する
     *
     * @return int アイテム数
     */
    public function getItemCount(): int
    {
        return array_reduce(
            $this->items,
            fn($carry, $item) => $carry + $item->getQuantity(),
            0
        );
    }

    /**
     * 合計金額を取得する
     *
     * @return float 合計金額
     */
    public function getTotal(): float
    {
        return array_reduce(
            $this->items,
            fn($carry, $item) => $carry + $item->getSubtotal(),
            0
        );
    }

    /**
     * カート内容を表示する
     *
     * @return void
     */
    public function displayContents(): void
    {
        if (empty($this->items)) {
            echo "カートは空です" . PHP_EOL;
            return;
        }

        echo "=== カート内容 ===" . PHP_EOL;

        foreach ($this->items as $item) {
            $product = $item->getProduct();
            $quantity = $item->getQuantity();
            $subtotal = number_format($item->getSubtotal());

            echo "- {$product->getName()} × {$quantity} = {$subtotal}円" . PHP_EOL;
        }

        echo PHP_EOL;
        echo "商品数: {$this->getItemCount()}点" . PHP_EOL;
        echo "合計: " . number_format($this->getTotal()) . "円" . PHP_EOL;
    }
}

// ShoppingCartクラスのテスト
$product1 = new Product(1, "ノートPC", "高性能ノートPC", 120000, 5, "PC");
$product2 = new Product(2, "マウス", "ワイヤレスマウス", 2500, 10, "PC周辺機器");
$product3 = new Product(3, "キーボード", "メカニカルキーボード", 8000, 8, "PC周辺機器");

$cart = new ShoppingCart();

echo "商品をカートに追加..." . PHP_EOL;
$cart->addItem($product1, 1);
$cart->addItem($product2, 2);
$cart->addItem($product3, 1);

echo PHP_EOL;
$cart->displayContents();

echo PHP_EOL . "マウスをさらに1個追加..." . PHP_EOL;
$cart->addItem($product2, 1);

echo PHP_EOL;
$cart->displayContents();

echo PHP_EOL;

// =============================================================================

echo "=== 演習課題完了 ===" . PHP_EOL;
echo "オブジェクト指向プログラミングの実践的な演習が完了しました！" . PHP_EOL;
echo "学習した内容:" . PHP_EOL;
echo "- Userクラス: 認証、バリデーション、状態管理" . PHP_EOL;
echo "- Productクラス: 在庫管理、価格計算、閲覧数管理" . PHP_EOL;
echo "- BankAccountクラス: 残高管理、取引履歴、送金機能" . PHP_EOL;
echo "- ShoppingCartクラス: カート管理、商品追加・削除" . PHP_EOL;
