<?php

declare(strict_types=1);

/**
 * Phase 4.3 演習課題: テスト駆動開発の実践
 *
 * この演習では、TDDの手法を使って新しい機能を実装します。
 * 先にテストを書き、その後に実装を行う「Red-Green-Refactor」のサイクルを実践します。
 *
 * 課題:
 * 1. TodoリストTDD演習
 * 2. 銀行口座TDD演習
 * 3. 在庫管理TDD演習
 */

namespace App\Phase4\Exercises;

echo "=== TDD演習課題 ===\n\n";

echo "この演習では、TDDの手法を使って以下のクラスを実装します。\n";
echo "1. まず tests/Exercises/ ディレクトリにテストファイルを作成\n";
echo "2. テストを実行して失敗を確認（Red）\n";
echo "3. 最小限の実装でテストをパス（Green）\n";
echo "4. コードをリファクタリング（Refactor）\n\n";

// ============================================
// 課題1: TodoリストTDD演習
// ============================================

/**
 * Todoアイテム
 */
class TodoItem
{
    public function __construct(
        private readonly int $id,
        private string $title,
        private string $description,
        private bool $completed = false,
        private ?\DateTimeImmutable $dueDate = null,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function complete(): void
    {
        $this->completed = true;
    }

    public function uncomplete(): void
    {
        $this->completed = false;
    }

    public function updateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException("タイトルは空にできません");
        }
        $this->title = $title;
    }

    public function updateDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function isOverdue(): bool
    {
        if ($this->dueDate === null || $this->completed) {
            return false;
        }
        return $this->dueDate < new \DateTimeImmutable();
    }
}

/**
 * Todoリスト
 */
class TodoList
{
    /** @var TodoItem[] */
    private array $items = [];
    private int $nextId = 1;

    public function addItem(string $title, string $description = '', ?\DateTimeImmutable $dueDate = null): TodoItem
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException("タイトルは空にできません");
        }

        $item = new TodoItem(
            id: $this->nextId++,
            title: $title,
            description: $description,
            dueDate: $dueDate,
        );

        $this->items[$item->getId()] = $item;
        return $item;
    }

    public function getItem(int $id): ?TodoItem
    {
        return $this->items[$id] ?? null;
    }

    public function getAllItems(): array
    {
        return array_values($this->items);
    }

    public function getCompletedItems(): array
    {
        return array_filter(
            $this->items,
            fn(TodoItem $item) => $item->isCompleted()
        );
    }

    public function getIncompleteItems(): array
    {
        return array_filter(
            $this->items,
            fn(TodoItem $item) => !$item->isCompleted()
        );
    }

    public function getOverdueItems(): array
    {
        return array_filter(
            $this->items,
            fn(TodoItem $item) => $item->isOverdue()
        );
    }

    public function removeItem(int $id): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }
        unset($this->items[$id]);
        return true;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function countCompleted(): int
    {
        return count($this->getCompletedItems());
    }

    public function countIncomplete(): int
    {
        return count($this->getIncompleteItems());
    }

    public function clear(): void
    {
        $this->items = [];
    }

    public function clearCompleted(): void
    {
        $this->items = array_filter(
            $this->items,
            fn(TodoItem $item) => !$item->isCompleted()
        );
    }
}

// ============================================
// 課題2: 銀行口座TDD演習
// ============================================

/**
 * トランザクション
 */
class Transaction
{
    public function __construct(
        private readonly int $id,
        private readonly string $type,
        private readonly float $amount,
        private readonly float $balance,
        private readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }
}

/**
 * 銀行口座
 */
class BankAccount
{
    private float $balance = 0.0;
    /** @var Transaction[] */
    private array $transactions = [];
    private int $nextTransactionId = 1;

    public function __construct(
        private readonly string $accountNumber,
        private readonly string $ownerName,
        float $initialDeposit = 0.0,
    ) {
        if ($initialDeposit < 0) {
            throw new \InvalidArgumentException("初期預金額は0以上である必要があります");
        }

        if ($initialDeposit > 0) {
            $this->deposit($initialDeposit);
        }
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("預金額は0より大きい必要があります");
        }

        $this->balance += $amount;
        $this->recordTransaction('deposit', $amount);
    }

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("引出額は0より大きい必要があります");
        }

        if ($amount > $this->balance) {
            throw new \RuntimeException("残高不足です");
        }

        $this->balance -= $amount;
        $this->recordTransaction('withdraw', $amount);
    }

    public function transfer(BankAccount $toAccount, float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("送金額は0より大きい必要があります");
        }

        $this->withdraw($amount);
        $toAccount->deposit($amount);
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getTransactionCount(): int
    {
        return count($this->transactions);
    }

    private function recordTransaction(string $type, float $amount): void
    {
        $transaction = new Transaction(
            id: $this->nextTransactionId++,
            type: $type,
            amount: $amount,
            balance: $this->balance,
        );

        $this->transactions[] = $transaction;
    }
}

// ============================================
// 課題3: 在庫管理TDD演習
// ============================================

/**
 * 商品
 */
class Product
{
    public function __construct(
        private readonly string $sku,
        private string $name,
        private float $price,
        private int $quantity = 0,
    ) {
        if ($price < 0) {
            throw new \InvalidArgumentException("価格は0以上である必要があります");
        }

        if ($quantity < 0) {
            throw new \InvalidArgumentException("数量は0以上である必要があります");
        }
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function updateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException("商品名は空にできません");
        }
        $this->name = $name;
    }

    public function updatePrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException("価格は0以上である必要があります");
        }
        $this->price = $price;
    }

    public function addStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("追加数量は0より大きい必要があります");
        }
        $this->quantity += $quantity;
    }

    public function removeStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("削減数量は0より大きい必要があります");
        }

        if ($quantity > $this->quantity) {
            throw new \RuntimeException("在庫不足です");
        }

        $this->quantity -= $quantity;
    }

    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    public function isLowStock(int $threshold = 10): bool
    {
        return $this->quantity <= $threshold && $this->quantity > 0;
    }

    public function getValue(): float
    {
        return $this->price * $this->quantity;
    }
}

/**
 * 在庫管理
 */
class Inventory
{
    /** @var Product[] */
    private array $products = [];

    public function addProduct(Product $product): void
    {
        $sku = $product->getSku();

        if (isset($this->products[$sku])) {
            throw new \RuntimeException("商品SKU {$sku} は既に登録されています");
        }

        $this->products[$sku] = $product;
    }

    public function getProduct(string $sku): ?Product
    {
        return $this->products[$sku] ?? null;
    }

    public function getAllProducts(): array
    {
        return array_values($this->products);
    }

    public function removeProduct(string $sku): bool
    {
        if (!isset($this->products[$sku])) {
            return false;
        }

        unset($this->products[$sku]);
        return true;
    }

    public function getInStockProducts(): array
    {
        return array_filter(
            $this->products,
            fn(Product $product) => $product->isInStock()
        );
    }

    public function getOutOfStockProducts(): array
    {
        return array_filter(
            $this->products,
            fn(Product $product) => !$product->isInStock()
        );
    }

    public function getLowStockProducts(int $threshold = 10): array
    {
        return array_filter(
            $this->products,
            fn(Product $product) => $product->isLowStock($threshold)
        );
    }

    public function getTotalValue(): float
    {
        return array_reduce(
            $this->products,
            fn(float $total, Product $product) => $total + $product->getValue(),
            0.0
        );
    }

    public function count(): int
    {
        return count($this->products);
    }

    public function search(string $keyword): array
    {
        $keyword = strtolower($keyword);
        return array_filter(
            $this->products,
            fn(Product $product) => str_contains(strtolower($product->getName()), $keyword)
                || str_contains(strtolower($product->getSku()), $keyword)
        );
    }
}

// ============================================
// 使用例
// ============================================

echo "--- 使用例 ---\n\n";

// TodoList
echo "1. TodoList:\n";
$todoList = new TodoList();
$item1 = $todoList->addItem('データベース設計', 'ER図の作成', new \DateTimeImmutable('+3 days'));
$item2 = $todoList->addItem('API実装', 'RESTful APIの実装');
echo "  総タスク数: " . $todoList->count() . "\n";
echo "  未完了タスク: " . $todoList->countIncomplete() . "\n";

$item1->complete();
echo "  完了タスク: " . $todoList->countCompleted() . "\n\n";

// BankAccount
echo "2. BankAccount:\n";
$account1 = new BankAccount('123-456', 'Alice', 10000);
$account2 = new BankAccount('789-012', 'Bob', 5000);
echo "  Alice残高: ¥" . number_format($account1->getBalance()) . "\n";
echo "  Bob残高: ¥" . number_format($account2->getBalance()) . "\n";

$account1->transfer($account2, 3000);
echo "  送金後:\n";
echo "    Alice残高: ¥" . number_format($account1->getBalance()) . "\n";
echo "    Bob残高: ¥" . number_format($account2->getBalance()) . "\n";
echo "  Aliceトランザクション数: " . $account1->getTransactionCount() . "\n\n";

// Inventory
echo "3. Inventory:\n";
$inventory = new Inventory();
$product1 = new Product('SKU001', 'ノートPC', 120000, 15);
$product2 = new Product('SKU002', 'マウス', 2000, 5);
$product3 = new Product('SKU003', 'キーボード', 8000, 0);

$inventory->addProduct($product1);
$inventory->addProduct($product2);
$inventory->addProduct($product3);

echo "  総商品数: " . $inventory->count() . "\n";
echo "  在庫あり: " . count($inventory->getInStockProducts()) . "件\n";
echo "  在庫切れ: " . count($inventory->getOutOfStockProducts()) . "件\n";
echo "  在庫わずか: " . count($inventory->getLowStockProducts(10)) . "件\n";
echo "  総在庫価値: ¥" . number_format($inventory->getTotalValue()) . "\n\n";

echo "=== TDDの実践 ===\n";
echo "\n";
echo "これらのクラスのテストは tests/Exercises/ ディレクトリに配置されています。\n";
echo "\n";
echo "【テスト実行】\n";
echo "  composer test tests/Exercises/TodoListTest.php\n";
echo "  composer test tests/Exercises/BankAccountTest.php\n";
echo "  composer test tests/Exercises/InventoryTest.php\n";
echo "\n";
echo "【TDDサイクル】\n";
echo "1. Red: テストを書いて失敗を確認\n";
echo "2. Green: 最小限の実装でテストをパス\n";
echo "3. Refactor: コードを改善\n";
echo "4. 次の機能に移る\n";
