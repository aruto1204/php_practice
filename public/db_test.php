<?php

declare(strict_types=1);

/**
 * データベース接続テストプログラム
 *
 * SQLiteデータベースへの接続と基本操作をテストします。
 */

echo "==================================" . PHP_EOL;
echo "  データベース接続テスト" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

try {
    // データベースファイルのパス
    $dbPath = __DIR__ . '/../database/test.db';
    $dbDir = dirname($dbPath);

    // データベースディレクトリが存在しない場合は作成
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
        echo "✓ データベースディレクトリを作成しました" . PHP_EOL;
    }

    // SQLiteデータベースに接続
    $pdo = new PDO("sqlite:{$dbPath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "✓ データベースに接続しました" . PHP_EOL;
    echo "  データベース: SQLite" . PHP_EOL;
    echo "  パス: {$dbPath}" . PHP_EOL;
    echo PHP_EOL;

    // テーブルの作成
    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
    SQL;

    $pdo->exec($sql);
    echo "✓ テーブル 'users' を作成しました" . PHP_EOL;
    echo PHP_EOL;

    // サンプルデータの挿入
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO users (name, email) VALUES (:name, :email)');

    $users = [
        ['name' => '山田太郎', 'email' => 'taro@example.com'],
        ['name' => '佐藤花子', 'email' => 'hanako@example.com'],
        ['name' => '鈴木一郎', 'email' => 'ichiro@example.com'],
    ];

    foreach ($users as $user) {
        $stmt->execute($user);
    }

    echo "✓ サンプルデータを挿入しました" . PHP_EOL;
    echo PHP_EOL;

    // データの取得
    $stmt = $pdo->query('SELECT * FROM users');
    $results = $stmt->fetchAll();

    echo "【登録ユーザー一覧】" . PHP_EOL;
    echo str_repeat('-', 50) . PHP_EOL;
    printf("%-5s %-15s %-25s%s", "ID", "名前", "メール", PHP_EOL);
    echo str_repeat('-', 50) . PHP_EOL;

    foreach ($results as $row) {
        printf(
            "%-5d %-15s %-25s%s",
            $row['id'],
            $row['name'],
            $row['email'],
            PHP_EOL
        );
    }

    echo str_repeat('-', 50) . PHP_EOL;
    echo "総件数: " . count($results) . " 件" . PHP_EOL;
    echo PHP_EOL;

    // 統計情報
    echo "【データベース統計】" . PHP_EOL;
    echo "  総レコード数: " . count($results) . PHP_EOL;
    echo "  データベースサイズ: " . number_format(filesize($dbPath)) . " bytes" . PHP_EOL;
    echo PHP_EOL;

    echo "✓ データベース接続テストが正常に完了しました！" . PHP_EOL;
    echo "==================================" . PHP_EOL;

} catch (PDOException $e) {
    echo "✗ データベースエラー: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo "✗ エラー: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
