<?php

declare(strict_types=1);

/**
 * Phase 3.2: データベース操作 - 応用 - 実践演習
 *
 * トランザクション、JOIN、リレーションシップを使った実践的なシステムの実装
 */

echo "=== Phase 3.2: データベース操作 - 応用 - 実践演習 ===\n\n";

// =====================================
// データベース接続とテーブル作成
// =====================================

try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // テーブル作成
    $pdo->exec("
        CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            stock INTEGER NOT NULL DEFAULT 0,
            category_id INTEGER,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            parent_id INTEGER,
            FOREIGN KEY (parent_id) REFERENCES categories(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            address TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INTEGER NOT NULL,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE inventory_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            quantity_change INTEGER NOT NULL,
            reason TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");

    echo "データベースとテーブルを準備しました\n\n";
} catch (PDOException $e) {
    echo "エラー: {$e->getMessage()}\n";
    exit(1);
}

// サンプルデータ挿入
$pdo->exec("INSERT INTO categories (name, parent_id) VALUES ('Electronics', NULL)");
$pdo->exec("INSERT INTO categories (name, parent_id) VALUES ('Computers', 1)");
$pdo->exec("INSERT INTO categories (name, parent_id) VALUES ('Smartphones', 1)");
$pdo->exec("INSERT INTO categories (name, parent_id) VALUES ('Books', NULL)");
$pdo->exec("INSERT INTO categories (name, parent_id) VALUES ('Programming', 4)");

$pdo->exec("INSERT INTO products (name, description, price, stock, category_id) VALUES ('MacBook Pro', 'High-performance laptop', 250000, 10, 2)");
$pdo->exec("INSERT INTO products (name, description, price, stock, category_id) VALUES ('iPhone 15', 'Latest smartphone', 120000, 20, 3)");
$pdo->exec("INSERT INTO products (name, description, price, stock, category_id) VALUES ('iPad Air', 'Versatile tablet', 80000, 15, 2)");
$pdo->exec("INSERT INTO products (name, description, price, stock, category_id) VALUES ('PHP Book', 'Learn PHP programming', 3000, 50, 5)");
$pdo->exec("INSERT INTO products (name, description, price, stock, category_id) VALUES ('Python Book', 'Python for beginners', 3500, 30, 5)");

$pdo->exec("INSERT INTO customers (name, email, phone, address) VALUES ('山田太郎', 'yamada@example.com', '090-1234-5678', '東京都渋谷区')");
$pdo->exec("INSERT INTO customers (name, email, phone, address) VALUES ('佐藤花子', 'sato@example.com', '090-2345-6789', '大阪府大阪市')");
$pdo->exec("INSERT INTO customers (name, email, phone, address) VALUES ('鈴木一郎', 'suzuki@example.com', '090-3456-7890', '福岡県福岡市')");

echo "サンプルデータを挿入しました\n\n";

// =====================================
// 演習1: ECサイトの注文処理システム
// =====================================

echo "--- 演習1: ECサイトの注文処理システム ---\n";

/**
 * 注文処理サービス
 */
class OrderProcessingService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 注文を作成（トランザクション使用）
     *
     * @param int $customerId 顧客ID
     * @param array $items 商品配列 [['product_id' => ID, 'quantity' => 数量], ...]
     * @return int 注文ID
     */
    public function createOrder(int $customerId, array $items): int
    {
        if (empty($items)) {
            throw new InvalidArgumentException("商品が指定されていません");
        }

        try {
            $this->pdo->beginTransaction();

            // 顧客の存在確認
            $stmt = $this->pdo->prepare("SELECT id FROM customers WHERE id = ?");
            $stmt->execute([$customerId]);
            if (!$stmt->fetch()) {
                throw new RuntimeException("顧客が見つかりません");
            }

            $totalAmount = 0;
            $orderItems = [];

            // 商品の在庫チェックと金額計算
            foreach ($items as $item) {
                $stmt = $this->pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch();

                if (!$product) {
                    throw new RuntimeException("商品ID {$item['product_id']} が見つかりません");
                }

                if ($product['stock'] < $item['quantity']) {
                    throw new RuntimeException(
                        "商品 '{$product['name']}' の在庫不足（在庫: {$product['stock']}、注文: {$item['quantity']}）"
                    );
                }

                $itemTotal = $product['price'] * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                ];
            }

            // 注文を作成
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (customer_id, total_amount, status)
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$customerId, $totalAmount]);
            $orderId = (int)$this->pdo->lastInsertId();

            // 注文明細を作成 & 在庫を減らす
            foreach ($orderItems as $item) {
                // 注文明細
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                ]);

                // 在庫を減らす
                $stmt = $this->pdo->prepare("
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);

                // 在庫ログを記録
                $stmt = $this->pdo->prepare("
                    INSERT INTO inventory_logs (product_id, quantity_change, reason)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $item['product_id'],
                    -$item['quantity'],
                    "注文ID {$orderId} による販売",
                ]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("注文作成エラー: {$e->getMessage()}");
        }
    }

    /**
     * 注文をキャンセル（在庫を戻す）
     *
     * @param int $orderId 注文ID
     * @return bool 成功した場合true
     */
    public function cancelOrder(int $orderId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 注文の存在確認
            $stmt = $this->pdo->prepare("SELECT id, status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new RuntimeException("注文が見つかりません");
            }

            if ($order['status'] === 'cancelled') {
                throw new RuntimeException("既にキャンセル済みです");
            }

            if ($order['status'] === 'completed') {
                throw new RuntimeException("完了済みの注文はキャンセルできません");
            }

            // 注文明細を取得
            $stmt = $this->pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();

            // 在庫を戻す
            foreach ($items as $item) {
                $stmt = $this->pdo->prepare("
                    UPDATE products
                    SET stock = stock + ?
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);

                // 在庫ログを記録
                $stmt = $this->pdo->prepare("
                    INSERT INTO inventory_logs (product_id, quantity_change, reason)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $item['product_id'],
                    $item['quantity'],
                    "注文ID {$orderId} のキャンセルによる返品",
                ]);
            }

            // 注文ステータスを更新
            $stmt = $this->pdo->prepare("
                UPDATE orders
                SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$orderId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("注文キャンセルエラー: {$e->getMessage()}");
        }
    }

    /**
     * 注文を完了
     *
     * @param int $orderId 注文ID
     * @return bool 成功した場合true
     */
    public function completeOrder(int $orderId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE orders
                SET status = 'completed', updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->execute([$orderId]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException("注文が見つからないか、既に処理済みです");
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("注文完了エラー: {$e->getMessage()}");
        }
    }

    /**
     * 注文詳細を取得（JOIN使用）
     *
     * @param int $orderId 注文ID
     * @return array 注文詳細
     */
    public function getOrderDetails(int $orderId): array
    {
        // 注文情報と顧客情報を取得
        $stmt = $this->pdo->prepare("
            SELECT
                orders.*,
                customers.name AS customer_name,
                customers.email AS customer_email,
                customers.address AS customer_address
            FROM orders
            INNER JOIN customers ON orders.customer_id = customers.id
            WHERE orders.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new RuntimeException("注文が見つかりません");
        }

        // 注文明細と商品情報を取得
        $stmt = $this->pdo->prepare("
            SELECT
                order_items.*,
                products.name AS product_name
            FROM order_items
            INNER JOIN products ON order_items.product_id = products.id
            WHERE order_items.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();

        return [
            'order' => $order,
            'items' => $items,
        ];
    }
}

// 使用例
$orderService = new OrderProcessingService($pdo);

try {
    // 注文を作成
    $orderId = $orderService->createOrder(1, [
        ['product_id' => 1, 'quantity' => 1],  // MacBook Pro
        ['product_id' => 2, 'quantity' => 2],  // iPhone 15 x2
    ]);
    echo "注文を作成しました（注文ID: {$orderId}）\n";

    // 注文詳細を取得
    $details = $orderService->getOrderDetails($orderId);
    echo "\n注文詳細:\n";
    echo "  顧客: {$details['order']['customer_name']} ({$details['order']['customer_email']})\n";
    echo "  合計: " . number_format($details['order']['total_amount']) . "円\n";
    echo "  ステータス: {$details['order']['status']}\n";
    echo "  商品:\n";
    foreach ($details['items'] as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        echo "    - {$item['product_name']} x{$item['quantity']}: " . number_format($subtotal) . "円\n";
    }

    // 注文を完了
    $orderService->completeOrder($orderId);
    echo "\n注文を完了しました\n";
} catch (RuntimeException $e) {
    echo "エラー: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 演習2: レポート生成システム
// =====================================

echo "--- 演習2: レポート生成システム ---\n";

/**
 * レポート生成サービス
 */
class ReportingService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 売上レポートを生成
     *
     * @return array 売上レポート
     */
    public function getSalesReport(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total_orders,
                SUM(total_amount) AS total_sales,
                AVG(total_amount) AS average_order_value,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
            FROM orders
        ");

        return $stmt->fetch();
    }

    /**
     * 商品別売上レポート
     *
     * @param int $limit 上位件数
     * @return array 商品別売上
     */
    public function getProductSalesReport(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                products.id,
                products.name,
                SUM(order_items.quantity) AS total_quantity,
                SUM(order_items.quantity * order_items.price) AS total_sales,
                COUNT(DISTINCT order_items.order_id) AS order_count
            FROM products
            INNER JOIN order_items ON products.id = order_items.product_id
            INNER JOIN orders ON order_items.order_id = orders.id
            WHERE orders.status = 'completed'
            GROUP BY products.id, products.name
            ORDER BY total_sales DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * カテゴリ別売上レポート
     *
     * @return array カテゴリ別売上
     */
    public function getCategorySalesReport(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                categories.id,
                categories.name AS category_name,
                COUNT(DISTINCT products.id) AS product_count,
                SUM(order_items.quantity) AS total_quantity,
                SUM(order_items.quantity * order_items.price) AS total_sales
            FROM categories
            LEFT JOIN products ON categories.id = products.category_id
            LEFT JOIN order_items ON products.id = order_items.product_id
            LEFT JOIN orders ON order_items.order_id = orders.id AND orders.status = 'completed'
            GROUP BY categories.id, categories.name
            ORDER BY total_sales DESC
        ");

        return $stmt->fetchAll();
    }

    /**
     * 顧客別購入履歴レポート
     *
     * @return array 顧客別購入履歴
     */
    public function getCustomerPurchaseReport(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                customers.id,
                customers.name,
                customers.email,
                COUNT(orders.id) AS order_count,
                SUM(CASE WHEN orders.status = 'completed' THEN orders.total_amount ELSE 0 END) AS total_spent,
                MAX(orders.created_at) AS last_order_date
            FROM customers
            LEFT JOIN orders ON customers.id = orders.customer_id
            GROUP BY customers.id, customers.name, customers.email
            ORDER BY total_spent DESC
        ");

        return $stmt->fetchAll();
    }

    /**
     * 在庫状況レポート
     *
     * @return array 在庫状況
     */
    public function getInventoryReport(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                products.id,
                products.name,
                categories.name AS category_name,
                products.stock,
                products.price,
                products.stock * products.price AS inventory_value,
                COALESCE(SUM(order_items.quantity), 0) AS total_sold
            FROM products
            LEFT JOIN categories ON products.category_id = categories.id
            LEFT JOIN order_items ON products.id = order_items.product_id
            LEFT JOIN orders ON order_items.order_id = orders.id AND orders.status = 'completed'
            GROUP BY products.id, products.name, categories.name, products.stock, products.price
            ORDER BY inventory_value DESC
        ");

        return $stmt->fetchAll();
    }
}

// 使用例
$reportService = new ReportingService($pdo);

// 売上レポート
$salesReport = $reportService->getSalesReport();
echo "売上サマリー:\n";
echo "  総注文数: {$salesReport['total_orders']}件\n";
echo "  総売上: " . number_format($salesReport['total_sales']) . "円\n";
echo "  平均注文額: " . number_format($salesReport['average_order_value']) . "円\n";
echo "  完了: {$salesReport['completed_orders']}件、保留: {$salesReport['pending_orders']}件、キャンセル: {$salesReport['cancelled_orders']}件\n";

// 商品別売上レポート
echo "\n商品別売上（上位5件）:\n";
$productSales = $reportService->getProductSalesReport(5);
foreach ($productSales as $product) {
    echo "  {$product['name']}:\n";
    echo "    販売数: {$product['total_quantity']}個\n";
    echo "    売上: " . number_format($product['total_sales']) . "円\n";
}

// カテゴリ別売上レポート
echo "\nカテゴリ別売上:\n";
$categorySales = $reportService->getCategorySalesReport();
foreach ($categorySales as $category) {
    $sales = $category['total_sales'] ?? 0;
    echo "  {$category['category_name']}: " . number_format($sales) . "円\n";
}

// 顧客別購入履歴レポート
echo "\n顧客別購入履歴:\n";
$customerPurchases = $reportService->getCustomerPurchaseReport();
foreach ($customerPurchases as $customer) {
    echo "  {$customer['name']} ({$customer['email']}):\n";
    echo "    注文数: {$customer['order_count']}件\n";
    echo "    総購入額: " . number_format($customer['total_spent']) . "円\n";
}

echo "\n";

// =====================================
// 演習3: 在庫管理システム
// =====================================

echo "--- 演習3: 在庫管理システム ---\n";

/**
 * 在庫管理サービス
 */
class InventoryManagementService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 在庫を追加
     *
     * @param int $productId 商品ID
     * @param int $quantity 数量
     * @param string $reason 理由
     * @return bool 成功した場合true
     */
    public function addStock(int $productId, int $quantity, string $reason = '入荷'): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 在庫を増やす
            $stmt = $this->pdo->prepare("
                UPDATE products
                SET stock = stock + ?
                WHERE id = ?
            ");
            $stmt->execute([$quantity, $productId]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException("商品が見つかりません");
            }

            // ログを記録
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_logs (product_id, quantity_change, reason)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$productId, $quantity, $reason]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("在庫追加エラー: {$e->getMessage()}");
        }
    }

    /**
     * 在庫を減らす
     *
     * @param int $productId 商品ID
     * @param int $quantity 数量
     * @param string $reason 理由
     * @return bool 成功した場合true
     */
    public function removeStock(int $productId, int $quantity, string $reason = '出荷'): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 現在の在庫をチェック
            $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new RuntimeException("商品が見つかりません");
            }

            if ($product['stock'] < $quantity) {
                throw new RuntimeException("在庫不足");
            }

            // 在庫を減らす
            $stmt = $this->pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ?
            ");
            $stmt->execute([$quantity, $productId]);

            // ログを記録
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_logs (product_id, quantity_change, reason)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$productId, -$quantity, $reason]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("在庫削減エラー: {$e->getMessage()}");
        }
    }

    /**
     * 在庫履歴を取得
     *
     * @param int $productId 商品ID
     * @param int $limit 取得件数
     * @return array 在庫履歴
     */
    public function getInventoryHistory(int $productId, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM inventory_logs
            WHERE product_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$productId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * 在庫アラートを取得（在庫が少ない商品）
     *
     * @param int $threshold しきい値
     * @return array 在庫が少ない商品
     */
    public function getLowStockAlert(int $threshold = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                products.*,
                categories.name AS category_name
            FROM products
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE products.stock <= ?
            ORDER BY products.stock ASC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
}

// 使用例
$inventoryService = new InventoryManagementService($pdo);

try {
    // 在庫を追加
    $inventoryService->addStock(4, 20, '新規入荷');
    echo "商品ID 4の在庫を20個追加しました\n";

    // 在庫履歴を取得
    $history = $inventoryService->getInventoryHistory(1, 5);
    echo "\n商品ID 1の在庫履歴:\n";
    foreach ($history as $log) {
        $change = $log['quantity_change'] > 0 ? "+{$log['quantity_change']}" : $log['quantity_change'];
        echo "  {$log['created_at']}: {$change}個（{$log['reason']}）\n";
    }

    // 在庫アラート
    $lowStockProducts = $inventoryService->getLowStockAlert(15);
    echo "\n在庫が少ない商品（15個以下）:\n";
    foreach ($lowStockProducts as $product) {
        echo "  {$product['name']}: 残り{$product['stock']}個\n";
    }
} catch (RuntimeException $e) {
    echo "エラー: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// まとめ
// =====================================

echo "--- まとめ ---\n";

echo "
【実装したシステム】

1. ECサイトの注文処理システム
   - トランザクションを使った注文作成
   - 在庫チェックと在庫削減
   - 注文のキャンセル（在庫の復元）
   - JOIN を使った注文詳細の取得
   - 在庫ログの記録

2. レポート生成システム
   - 売上サマリーレポート
   - 商品別売上レポート（GROUP BY、ORDER BY）
   - カテゴリ別売上レポート（複数JOIN）
   - 顧客別購入履歴レポート
   - 在庫状況レポート

3. 在庫管理システム
   - トランザクションを使った在庫操作
   - 在庫履歴の記録と参照
   - 在庫アラート機能

【学んだこと】

- トランザクションによるデータ整合性の保証
- 複数テーブルのJOINとリレーション管理
- GROUP BY、集計関数（COUNT、SUM、AVG）の活用
- LEFT JOIN による外部結合
- サブクエリとCASE文の使用
- ビジネスロジックとデータアクセスの分離
- エラーハンドリングとロールバック
- 在庫管理のベストプラクティス

【応用できる場面】

- ECサイトの構築
- 在庫管理システム
- 売上分析・レポーティング
- 顧客管理システム
- オーダー管理システム
";

echo "\n=== Phase 3.2: データベース操作 - 応用 - 実践演習完了 ===\n";
