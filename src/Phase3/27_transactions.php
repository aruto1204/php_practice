<?php

declare(strict_types=1);

/**
 * Phase 3.2: データベース操作 - トランザクション処理
 *
 * トランザクションの詳細な使い方とベストプラクティスを学習します。
 * ACID特性、ロールバック、セーブポイントなどを実践します。
 */

echo "=== Phase 3.2: トランザクション処理 ===\n\n";

// データベース接続
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // テーブル作成
    $pdo->exec("
        CREATE TABLE accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            balance REAL NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            from_account_id INTEGER,
            to_account_id INTEGER,
            amount REAL NOT NULL,
            type TEXT NOT NULL,
            description TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (from_account_id) REFERENCES accounts(id),
            FOREIGN KEY (to_account_id) REFERENCES accounts(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )
    ");

    echo "データベースとテーブルを準備しました\n\n";
} catch (PDOException $e) {
    echo "エラー: {$e->getMessage()}\n";
    exit(1);
}

// =====================================
// 1. トランザクションの基礎
// =====================================

echo "--- 1. トランザクションの基礎 ---\n";

/**
 * ACID特性
 *
 * - Atomicity（原子性）: すべての操作が成功するか、すべて失敗するか
 * - Consistency（一貫性）: データベースの整合性が保たれる
 * - Isolation（独立性）: 並行トランザクションが互いに影響しない
 * - Durability（永続性）: コミット後のデータは永続的に保存される
 */

echo "【ACID特性】\n";
echo "  - Atomicity（原子性）: All or Nothing\n";
echo "  - Consistency（一貫性）: データの整合性\n";
echo "  - Isolation（独立性）: 並行処理の安全性\n";
echo "  - Durability（永続性）: データの永続化\n";

echo "\n【基本的なトランザクション】\n";

// サンプルデータ挿入
$pdo->exec("INSERT INTO accounts (name, balance) VALUES ('Alice', 10000)");
$pdo->exec("INSERT INTO accounts (name, balance) VALUES ('Bob', 5000)");
$pdo->exec("INSERT INTO accounts (name, balance) VALUES ('Charlie', 8000)");

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // 複数の操作
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE name = ?");
    $stmt->execute([1000, 'Alice']);

    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE name = ?");
    $stmt->execute([1000, 'Bob']);

    // コミット
    $pdo->commit();
    echo "トランザクション成功: AliceからBobへ1000円送金\n";
} catch (PDOException $e) {
    // エラー時はロールバック
    $pdo->rollBack();
    echo "トランザクション失敗: {$e->getMessage()}\n";
}

// 結果確認
$stmt = $pdo->query("SELECT name, balance FROM accounts ORDER BY name");
echo "\n口座残高:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['name']}: " . number_format($row['balance']) . "円\n";
}

echo "\n";

// =====================================
// 2. ロールバックの実践
// =====================================

echo "--- 2. ロールバックの実践 ---\n";

echo "【エラー発生時の自動ロールバック】\n";

try {
    $pdo->beginTransaction();

    // Aliceの残高を減らす
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE name = ?");
    $stmt->execute([2000, 'Alice']);

    // 意図的にエラーを発生させる（残高不足をシミュレート）
    $stmt = $pdo->query("SELECT balance FROM accounts WHERE name = 'Bob'");
    $bobBalance = $stmt->fetchColumn();

    if ($bobBalance < 10000) {
        throw new RuntimeException("Bobの残高が不足しています");
    }

    // この操作は実行されない
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE name = ?");
    $stmt->execute([2000, 'Bob']);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "エラーが発生したためロールバックしました: {$e->getMessage()}\n";
}

// 結果確認（残高は変わっていない）
$stmt = $pdo->query("SELECT balance FROM accounts WHERE name = 'Alice'");
$aliceBalance = $stmt->fetchColumn();
echo "Aliceの残高（変更なし）: " . number_format($aliceBalance) . "円\n";

echo "\n";

// =====================================
// 3. トランザクションの入れ子（セーブポイント）
// =====================================

echo "--- 3. セーブポイント ---\n";

/**
 * SQLiteはセーブポイントをサポート
 * MySQL、PostgreSQLも対応
 */

echo "【セーブポイントを使った部分的なロールバック】\n";

try {
    $pdo->beginTransaction();

    // 最初の操作
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE name = ?");
    $stmt->execute([500, 'Alice']);
    echo "1. Aliceから500円引き出し\n";

    // セーブポイント作成
    $pdo->exec("SAVEPOINT sp1");

    // 2番目の操作
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE name = ?");
    $stmt->execute([300, 'Bob']);
    echo "2. Bobから300円引き出し\n";

    // セーブポイント作成
    $pdo->exec("SAVEPOINT sp2");

    // 3番目の操作（エラーをシミュレート）
    try {
        $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE name = ?");
        $stmt->execute([20000, 'Charlie']);  // 残高不足

        // チェック
        $stmt = $pdo->query("SELECT balance FROM accounts WHERE name = 'Charlie'");
        $charlieBalance = $stmt->fetchColumn();
        if ($charlieBalance < 0) {
            throw new RuntimeException("残高不足");
        }
    } catch (Exception $e) {
        // sp2までロールバック（3番目の操作のみキャンセル）
        $pdo->exec("ROLLBACK TO SAVEPOINT sp2");
        echo "3. Charlieの操作は失敗（セーブポイントsp2までロールバック）\n";
    }

    // コミット（1番目と2番目の操作は確定）
    $pdo->commit();
    echo "トランザクションをコミットしました\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "エラー: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 4. 送金処理の実装
// =====================================

echo "--- 4. 送金処理の実装 ---\n";

/**
 * 送金処理クラス
 */
class TransferService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 送金を実行
     *
     * @param string $fromAccount 送金元
     * @param string $toAccount 送金先
     * @param float $amount 金額
     * @param string $description 説明
     * @return bool 成功した場合true
     */
    public function transfer(
        string $fromAccount,
        string $toAccount,
        float $amount,
        string $description = ''
    ): bool {
        if ($amount <= 0) {
            throw new InvalidArgumentException("金額は正の数である必要があります");
        }

        if ($fromAccount === $toAccount) {
            throw new InvalidArgumentException("送金元と送金先が同じです");
        }

        try {
            $this->pdo->beginTransaction();

            // 送金元の口座情報を取得（行ロック）
            $stmt = $this->pdo->prepare("SELECT id, balance FROM accounts WHERE name = ?");
            $stmt->execute([$fromAccount]);
            $from = $stmt->fetch();

            if (!$from) {
                throw new RuntimeException("送金元の口座が見つかりません: {$fromAccount}");
            }

            // 残高チェック
            if ($from['balance'] < $amount) {
                throw new RuntimeException("残高不足: " . number_format($from['balance']) . "円");
            }

            // 送金先の口座情報を取得
            $stmt = $this->pdo->prepare("SELECT id FROM accounts WHERE name = ?");
            $stmt->execute([$toAccount]);
            $to = $stmt->fetch();

            if (!$to) {
                throw new RuntimeException("送金先の口座が見つかりません: {$toAccount}");
            }

            // 送金元から引き出し
            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $from['id']]);

            // 送金先に入金
            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $to['id']]);

            // 取引履歴を記録
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (from_account_id, to_account_id, amount, type, description)
                VALUES (?, ?, ?, 'transfer', ?)
            ");
            $stmt->execute([$from['id'], $to['id'], $amount, $description]);

            // コミット
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("送金エラー: {$e->getMessage()}");
        }
    }

    /**
     * 入金を実行
     *
     * @param string $account 口座名
     * @param float $amount 金額
     * @param string $description 説明
     * @return bool 成功した場合true
     */
    public function deposit(string $account, float $amount, string $description = ''): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("金額は正の数である必要があります");
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id FROM accounts WHERE name = ?");
            $stmt->execute([$account]);
            $accountData = $stmt->fetch();

            if (!$accountData) {
                throw new RuntimeException("口座が見つかりません: {$account}");
            }

            // 入金
            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $accountData['id']]);

            // 取引履歴を記録
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (to_account_id, amount, type, description)
                VALUES (?, ?, 'deposit', ?)
            ");
            $stmt->execute([$accountData['id'], $amount, $description]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("入金エラー: {$e->getMessage()}");
        }
    }

    /**
     * 出金を実行
     *
     * @param string $account 口座名
     * @param float $amount 金額
     * @param string $description 説明
     * @return bool 成功した場合true
     */
    public function withdraw(string $account, float $amount, string $description = ''): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("金額は正の数である必要があります");
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id, balance FROM accounts WHERE name = ?");
            $stmt->execute([$account]);
            $accountData = $stmt->fetch();

            if (!$accountData) {
                throw new RuntimeException("口座が見つかりません: {$account}");
            }

            // 残高チェック
            if ($accountData['balance'] < $amount) {
                throw new RuntimeException("残高不足");
            }

            // 出金
            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $accountData['id']]);

            // 取引履歴を記録
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (from_account_id, amount, type, description)
                VALUES (?, ?, 'withdraw', ?)
            ");
            $stmt->execute([$accountData['id'], $amount, $description]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("出金エラー: {$e->getMessage()}");
        }
    }
}

// 使用例
$transferService = new TransferService($pdo);

try {
    // 送金
    $transferService->transfer('Alice', 'Bob', 1500, '食事代の立替分');
    echo "送金成功: Alice → Bob: 1,500円\n";

    // 入金
    $transferService->deposit('Charlie', 2000, '給料');
    echo "入金成功: Charlie: 2,000円\n";

    // 出金
    $transferService->withdraw('Bob', 500, 'ATM出金');
    echo "出金成功: Bob: 500円\n";
} catch (RuntimeException $e) {
    echo "エラー: {$e->getMessage()}\n";
}

// 残高確認
$stmt = $pdo->query("SELECT name, balance FROM accounts ORDER BY name");
echo "\n最終残高:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['name']}: " . number_format($row['balance']) . "円\n";
}

echo "\n";

// =====================================
// 5. 注文処理のトランザクション
// =====================================

echo "--- 5. 注文処理のトランザクション ---\n";

/**
 * 注文処理クラス
 */
class OrderService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 注文を作成
     *
     * @param string $customerName 顧客名
     * @param array $items 商品配列 [['name' => '商品名', 'quantity' => 数量, 'price' => 価格], ...]
     * @return int 注文ID
     */
    public function createOrder(string $customerName, array $items): int
    {
        if (empty($items)) {
            throw new InvalidArgumentException("商品が指定されていません");
        }

        try {
            $this->pdo->beginTransaction();

            // 合計金額を計算
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // 注文を作成
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (customer_name, total_amount, status)
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$customerName, $totalAmount]);
            $orderId = (int)$this->pdo->lastInsertId();

            // 注文明細を作成
            $stmt = $this->pdo->prepare("
                INSERT INTO order_items (order_id, product_name, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    $orderId,
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
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
     * 注文をキャンセル
     *
     * @param int $orderId 注文ID
     * @return bool 成功した場合true
     */
    public function cancelOrder(int $orderId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 注文の存在確認
            $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new RuntimeException("注文が見つかりません");
            }

            if ($order['status'] === 'completed') {
                throw new RuntimeException("完了済みの注文はキャンセルできません");
            }

            // 注文ステータスを更新
            $stmt = $this->pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
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

            $stmt = $this->pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmt->execute([$orderId]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException("注文が見つかりません");
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("注文完了エラー: {$e->getMessage()}");
        }
    }

    /**
     * 注文詳細を取得
     *
     * @param int $orderId 注文ID
     * @return array 注文詳細
     */
    public function getOrderDetails(int $orderId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new RuntimeException("注文が見つかりません");
        }

        $stmt = $this->pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();

        return [
            'order' => $order,
            'items' => $items,
        ];
    }
}

// 使用例
$orderService = new OrderService($pdo);

try {
    // 注文作成
    $orderId = $orderService->createOrder('山田太郎', [
        ['name' => 'ノートPC', 'quantity' => 1, 'price' => 150000],
        ['name' => 'マウス', 'quantity' => 2, 'price' => 3000],
        ['name' => 'キーボード', 'quantity' => 1, 'price' => 8000],
    ]);
    echo "注文を作成しました（注文ID: {$orderId}）\n";

    // 注文詳細を取得
    $details = $orderService->getOrderDetails($orderId);
    echo "\n注文詳細:\n";
    echo "  顧客: {$details['order']['customer_name']}\n";
    echo "  合計: " . number_format($details['order']['total_amount']) . "円\n";
    echo "  商品:\n";
    foreach ($details['items'] as $item) {
        echo "    - {$item['product_name']} x{$item['quantity']}: " . number_format($item['price'] * $item['quantity']) . "円\n";
    }

    // 注文完了
    $orderService->completeOrder($orderId);
    echo "\n注文を完了しました\n";
} catch (RuntimeException $e) {
    echo "エラー: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 6. トランザクション分離レベル
// =====================================

echo "--- 6. トランザクション分離レベル ---\n";

/**
 * トランザクション分離レベル
 *
 * - READ UNCOMMITTED: コミットされていないデータも読める（ダーティリード）
 * - READ COMMITTED: コミット済みデータのみ読める
 * - REPEATABLE READ: 同じトランザクション内では同じデータを読める
 * - SERIALIZABLE: 最も厳格、完全に直列化
 *
 * SQLiteはデフォルトでSERIALIZABLE
 */

echo "
【トランザクション分離レベル】

1. READ UNCOMMITTED
   - 他のトランザクションのコミット前のデータも読める
   - ダーティリード、ファントムリードが発生
   - 最も低い分離レベル

2. READ COMMITTED（多くのDBMSのデフォルト）
   - コミット済みのデータのみ読める
   - ノンリピータブルリード、ファントムリードが発生

3. REPEATABLE READ
   - 同じトランザクション内では同じ結果を得られる
   - ファントムリードが発生する可能性

4. SERIALIZABLE
   - 最も厳格な分離レベル
   - すべての読み取り異常を防ぐ
   - パフォーマンスが低下する可能性

SQLiteはデフォルトでSERIALIZABLE相当
";

echo "\n";

// =====================================
// 7. ベストプラクティス
// =====================================

echo "--- 7. トランザクションのベストプラクティス ---\n";

echo "
【トランザクションの基本原則】

1. トランザクションは短く保つ
   - 長時間のトランザクションはロックを長く保持
   - 他のトランザクションをブロックする可能性
   - できるだけ早くコミットまたはロールバック

2. 必要な操作のみをトランザクション内で実行
   - ファイルI/O、外部API呼び出しはトランザクション外で
   - データベース操作のみをトランザクション内に

3. エラーハンドリングを徹底
   - try-catch でエラーを捕捉
   - catch ブロック内で必ずロールバック
   - finally を使った後処理（接続クローズなど）

4. デッドロックに注意
   - 複数のテーブルを更新する順序を統一
   - タイムアウトを設定
   - デッドロック検出機能を活用

5. トランザクション内でのSELECT
   - 行ロック（FOR UPDATE）を活用
   - 楽観的ロックと悲観的ロック

【パフォーマンスの最適化】

1. バッチ処理にトランザクションを使用
   - 複数のINSERTを1つのトランザクションで
   - コミット回数を減らす

2. 適切な分離レベルを選択
   - 要件に応じて最低限の分離レベルを使用
   - 過度に厳格な設定は避ける

3. インデックスを適切に設定
   - WHERE句のカラムにインデックス
   - ロック範囲を最小化

【セキュリティ】

1. 権限の最小化
   - トランザクションを実行するユーザーの権限を制限
   - 必要最小限の操作のみ許可

2. タイムアウト設定
   - 長時間実行されるトランザクションを防ぐ
   - リソースの占有を防止

3. ログ記録
   - トランザクションの開始・終了をログに記録
   - エラー時の調査に活用
";

echo "=== Phase 3.2: トランザクション処理 - 完了 ===\n";
