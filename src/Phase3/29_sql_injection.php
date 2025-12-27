<?php

declare(strict_types=1);

/**
 * Phase 3.3: セキュリティ - SQLインジェクション対策
 *
 * このファイルでは、SQLインジェクションの危険性と対策方法を学習します。
 *
 * 学習内容:
 * - SQLインジェクションとは
 * - 危険なコード例（絶対に使わない！）
 * - プリペアドステートメントによる対策
 * - エスケープ処理の限界
 * - ホワイトリスト方式のバリデーション
 */

echo "=== Phase 3.3: SQLインジェクション対策 ===\n\n";

// データベース接続
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../../database/php_learning.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ データベースに接続しました\n\n";
} catch (PDOException $e) {
    die("接続エラー: " . $e->getMessage() . "\n");
}

echo "--- 1. SQLインジェクションとは ---\n";
/**
 * SQLインジェクションは、悪意のあるSQL文を注入することで、
 * データベースを不正に操作する攻撃手法です。
 *
 * 影響:
 * - データの不正取得（個人情報漏洩）
 * - データの改ざん・削除
 * - 認証のバイパス
 * - データベースサーバーの乗っ取り
 */
echo "SQLインジェクションは、悪意のあるSQL文を注入する攻撃です。\n";
echo "適切な対策を行わないと、重大なセキュリティ事故につながります。\n\n";

echo "--- 2. 危険なコード例（絶対に使用禁止！） ---\n";
/**
 * ❌ 危険: ユーザー入力を直接SQL文に埋め込む
 *
 * このコードは、SQLインジェクション攻撃に対して脆弱です。
 * 絶対に本番環境で使用してはいけません。
 */

// テストユーザーを作成
try {
    $pdo->exec("DROP TABLE IF EXISTS test_users");
    $pdo->exec("
        CREATE TABLE test_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0
        )
    ");

    // テストデータ
    $stmt = $pdo->prepare("INSERT INTO test_users (username, password, email, is_admin) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_ARGON2ID), 'admin@example.com', 1]);
    $stmt->execute(['user1', password_hash('pass123', PASSWORD_ARGON2ID), 'user1@example.com', 0]);
    $stmt->execute(['user2', password_hash('pass456', PASSWORD_ARGON2ID), 'user2@example.com', 0]);

    echo "✓ テストテーブルとデータを作成しました\n\n";
} catch (PDOException $e) {
    die("テーブル作成エラー: " . $e->getMessage() . "\n");
}

// ❌ 危険な例1: 文字列連結によるSQL構築
echo "❌ 危険な例1: 文字列連結（使用禁止）\n";
$unsafeUsername = "admin";
// 以下のコードは脆弱性のデモンストレーションです
// 実際のコードでは絶対に使用しないでください
$vulnerableQuery = "SELECT * FROM test_users WHERE username = '$unsafeUsername'";
echo "クエリ: $vulnerableQuery\n";
echo "→ このコードでは、ユーザー入力を直接SQL文に埋め込んでいます\n\n";

// 攻撃例を示す（実際には実行しない）
echo "攻撃例: ユーザーが \"admin' OR '1'='1\" と入力した場合\n";
$maliciousInput = "admin' OR '1'='1";
$attackQuery = "SELECT * FROM test_users WHERE username = '$maliciousInput'";
echo "クエリ: $attackQuery\n";
echo "→ このクエリは、全てのユーザーを返してしまいます！\n";
echo "→ 認証をバイパスできてしまいます\n\n";

// ❌ 危険な例2: 数値パラメータの文字列連結
echo "❌ 危険な例2: 数値パラメータも危険\n";
$userId = "1";
$vulnerableNumericQuery = "SELECT * FROM test_users WHERE id = $userId";
echo "クエリ: $vulnerableNumericQuery\n";
echo "→ 数値でも文字列連結は危険です\n\n";

echo "攻撃例: ユーザーが \"1 OR 1=1\" と入力した場合\n";
$maliciousId = "1 OR 1=1";
$attackNumericQuery = "SELECT * FROM test_users WHERE id = $maliciousId";
echo "クエリ: $attackNumericQuery\n";
echo "→ 全てのレコードが返されてしまいます\n\n";

echo "--- 3. プリペアドステートメントによる対策（推奨） ---\n";
/**
 * ✅ 安全: プリペアドステートメント（パラメータバインディング）
 *
 * プリペアドステートメントを使用すると、SQL文とデータが分離されるため、
 * SQLインジェクションを防ぐことができます。
 */

echo "✅ 安全な方法1: 位置プレースホルダー\n";
$username = "user1";
$stmt = $pdo->prepare("SELECT * FROM test_users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "検索ユーザー: {$user['username']}\n";
echo "→ プリペアドステートメントでは、データはSQL文として解釈されません\n\n";

echo "✅ 安全な方法2: 名前付きプレースホルダー\n";
$email = "user2@example.com";
$stmt = $pdo->prepare("SELECT * FROM test_users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "検索ユーザー: {$user['username']}\n";
echo "→ 名前付きプレースホルダーはコードの可読性が高い\n\n";

echo "✅ 攻撃を試みた場合の安全性\n";
$maliciousInput = "admin' OR '1'='1";
$stmt = $pdo->prepare("SELECT * FROM test_users WHERE username = ?");
$stmt->execute([$maliciousInput]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result === false) {
    echo "結果: ユーザーが見つかりませんでした\n";
    echo "→ 攻撃文字列がそのまま検索文字列として扱われ、攻撃は失敗します\n\n";
}

echo "--- 4. LIKE句でのプリペアドステートメント ---\n";
/**
 * LIKE句を使用する場合も、プリペアドステートメントを使用します。
 * ワイルドカード（%、_）は、パラメータ値に含めます。
 */

echo "✅ LIKE句の安全な使用方法\n";
$searchTerm = "user";
// ワイルドカードはパラメータ値に含める
$stmt = $pdo->prepare("SELECT username, email FROM test_users WHERE username LIKE ?");
$stmt->execute(["%$searchTerm%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "検索結果（'$searchTerm'を含むユーザー）:\n";
foreach ($users as $user) {
    echo "  - {$user['username']} ({$user['email']})\n";
}
echo "\n";

echo "--- 5. ORDER BY句とLIMIT句の安全な実装 ---\n";
/**
 * ORDER BY句やLIMIT句では、プレースホルダーを使用できない場合があります。
 * この場合は、ホワイトリスト方式でバリデーションを行います。
 */

echo "✅ ORDER BY句の安全な実装（ホワイトリスト方式）\n";

/**
 * ソートカラムをホワイトリストで検証する関数
 */
function validateSortColumn(string $column): string
{
    // 許可されたカラムのホワイトリスト
    $allowedColumns = ['id', 'username', 'email', 'created_at'];

    if (!in_array($column, $allowedColumns, true)) {
        throw new InvalidArgumentException("無効なソートカラム: $column");
    }

    return $column;
}

/**
 * ソート順をホワイトリストで検証する関数
 */
function validateSortOrder(string $order): string
{
    $order = strtoupper($order);

    if ($order !== 'ASC' && $order !== 'DESC') {
        throw new InvalidArgumentException("無効なソート順: $order");
    }

    return $order;
}

try {
    $sortColumn = validateSortColumn('username');
    $sortOrder = validateSortOrder('DESC');

    // ホワイトリストで検証済みの値は、SQL文に直接埋め込んでも安全
    $sql = "SELECT username, email FROM test_users ORDER BY $sortColumn $sortOrder";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "ソート結果（{$sortColumn} {$sortOrder}）:\n";
    foreach ($users as $user) {
        echo "  - {$user['username']}\n";
    }
    echo "\n";
} catch (InvalidArgumentException $e) {
    echo "エラー: {$e->getMessage()}\n\n";
}

echo "❌ ホワイトリストなしの危険な例\n";
try {
    // 攻撃を試みる
    $maliciousSortColumn = "username; DROP TABLE test_users--";
    validateSortColumn($maliciousSortColumn);
} catch (InvalidArgumentException $e) {
    echo "攻撃を検出: {$e->getMessage()}\n";
    echo "→ ホワイトリストにより攻撃を防ぎました\n\n";
}

echo "--- 6. LIMIT句とOFFSET句の安全な実装 ---\n";
/**
 * LIMIT句とOFFSET句では、整数値のバリデーションを行います。
 */

echo "✅ LIMIT句の安全な実装\n";

/**
 * ページネーションパラメータを検証する関数
 */
function validatePagination(mixed $page, mixed $perPage): array
{
    // 整数に変換してバリデーション
    $page = filter_var($page, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'default' => 1]
    ]);

    $perPage = filter_var($perPage, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 100, 'default' => 10]
    ]);

    if ($page === false || $perPage === false) {
        throw new InvalidArgumentException("無効なページネーションパラメータ");
    }

    return [
        'limit' => $perPage,
        'offset' => ($page - 1) * $perPage
    ];
}

try {
    ['limit' => $limit, 'offset' => $offset] = validatePagination(1, 2);

    // バリデーション済みの整数値は安全
    $sql = "SELECT username, email FROM test_users LIMIT $limit OFFSET $offset";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "ページネーション結果（ページ1、2件ずつ）:\n";
    foreach ($users as $user) {
        echo "  - {$user['username']}\n";
    }
    echo "\n";
} catch (InvalidArgumentException $e) {
    echo "エラー: {$e->getMessage()}\n\n";
}

echo "--- 7. IN句での安全な実装 ---\n";
/**
 * IN句で複数の値を使用する場合も、プリペアドステートメントを使用します。
 */

echo "✅ IN句の安全な使用方法\n";
$userIds = [1, 2];

// プレースホルダーを動的に生成
$placeholders = str_repeat('?,', count($userIds) - 1) . '?';
$sql = "SELECT username, email FROM test_users WHERE id IN ($placeholders)";

$stmt = $pdo->prepare($sql);
$stmt->execute($userIds);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "検索結果（ID: " . implode(', ', $userIds) . "）:\n";
foreach ($users as $user) {
    echo "  - {$user['username']}\n";
}
echo "\n";

echo "--- 8. 動的なテーブル名・カラム名の安全な実装 ---\n";
/**
 * テーブル名やカラム名を動的に変更する必要がある場合は、
 * ホワイトリスト方式で検証します。
 *
 * プリペアドステートメントは、値のプレースホルダーにのみ使用でき、
 * テーブル名やカラム名には使用できません。
 */

echo "✅ テーブル名の安全な実装\n";

/**
 * テーブル名を検証する関数
 */
function validateTableName(string $tableName): string
{
    // 許可されたテーブルのホワイトリスト
    $allowedTables = ['test_users', 'posts', 'comments'];

    if (!in_array($tableName, $allowedTables, true)) {
        throw new InvalidArgumentException("無効なテーブル名: $tableName");
    }

    return $tableName;
}

try {
    $tableName = validateTableName('test_users');

    // ホワイトリストで検証済みのテーブル名は安全
    $sql = "SELECT COUNT(*) FROM $tableName";
    $count = $pdo->query($sql)->fetchColumn();

    echo "テーブル '$tableName' のレコード数: $count\n\n";
} catch (InvalidArgumentException $e) {
    echo "エラー: {$e->getMessage()}\n\n";
}

echo "--- 9. エスケープ処理の限界 ---\n";
/**
 * PDO::quote()メソッドによるエスケープは、
 * プリペアドステートメントの代替として推奨されません。
 *
 * 理由:
 * - エスケープ漏れのリスク
 * - データベースごとのエスケープルールの違い
 * - パフォーマンスの問題
 */

echo "⚠️ PDO::quote()の使用例（非推奨）\n";
$username = "user1";
$escapedUsername = $pdo->quote($username);
echo "エスケープ後: $escapedUsername\n";
$sql = "SELECT * FROM test_users WHERE username = $escapedUsername";
$stmt = $pdo->query($sql);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "検索ユーザー: {$user['username']}\n";
echo "→ この方法は機能しますが、プリペアドステートメントの使用を推奨します\n\n";

echo "--- 10. まとめ：SQLインジェクション対策のベストプラクティス ---\n";
echo "✅ 推奨される対策:\n";
echo "  1. プリペアドステートメントを常に使用する\n";
echo "  2. ユーザー入力を絶対にSQL文に直接埋め込まない\n";
echo "  3. ORDER BY、LIMIT、テーブル名はホワイトリスト方式で検証\n";
echo "  4. 数値パラメータも必ず検証する\n";
echo "  5. 最小権限の原則（データベースユーザーの権限を最小限に）\n\n";

echo "❌ 避けるべき方法:\n";
echo "  1. 文字列連結によるSQL文の構築\n";
echo "  2. ユーザー入力を直接SQL文に埋め込む\n";
echo "  3. PDO::quote()のみに依存する\n";
echo "  4. 入力値の検証を怠る\n\n";

// クリーンアップ
// すべてのステートメントを解放
$stmt = null;

$pdo->exec("DROP TABLE IF EXISTS test_users");
echo "✓ テストテーブルを削除しました\n";

echo "\n=== Phase 3.3: SQLインジェクション対策 完了 ===\n";
