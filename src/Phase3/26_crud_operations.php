<?php

declare(strict_types=1);

/**
 * Phase 3.1: データベース操作 - CRUD操作
 *
 * Create, Read, Update, Delete の基本操作を学習します。
 * プリペアドステートメントを使った安全なデータベース操作を実践します。
 */

echo "=== Phase 3.1: CRUD操作 ===\n\n";

// データベース接続
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
            category TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "データベースとテーブルを準備しました\n\n";
} catch (PDOException $e) {
    echo "エラー: {$e->getMessage()}\n";
    exit(1);
}

// =====================================
// 1. CREATE - データの挿入
// =====================================

echo "--- 1. CREATE - データの挿入 ---\n";

/**
 * INSERT文の基本
 */

echo "【単一レコードの挿入】\n";

// prepare + execute
$stmt = $pdo->prepare("
    INSERT INTO products (name, description, price, stock, category)
    VALUES (:name, :description, :price, :stock, :category)
");

$result = $stmt->execute([
    ':name' => 'ノートPC',
    ':description' => '高性能なノートパソコン',
    ':price' => 150000,
    ':stock' => 10,
    ':category' => 'Electronics',
]);

if ($result) {
    $productId = $pdo->lastInsertId();
    echo "商品を追加しました（ID: {$productId}）\n";
}

echo "\n【複数レコードの挿入】\n";

$products = [
    ['マウス', 'ワイヤレスマウス', 3000, 50, 'Electronics'],
    ['キーボード', 'メカニカルキーボード', 8000, 30, 'Electronics'],
    ['ノート', 'A4サイズのノート', 500, 100, 'Stationery'],
    ['ペン', 'ボールペン10本セット', 1000, 200, 'Stationery'],
];

$stmt = $pdo->prepare("
    INSERT INTO products (name, description, price, stock, category)
    VALUES (?, ?, ?, ?, ?)
");

$insertCount = 0;
foreach ($products as $product) {
    if ($stmt->execute($product)) {
        $insertCount++;
    }
}

echo "{$insertCount}件の商品を追加しました\n";

echo "\n【INSERT OR IGNORE】\n";

// 重複を無視して挿入（SQLite特有）
$pdo->exec("CREATE UNIQUE INDEX idx_product_name ON products(name)");

try {
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['マウス', '別のマウス', 2500, 20, 'Electronics']);
    echo "重複する商品名のため、挿入されませんでした\n";
} catch (PDOException $e) {
    echo "エラー: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 2. READ - データの読み取り
// =====================================

echo "--- 2. READ - データの読み取り ---\n";

/**
 * SELECT文のバリエーション
 */

echo "【全件取得】\n";
$stmt = $pdo->query("SELECT * FROM products");
$allProducts = $stmt->fetchAll();
echo "商品数: " . count($allProducts) . "件\n";

echo "\n【条件付き取得】\n";
$stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
$stmt->execute(['Electronics']);
$electronics = $stmt->fetchAll();
echo "Electronics カテゴリ: " . count($electronics) . "件\n";

foreach ($electronics as $product) {
    echo "  - {$product['name']}: " . number_format($product['price']) . "円\n";
}

echo "\n【範囲指定】\n";
$stmt = $pdo->prepare("SELECT * FROM products WHERE price BETWEEN ? AND ?");
$stmt->execute([1000, 10000]);

echo "価格1,000円〜10,000円の商品:\n";
while ($product = $stmt->fetch()) {
    echo "  - {$product['name']}: " . number_format($product['price']) . "円\n";
}

echo "\n【LIKE検索】\n";
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
$stmt->execute(['%ノート%']);

echo "'ノート'を含む商品:\n";
while ($product = $stmt->fetch()) {
    echo "  - {$product['name']}\n";
}

echo "\n【ORDER BY - ソート】\n";
$stmt = $pdo->query("SELECT * FROM products ORDER BY price DESC LIMIT 3");

echo "価格が高い順（上位3件）:\n";
while ($product = $stmt->fetch()) {
    echo "  {$product['name']}: " . number_format($product['price']) . "円\n";
}

echo "\n【LIMIT - 件数制限】\n";
$stmt = $pdo->query("SELECT * FROM products LIMIT 3 OFFSET 2");

echo "3件目から3件取得:\n";
while ($product = $stmt->fetch()) {
    echo "  - {$product['name']}\n";
}

echo "\n【COUNT - 件数取得】\n";
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category = ?");
$stmt->execute(['Stationery']);
$result = $stmt->fetch();
echo "Stationery カテゴリの商品数: {$result['count']}件\n";

echo "\n【集計関数】\n";
$stmt = $pdo->query("
    SELECT
        category,
        COUNT(*) as count,
        SUM(stock) as total_stock,
        AVG(price) as avg_price,
        MIN(price) as min_price,
        MAX(price) as max_price
    FROM products
    GROUP BY category
");

echo "カテゴリ別集計:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['category']}:\n";
    echo "    商品数: {$row['count']}件\n";
    echo "    総在庫: {$row['total_stock']}個\n";
    echo "    平均価格: " . number_format($row['avg_price']) . "円\n";
    echo "    最低価格: " . number_format($row['min_price']) . "円\n";
    echo "    最高価格: " . number_format($row['max_price']) . "円\n";
}

echo "\n【特定カラムのみ取得】\n";
$stmt = $pdo->query("SELECT id, name, price FROM products");

echo "ID、名前、価格のみ:\n";
while ($product = $stmt->fetch()) {
    echo "  [{$product['id']}] {$product['name']}: " . number_format($product['price']) . "円\n";
}

echo "\n";

// =====================================
// 3. UPDATE - データの更新
// =====================================

echo "--- 3. UPDATE - データの更新 ---\n";

/**
 * UPDATE文の基本
 */

echo "【単一レコードの更新】\n";
$stmt = $pdo->prepare("
    UPDATE products
    SET price = :price, updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
");

$stmt->execute([
    ':price' => 140000,
    ':id' => 1,
]);

$affectedRows = $stmt->rowCount();
echo "{$affectedRows}件の商品価格を更新しました\n";

// 更新結果を確認
$stmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
$stmt->execute([1]);
$product = $stmt->fetch();
echo "  {$product['name']}: " . number_format($product['price']) . "円\n";

echo "\n【複数カラムの更新】\n";
$stmt = $pdo->prepare("
    UPDATE products
    SET price = :price, stock = :stock, description = :description, updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
");

$stmt->execute([
    ':price' => 2800,
    ':stock' => 60,
    ':description' => '高精度ワイヤレスマウス',
    ':id' => 2,
]);

echo "商品情報を更新しました\n";

echo "\n【条件に基づく一括更新】\n";
$stmt = $pdo->prepare("
    UPDATE products
    SET price = price * 0.9, updated_at = CURRENT_TIMESTAMP
    WHERE category = ?
");

$stmt->execute(['Stationery']);
$affectedRows = $stmt->rowCount();
echo "Stationeryカテゴリの{$affectedRows}件の商品を10%割引にしました\n";

echo "\n【在庫の増減】\n";
$stmt = $pdo->prepare("
    UPDATE products
    SET stock = stock + :quantity, updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
");

// 在庫を10個増やす
$stmt->execute([':quantity' => 10, ':id' => 1]);
echo "商品ID 1の在庫を10個増やしました\n";

// 在庫を5個減らす
$stmt->execute([':quantity' => -5, ':id' => 1]);
echo "商品ID 1の在庫を5個減らしました\n";

echo "\n";

// =====================================
// 4. DELETE - データの削除
// =====================================

echo "--- 4. DELETE - データの削除 ---\n";

/**
 * DELETE文の基本
 */

echo "【単一レコードの削除】\n";

// 削除前に確認
$stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$stmt->execute([5]);
$product = $stmt->fetch();

if ($product) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([5]);
    $affectedRows = $stmt->rowCount();
    echo "'{$product['name']}'を削除しました（{$affectedRows}件）\n";
}

echo "\n【条件に基づく削除】\n";
$stmt = $pdo->prepare("DELETE FROM products WHERE stock = 0");
$stmt->execute();
$affectedRows = $stmt->rowCount();
echo "在庫0の商品を{$affectedRows}件削除しました\n";

echo "\n【複数条件での削除】\n";
$stmt = $pdo->prepare("DELETE FROM products WHERE category = ? AND price < ?");
$stmt->execute(['Stationery', 500]);
$affectedRows = $stmt->rowCount();
echo "Stationeryカテゴリで500円未満の商品を{$affectedRows}件削除しました\n";

echo "\n【削除前の件数確認】\n";
// 現在の商品数
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$beforeCount = $stmt->fetchColumn();
echo "削除前の商品数: {$beforeCount}件\n";

echo "\n";

// =====================================
// 5. トランザクションを使ったCRUD
// =====================================

echo "--- 5. トランザクションを使ったCRUD ---\n";

/**
 * 複数の操作をアトミックに実行
 */

echo "【注文処理のシミュレーション】\n";

try {
    $pdo->beginTransaction();

    // 商品の在庫を確認
    $stmt = $pdo->prepare("SELECT name, stock, price FROM products WHERE id = ?");
    $stmt->execute([1]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new RuntimeException("商品が見つかりません");
    }

    $orderQuantity = 3;
    if ($product['stock'] < $orderQuantity) {
        throw new RuntimeException("在庫が不足しています");
    }

    // 在庫を減らす
    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$orderQuantity, 1]);

    $totalPrice = $product['price'] * $orderQuantity;

    echo "注文を処理しました:\n";
    echo "  商品: {$product['name']}\n";
    echo "  数量: {$orderQuantity}個\n";
    echo "  合計: " . number_format($totalPrice) . "円\n";
    echo "  残在庫: " . ($product['stock'] - $orderQuantity) . "個\n";

    // コミット
    $pdo->commit();
    echo "トランザクションをコミットしました\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "エラー: {$e->getMessage()}\n";
    echo "トランザクションをロールバックしました\n";
}

echo "\n";

// =====================================
// 6. 安全なCRUD操作のヘルパー関数
// =====================================

echo "--- 6. 安全なCRUD操作のヘルパー関数 ---\n";

/**
 * レコードを挿入
 *
 * @param PDO $pdo PDOインスタンス
 * @param string $table テーブル名
 * @param array $data 挿入するデータ（カラム名 => 値）
 * @return int 挿入されたレコードのID
 */
function insertRecord(PDO $pdo, string $table, array $data): int
{
    $columns = array_keys($data);
    $placeholders = array_map(fn($col) => ":{$col}", $columns);

    $sql = sprintf(
        "INSERT INTO %s (%s) VALUES (%s)",
        $table,
        implode(', ', $columns),
        implode(', ', $placeholders)
    );

    $stmt = $pdo->prepare($sql);

    $params = [];
    foreach ($data as $key => $value) {
        $params[":{$key}"] = $value;
    }

    $stmt->execute($params);
    return (int)$pdo->lastInsertId();
}

/**
 * レコードを更新
 *
 * @param PDO $pdo PDOインスタンス
 * @param string $table テーブル名
 * @param array $data 更新するデータ（カラム名 => 値）
 * @param array $where 条件（カラム名 => 値）
 * @return int 更新された行数
 */
function updateRecord(PDO $pdo, string $table, array $data, array $where): int
{
    $setClause = [];
    foreach (array_keys($data) as $column) {
        $setClause[] = "{$column} = :{$column}";
    }

    $whereClause = [];
    foreach (array_keys($where) as $column) {
        $whereClause[] = "{$column} = :where_{$column}";
    }

    $sql = sprintf(
        "UPDATE %s SET %s WHERE %s",
        $table,
        implode(', ', $setClause),
        implode(' AND ', $whereClause)
    );

    $stmt = $pdo->prepare($sql);

    $params = [];
    foreach ($data as $key => $value) {
        $params[":{$key}"] = $value;
    }
    foreach ($where as $key => $value) {
        $params[":where_{$key}"] = $value;
    }

    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * レコードを削除
 *
 * @param PDO $pdo PDOインスタンス
 * @param string $table テーブル名
 * @param array $where 条件（カラム名 => 値）
 * @return int 削除された行数
 */
function deleteRecord(PDO $pdo, string $table, array $where): int
{
    $whereClause = [];
    foreach (array_keys($where) as $column) {
        $whereClause[] = "{$column} = :{$column}";
    }

    $sql = sprintf(
        "DELETE FROM %s WHERE %s",
        $table,
        implode(' AND ', $whereClause)
    );

    $stmt = $pdo->prepare($sql);

    $params = [];
    foreach ($where as $key => $value) {
        $params[":{$key}"] = $value;
    }

    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * レコードを検索
 *
 * @param PDO $pdo PDOインスタンス
 * @param string $table テーブル名
 * @param array $where 条件（カラム名 => 値）
 * @return array 検索結果
 */
function findRecords(PDO $pdo, string $table, array $where = []): array
{
    if (empty($where)) {
        $sql = "SELECT * FROM {$table}";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    $whereClause = [];
    foreach (array_keys($where) as $column) {
        $whereClause[] = "{$column} = :{$column}";
    }

    $sql = sprintf(
        "SELECT * FROM %s WHERE %s",
        $table,
        implode(' AND ', $whereClause)
    );

    $stmt = $pdo->prepare($sql);

    $params = [];
    foreach ($where as $key => $value) {
        $params[":{$key}"] = $value;
    }

    $stmt->execute($params);
    return $stmt->fetchAll();
}

// 使用例
echo "【ヘルパー関数を使った挿入】\n";
$newId = insertRecord($pdo, 'products', [
    'name' => 'モニター',
    'description' => '27インチモニター',
    'price' => 35000,
    'stock' => 15,
    'category' => 'Electronics',
]);
echo "新しい商品を追加しました（ID: {$newId}）\n";

echo "\n【ヘルパー関数を使った更新】\n";
$updatedRows = updateRecord(
    $pdo,
    'products',
    ['price' => 33000, 'updated_at' => date('Y-m-d H:i:s')],
    ['id' => $newId]
);
echo "{$updatedRows}件の商品を更新しました\n";

echo "\n【ヘルパー関数を使った検索】\n";
$electronics = findRecords($pdo, 'products', ['category' => 'Electronics']);
echo "Electronics カテゴリ: " . count($electronics) . "件\n";

echo "\n";

// =====================================
// 7. ベストプラクティス
// =====================================

echo "--- 7. CRUD操作のベストプラクティス ---\n";

echo "
【セキュリティ】

1. 必ずプリペアドステートメントを使用
   - 文字列連結でSQL文を構築しない
   - すべてのユーザー入力をプレースホルダーで処理

2. ホワイトリスト方式で検証
   - カラム名やテーブル名はハードコード
   - 動的に生成する場合はホワイトリストで検証

3. エラーメッセージに機密情報を含めない
   - 本番環境ではエラー詳細を隠す
   - ログに記録して開発者のみ確認

【パフォーマンス】

1. 必要なカラムのみ取得
   - SELECT * を避ける
   - 必要なカラムを明示的に指定

2. 適切なインデックスを設定
   - WHERE句で頻繁に使うカラムにINDEX
   - JOIN、ORDER BY でも効果的

3. バッチ処理を活用
   - 複数のINSERTは1つのトランザクションで
   - バルクINSERTやバルクUPDATEを検討

4. ページネーションを実装
   - LIMIT と OFFSET で大量データを分割
   - カーソルベースのページネーションも検討

【データ整合性】

1. トランザクションを使用
   - 関連する複数の操作はトランザクション内で
   - エラー時は必ずロールバック

2. 外部キー制約を活用
   - リレーションの整合性を保つ
   - CASCADE、RESTRICT などを適切に設定

3. updated_at を自動更新
   - UPDATE時に常に更新
   - トリガーやアプリケーションロジックで実装

4. ソフトデリート（論理削除）を検討
   - deleted_at カラムで削除フラグ管理
   - 物理削除は慎重に

【コーディング】

1. DRYを意識
   - 共通処理はヘルパー関数に
   - リポジトリパターンの活用

2. 名前付きプレースホルダーを使用
   - 可読性とメンテナンス性が向上

3. 影響を受けた行数を確認
   - rowCount() で結果を確認
   - 想定外の更新・削除を防ぐ

4. エラーハンドリングを徹底
   - try-catch で例外を捕捉
   - 適切なエラーメッセージを表示
";

echo "=== Phase 3.1: CRUD操作 - 完了 ===\n";
