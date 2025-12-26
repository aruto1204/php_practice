<?php

declare(strict_types=1);

/**
 * Phase 2.3: 名前空間とオートローディング - 実践演習
 *
 * この演習では、名前空間とオートローディングを実践的に学習します。
 *
 * 演習内容:
 * 1. ECサイトシステム（複数の名前空間を使った設計）
 * 2. ロギングシステム（名前空間の階層構造）
 * 3. プラグインシステム（動的なクラスローディング）
 */

echo "=== Phase 2.3: 名前空間とオートローディング - 実践演習 ===\n\n";

// ============================================================
// 演習1: ECサイトシステム
// ============================================================

echo "--- 演習1: ECサイトシステム ---\n\n";

/**
 * 課題:
 * ECサイトのシステムを名前空間を使って設計します。
 * 以下の名前空間構成で実装してください:
 * - ECommerce\Models (商品、カート、注文)
 * - ECommerce\Services (カート管理、注文処理)
 * - ECommerce\Repositories (データアクセス)
 */

// ============================================================
// ECommerce\Models 名前空間
// ============================================================

namespace ECommerce\Models {

    /**
     * 商品クラス
     */
    class Product
    {
        /**
         * コンストラクタ
         *
         * @param int $id 商品ID
         * @param string $name 商品名
         * @param float $price 価格
         * @param int $stock 在庫数
         */
        public function __construct(
            private int $id,
            private string $name,
            private float $price,
            private int $stock,
        ) {}

        public function getId(): int
        {
            return $this->id;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getPrice(): float
        {
            return $this->price;
        }

        public function getStock(): int
        {
            return $this->stock;
        }

        /**
         * 在庫を減らす
         *
         * @param int $quantity 数量
         * @return bool 成功した場合true
         */
        public function reduceStock(int $quantity): bool
        {
            if ($this->stock >= $quantity) {
                $this->stock -= $quantity;
                return true;
            }
            return false;
        }

        /**
         * 在庫があるか確認
         *
         * @param int $quantity 必要な数量
         * @return bool 在庫がある場合true
         */
        public function hasStock(int $quantity): bool
        {
            return $this->stock >= $quantity;
        }
    }

    /**
     * カート商品クラス
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
        ) {}

        public function getProduct(): Product
        {
            return $this->product;
        }

        public function getQuantity(): int
        {
            return $this->quantity;
        }

        public function setQuantity(int $quantity): void
        {
            $this->quantity = $quantity;
        }

        /**
         * 小計を計算
         *
         * @return float 小計金額
         */
        public function getSubtotal(): float
        {
            return $this->product->getPrice() * $this->quantity;
        }
    }

    /**
     * 注文クラス
     */
    class Order
    {
        private string $createdAt;

        /**
         * コンストラクタ
         *
         * @param int $id 注文ID
         * @param int $userId ユーザーID
         * @param array<CartItem> $items カート商品の配列
         * @param float $totalAmount 合計金額
         */
        public function __construct(
            private int $id,
            private int $userId,
            private array $items,
            private float $totalAmount,
        ) {
            $this->createdAt = date('Y-m-d H:i:s');
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getUserId(): int
        {
            return $this->userId;
        }

        /**
         * @return array<CartItem>
         */
        public function getItems(): array
        {
            return $this->items;
        }

        public function getTotalAmount(): float
        {
            return $this->totalAmount;
        }

        public function getCreatedAt(): string
        {
            return $this->createdAt;
        }

        /**
         * 注文の詳細を表示
         *
         * @return string 注文詳細
         */
        public function getDetails(): string
        {
            $details = "=== 注文詳細 ===\n";
            $details .= "注文ID: {$this->id}\n";
            $details .= "ユーザーID: {$this->userId}\n";
            $details .= "注文日時: {$this->createdAt}\n";
            $details .= "商品:\n";

            foreach ($this->items as $item) {
                $product = $item->getProduct();
                $details .= sprintf(
                    "  - %s × %d = %.2f円\n",
                    $product->getName(),
                    $item->getQuantity(),
                    $item->getSubtotal()
                );
            }

            $details .= sprintf("合計金額: %.2f円\n", $this->totalAmount);

            return $details;
        }
    }
}

// ============================================================
// ECommerce\Services 名前空間
// ============================================================

namespace ECommerce\Services {

    use ECommerce\Models\{Product, CartItem, Order};

    /**
     * カートサービス
     */
    class CartService
    {
        /** @var array<CartItem> */
        private array $items = [];

        /**
         * 商品をカートに追加
         *
         * @param Product $product 商品
         * @param int $quantity 数量
         * @return bool 成功した場合true
         */
        public function addItem(Product $product, int $quantity): bool
        {
            // 在庫チェック
            if (!$product->hasStock($quantity)) {
                echo "エラー: {$product->getName()} の在庫が不足しています\n";
                return false;
            }

            // 既に同じ商品がカートにある場合は数量を増やす
            foreach ($this->items as $item) {
                if ($item->getProduct()->getId() === $product->getId()) {
                    $newQuantity = $item->getQuantity() + $quantity;
                    if (!$product->hasStock($newQuantity)) {
                        echo "エラー: {$product->getName()} の在庫が不足しています\n";
                        return false;
                    }
                    $item->setQuantity($newQuantity);
                    echo "{$product->getName()} をカートに追加しました（数量: {$newQuantity}）\n";
                    return true;
                }
            }

            // 新しい商品をカートに追加
            $this->items[] = new CartItem($product, $quantity);
            echo "{$product->getName()} をカートに追加しました（数量: {$quantity}）\n";
            return true;
        }

        /**
         * カートから商品を削除
         *
         * @param int $productId 商品ID
         * @return bool 成功した場合true
         */
        public function removeItem(int $productId): bool
        {
            foreach ($this->items as $index => $item) {
                if ($item->getProduct()->getId() === $productId) {
                    $productName = $item->getProduct()->getName();
                    unset($this->items[$index]);
                    $this->items = array_values($this->items); // インデックスを振り直す
                    echo "{$productName} をカートから削除しました\n";
                    return true;
                }
            }

            echo "エラー: 商品が見つかりません\n";
            return false;
        }

        /**
         * カートの商品を取得
         *
         * @return array<CartItem>
         */
        public function getItems(): array
        {
            return $this->items;
        }

        /**
         * カート内の商品数を取得
         *
         * @return int 商品数
         */
        public function getItemCount(): int
        {
            return array_reduce(
                $this->items,
                fn(int $total, CartItem $item) => $total + $item->getQuantity(),
                0
            );
        }

        /**
         * カートの合計金額を計算
         *
         * @return float 合計金額
         */
        public function getTotalAmount(): float
        {
            return array_reduce(
                $this->items,
                fn(float $total, CartItem $item) => $total + $item->getSubtotal(),
                0.0
            );
        }

        /**
         * カートをクリア
         */
        public function clear(): void
        {
            $this->items = [];
            echo "カートをクリアしました\n";
        }

        /**
         * カートの内容を表示
         */
        public function showCart(): void
        {
            if (empty($this->items)) {
                echo "カートは空です\n";
                return;
            }

            echo "=== カートの内容 ===\n";
            foreach ($this->items as $item) {
                $product = $item->getProduct();
                echo sprintf(
                    "%s × %d = %.2f円\n",
                    $product->getName(),
                    $item->getQuantity(),
                    $item->getSubtotal()
                );
            }
            echo sprintf("合計: %.2f円 (%d点)\n", $this->getTotalAmount(), $this->getItemCount());
        }
    }

    /**
     * 注文サービス
     */
    class OrderService
    {
        private int $nextOrderId = 1;

        /**
         * 注文を作成
         *
         * @param int $userId ユーザーID
         * @param CartService $cart カートサービス
         * @return Order|null 注文（失敗時はnull）
         */
        public function createOrder(int $userId, CartService $cart): ?Order
        {
            $items = $cart->getItems();

            if (empty($items)) {
                echo "エラー: カートが空です\n";
                return null;
            }

            // 在庫チェック
            foreach ($items as $item) {
                $product = $item->getProduct();
                if (!$product->hasStock($item->getQuantity())) {
                    echo "エラー: {$product->getName()} の在庫が不足しています\n";
                    return null;
                }
            }

            // 在庫を減らす
            foreach ($items as $item) {
                $product = $item->getProduct();
                $product->reduceStock($item->getQuantity());
            }

            // 注文を作成
            $order = new Order(
                $this->nextOrderId++,
                $userId,
                $items,
                $cart->getTotalAmount()
            );

            echo "注文を作成しました（注文ID: {$order->getId()}）\n";

            // カートをクリア
            $cart->clear();

            return $order;
        }
    }
}

// ============================================================
// ECommerce\Repositories 名前空間
// ============================================================

namespace ECommerce\Repositories {

    use ECommerce\Models\Product;

    /**
     * 商品リポジトリ
     */
    class ProductRepository
    {
        /** @var array<Product> */
        private array $products = [];
        private int $nextId = 1;

        /**
         * 商品を追加
         *
         * @param string $name 商品名
         * @param float $price 価格
         * @param int $stock 在庫数
         * @return Product 追加された商品
         */
        public function create(string $name, float $price, int $stock): Product
        {
            $product = new Product($this->nextId++, $name, $price, $stock);
            $this->products[$product->getId()] = $product;
            return $product;
        }

        /**
         * 商品を取得
         *
         * @param int $id 商品ID
         * @return Product|null 商品（見つからない場合はnull）
         */
        public function find(int $id): ?Product
        {
            return $this->products[$id] ?? null;
        }

        /**
         * すべての商品を取得
         *
         * @return array<Product>
         */
        public function all(): array
        {
            return array_values($this->products);
        }

        /**
         * 在庫がある商品のみを取得
         *
         * @return array<Product>
         */
        public function getInStock(): array
        {
            return array_filter(
                $this->products,
                fn(Product $product) => $product->getStock() > 0
            );
        }
    }
}

// ============================================================
// グローバル名前空間（テストコード）
// ============================================================

namespace {

    use ECommerce\Models\Product;
    use ECommerce\Services\{CartService, OrderService};
    use ECommerce\Repositories\ProductRepository;

    echo "【ECサイトシステムのテスト】\n\n";

    // 商品リポジトリを作成
    $productRepo = new ProductRepository();

    // 商品を登録
    $product1 = $productRepo->create('ノートPC', 89800.00, 5);
    $product2 = $productRepo->create('マウス', 2980.00, 20);
    $product3 = $productRepo->create('キーボード', 5980.00, 10);

    echo "商品を登録しました\n";
    echo "在庫がある商品: " . count($productRepo->getInStock()) . "件\n\n";

    // カートサービスを作成
    $cart = new CartService();

    // カートに商品を追加
    $cart->addItem($product1, 1);
    $cart->addItem($product2, 2);
    $cart->addItem($product3, 1);
    echo "\n";

    // カートの内容を表示
    $cart->showCart();
    echo "\n";

    // 注文サービスを作成
    $orderService = new OrderService();

    // 注文を作成
    $order = $orderService->createOrder(1, $cart);
    echo "\n";

    // 注文の詳細を表示
    if ($order !== null) {
        echo $order->getDetails();
        echo "\n";
    }

    // 在庫状況を確認
    echo "【在庫状況】\n";
    foreach ($productRepo->all() as $product) {
        echo sprintf(
            "%s: %d個\n",
            $product->getName(),
            $product->getStock()
        );
    }
    echo "\n";
}

// ============================================================
// 演習2: ロギングシステム
// ============================================================

namespace Logger\Handlers {

    /**
     * ログハンドラーインターフェース
     */
    interface HandlerInterface
    {
        public function handle(string $level, string $message): void;
    }

    /**
     * ファイルハンドラー
     */
    class FileHandler implements HandlerInterface
    {
        public function __construct(
            private string $filename,
        ) {}

        public function handle(string $level, string $message): void
        {
            $timestamp = date('Y-m-d H:i:s');
            $log = "[{$timestamp}] [{$level}] {$message}";
            echo "[FileHandler] {$this->filename} に記録: {$log}\n";
        }
    }

    /**
     * コンソールハンドラー
     */
    class ConsoleHandler implements HandlerInterface
    {
        public function handle(string $level, string $message): void
        {
            $timestamp = date('Y-m-d H:i:s');
            echo "[ConsoleHandler] [{$timestamp}] [{$level}] {$message}\n";
        }
    }
}

namespace Logger {

    use Logger\Handlers\HandlerInterface;

    /**
     * ロガークラス
     */
    class Logger
    {
        /** @var array<HandlerInterface> */
        private array $handlers = [];

        /**
         * ハンドラーを追加
         *
         * @param HandlerInterface $handler ハンドラー
         */
        public function addHandler(HandlerInterface $handler): void
        {
            $this->handlers[] = $handler;
        }

        /**
         * ログを記録
         *
         * @param string $level ログレベル
         * @param string $message メッセージ
         */
        public function log(string $level, string $message): void
        {
            foreach ($this->handlers as $handler) {
                $handler->handle($level, $message);
            }
        }

        public function info(string $message): void
        {
            $this->log('INFO', $message);
        }

        public function warning(string $message): void
        {
            $this->log('WARNING', $message);
        }

        public function error(string $message): void
        {
            $this->log('ERROR', $message);
        }
    }
}

namespace {

    use Logger\Logger;
    use Logger\Handlers\{FileHandler, ConsoleHandler};

    echo "\n--- 演習2: ロギングシステム ---\n\n";

    // ロガーを作成
    $logger = new Logger();

    // ハンドラーを追加
    $logger->addHandler(new FileHandler('app.log'));
    $logger->addHandler(new ConsoleHandler());

    // ログを記録
    $logger->info('アプリケーションを起動しました');
    $logger->warning('メモリ使用量が80%を超えました');
    $logger->error('データベース接続に失敗しました');

    echo "\n";
}

// ============================================================
// 演習3: プラグインシステム
// ============================================================

namespace Plugin {

    /**
     * プラグインインターフェース
     */
    interface PluginInterface
    {
        public function getName(): string;
        public function getVersion(): string;
        public function execute(): void;
    }

    /**
     * プラグインマネージャー
     */
    class PluginManager
    {
        /** @var array<string, PluginInterface> */
        private array $plugins = [];

        /**
         * プラグインを登録
         *
         * @param PluginInterface $plugin プラグイン
         */
        public function register(PluginInterface $plugin): void
        {
            $this->plugins[$plugin->getName()] = $plugin;
            echo "{$plugin->getName()} v{$plugin->getVersion()} を登録しました\n";
        }

        /**
         * プラグインを実行
         *
         * @param string $name プラグイン名
         */
        public function run(string $name): void
        {
            if (!isset($this->plugins[$name])) {
                echo "エラー: プラグイン '{$name}' が見つかりません\n";
                return;
            }

            echo "\n'{$name}' を実行中...\n";
            $this->plugins[$name]->execute();
        }

        /**
         * すべてのプラグインを実行
         */
        public function runAll(): void
        {
            foreach ($this->plugins as $plugin) {
                echo "\n'{$plugin->getName()}' を実行中...\n";
                $plugin->execute();
            }
        }

        /**
         * 登録されているプラグインのリストを表示
         */
        public function listPlugins(): void
        {
            echo "\n=== 登録されているプラグイン ===\n";
            foreach ($this->plugins as $plugin) {
                echo "- {$plugin->getName()} v{$plugin->getVersion()}\n";
            }
        }
    }
}

namespace Plugin\Examples {

    use Plugin\PluginInterface;

    /**
     * バックアッププラグイン
     */
    class BackupPlugin implements PluginInterface
    {
        public function getName(): string
        {
            return 'BackupPlugin';
        }

        public function getVersion(): string
        {
            return '1.0.0';
        }

        public function execute(): void
        {
            echo "データベースをバックアップしています...\n";
            echo "バックアップが完了しました\n";
        }
    }

    /**
     * メール送信プラグイン
     */
    class EmailPlugin implements PluginInterface
    {
        public function getName(): string
        {
            return 'EmailPlugin';
        }

        public function getVersion(): string
        {
            return '2.1.0';
        }

        public function execute(): void
        {
            echo "メールを送信しています...\n";
            echo "メール送信が完了しました\n";
        }
    }

    /**
     * キャッシュクリアプラグイン
     */
    class CacheClearPlugin implements PluginInterface
    {
        public function getName(): string
        {
            return 'CacheClearPlugin';
        }

        public function getVersion(): string
        {
            return '1.5.2';
        }

        public function execute(): void
        {
            echo "キャッシュをクリアしています...\n";
            echo "キャッシュクリアが完了しました\n";
        }
    }
}

namespace {

    use Plugin\PluginManager;
    use Plugin\Examples\{BackupPlugin, EmailPlugin, CacheClearPlugin};

    echo "\n--- 演習3: プラグインシステム ---\n\n";

    // プラグインマネージャーを作成
    $pluginManager = new PluginManager();

    // プラグインを登録
    $pluginManager->register(new BackupPlugin());
    $pluginManager->register(new EmailPlugin());
    $pluginManager->register(new CacheClearPlugin());

    // 登録されているプラグインのリストを表示
    $pluginManager->listPlugins();

    // 特定のプラグインを実行
    $pluginManager->run('BackupPlugin');

    // すべてのプラグインを実行
    echo "\n=== すべてのプラグインを実行 ===";
    $pluginManager->runAll();

    echo "\n";
}

// ============================================================
// まとめ
// ============================================================

namespace {

    echo "\n=== 演習のまとめ ===\n\n";

    echo "学習した内容:\n";
    echo "1. 複数の名前空間を使ったプロジェクト設計（ECサイト）\n";
    echo "2. 階層的な名前空間の構成（ロギングシステム）\n";
    echo "3. インターフェースと名前空間の組み合わせ（プラグイン）\n";
    echo "4. use文によるクラスのインポート\n";
    echo "5. グループ化されたuse文の活用\n\n";

    echo "ベストプラクティス:\n";
    echo "- 機能ごとに名前空間を分ける（Models, Services, Repositories）\n";
    echo "- インターフェースとその実装を別の名前空間に配置できる\n";
    echo "- use文でコードの見通しを良くする\n";
    echo "- 階層的な名前空間でコードを整理する\n\n";

    echo "=== すべての演習が完了しました ===\n";
}
