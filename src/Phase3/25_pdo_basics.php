<?php

declare(strict_types=1);

/**
 * Phase 3.1: データベース操作 - PDOの基礎
 *
 * PDO (PHP Data Objects) の基本的な使い方を学習します。
 * PDOはデータベースアクセスのための軽量で一貫性のあるインターフェースを提供します。
 */

echo "=== Phase 3.1: PDOの基礎 ===\n\n";

// =====================================
// 1. PDOとは
// =====================================

echo "--- 1. PDOとは ---\n";

/**
 * PDO (PHP Data Objects) の特徴
 *
 * 1. データベース抽象化層
 *    - MySQL, PostgreSQL, SQLite など複数のデータベースに対応
 *    - 統一されたAPIでデータベース操作が可能
 *
 * 2. プリペアドステートメント
 *    - SQLインジェクション対策
 *    - パフォーマンスの向上
 *
 * 3. 例外処理
 *    - エラーハンドリングが容易
 *    - トランザクション管理
 *
 * 4. オブジェクト指向API
 *    - モダンなPHPコードとの親和性が高い
 */

echo "PDOの特徴:\n";
echo "  - データベース抽象化層\n";
echo "  - プリペアドステートメントのサポート\n";
echo "  - 例外ベースのエラーハンドリング\n";
echo "  - オブジェクト指向API\n";

echo "\n";

// =====================================
// 2. データベース接続
// =====================================

echo "--- 2. データベース接続 ---\n";

/**
 * データベースに接続
 *
 * DSN (Data Source Name) の形式:
 * - SQLite: "sqlite:/path/to/database.db"
 * - MySQL: "mysql:host=localhost;dbname=database;charset=utf8mb4"
 * - PostgreSQL: "pgsql:host=localhost;dbname=database"
 */

try {
    // SQLiteデータベースに接続（メモリ上に作成）
    $pdo = new PDO('sqlite::memory:');

    // エラーモードを例外に設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // デフォルトのフェッチモードを連想配列に設定
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "データベース接続に成功しました\n";
    echo "PDOドライバ: {$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)}\n";
} catch (PDOException $e) {
    echo "接続エラー: {$e->getMessage()}\n";
    exit(1);
}

echo "\n";

// =====================================
// 3. PDOの設定
// =====================================

echo "--- 3. PDOの設定 ---\n";

/**
 * 重要な設定オプション
 */

// エラーモード
// - PDO::ERRMODE_SILENT: エラーコードのみ設定（デフォルト）
// - PDO::ERRMODE_WARNING: E_WARNING を発生
// - PDO::ERRMODE_EXCEPTION: PDOException をスロー（推奨）
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// フェッチモード
// - PDO::FETCH_ASSOC: 連想配列
// - PDO::FETCH_NUM: 数値インデックス配列
// - PDO::FETCH_BOTH: 両方（デフォルト）
// - PDO::FETCH_OBJ: オブジェクト
// - PDO::FETCH_CLASS: クラスのインスタンス
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// エミュレートプリペア（MySQLの場合は無効化推奨）
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// 永続的接続（通常は無効）
// $pdo->setAttribute(PDO::ATTR_PERSISTENT, false);

echo "PDO設定:\n";
echo "  エラーモード: EXCEPTION\n";
echo "  フェッチモード: ASSOC\n";
echo "  エミュレートプリペア: 無効\n";

echo "\n";

// =====================================
// 4. テーブルの作成
// =====================================

echo "--- 4. テーブルの作成 ---\n";

/**
 * exec() メソッドでDDL文を実行
 * 返り値: 影響を受けた行数
 */

$sql = "
    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        age INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
";

$affectedRows = $pdo->exec($sql);
echo "usersテーブルを作成しました\n";

// 別のテーブルも作成
$sql = "
    CREATE TABLE posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        status TEXT DEFAULT 'draft',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )
";

$pdo->exec($sql);
echo "postsテーブルを作成しました\n";

echo "\n";

// =====================================
// 5. シンプルなクエリ実行
// =====================================

echo "--- 5. シンプルなクエリ実行 ---\n";

/**
 * query() メソッド
 * - 単純なSELECT文の実行
 * - プレースホルダーを使わない場合に使用
 * - 返り値: PDOStatementオブジェクト
 */

// データを挿入（プリペアドステートメントは後述）
$pdo->exec("
    INSERT INTO users (username, email, password, age) VALUES
    ('alice', 'alice@example.com', 'password1', 25),
    ('bob', 'bob@example.com', 'password2', 30),
    ('charlie', 'charlie@example.com', 'password3', 35)
");

echo "サンプルデータを挿入しました\n\n";

// 全ユーザーを取得
$stmt = $pdo->query("SELECT * FROM users");

echo "全ユーザー:\n";
while ($row = $stmt->fetch()) {
    echo "  ID: {$row['id']}, ユーザー名: {$row['username']}, メール: {$row['email']}\n";
}

echo "\n";

// =====================================
// 6. プリペアドステートメント
// =====================================

echo "--- 6. プリペアドステートメント ---\n";

/**
 * プリペアドステートメントの利点
 *
 * 1. SQLインジェクション対策
 *    - パラメータを安全にエスケープ
 *
 * 2. パフォーマンス向上
 *    - クエリの解析が1回のみ
 *    - 複数回実行する場合に効率的
 *
 * 3. 可読性
 *    - SQL文とデータを分離
 */

echo "【プリペアドステートメントの基本】\n\n";

// prepare() でステートメントを準備
$stmt = $pdo->prepare("SELECT * FROM users WHERE age >= ?");

// execute() でパラメータをバインドして実行
$stmt->execute([30]);

echo "年齢30歳以上のユーザー:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['username']} ({$row['age']}歳)\n";
}

echo "\n";

// =====================================
// 7. プレースホルダー
// =====================================

echo "--- 7. プレースホルダー ---\n";

/**
 * プレースホルダーの種類
 *
 * 1. 位置プレースホルダー (?)
 *    - 位置で指定
 *    - シンプルだが順序に注意
 *
 * 2. 名前付きプレースホルダー (:name)
 *    - 名前で指定
 *    - 可読性が高い（推奨）
 */

echo "【位置プレースホルダー】\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND age > ?");
$stmt->execute(['alice', 20]);

if ($row = $stmt->fetch()) {
    echo "  ユーザー名: {$row['username']}, 年齢: {$row['age']}\n";
}

echo "\n【名前付きプレースホルダー】\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND age > :age");
$stmt->execute([
    ':username' => 'bob',
    ':age' => 20,
]);

if ($row = $stmt->fetch()) {
    echo "  ユーザー名: {$row['username']}, 年齢: {$row['age']}\n";
}

echo "\n";

// =====================================
// 8. bindParam と bindValue
// =====================================

echo "--- 8. bindParam と bindValue ---\n";

/**
 * パラメータバインドの2つの方法
 *
 * 1. bindValue()
 *    - 値をバインド
 *    - 即座に値がコピーされる
 *
 * 2. bindParam()
 *    - 変数をバインド
 *    - execute() 時の変数の値を使用
 *    - 参照渡し
 */

echo "【bindValue の例】\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindValue(':username', 'charlie');
$stmt->execute();

if ($row = $stmt->fetch()) {
    echo "  ユーザー: {$row['username']}\n";
}

echo "\n【bindParam の例】\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE age >= :age");
$age = 25;
$stmt->bindParam(':age', $age, PDO::PARAM_INT);
$stmt->execute();

echo "  年齢{$age}歳以上: " . $stmt->rowCount() . "人\n";

// 変数を変更して再実行
$age = 30;
$stmt->execute();
echo "  年齢{$age}歳以上: " . $stmt->rowCount() . "人\n";

echo "\n";

// =====================================
// 9. データ型の指定
// =====================================

echo "--- 9. データ型の指定 ---\n";

/**
 * PDOのデータ型定数
 *
 * - PDO::PARAM_BOOL: 真偽値
 * - PDO::PARAM_NULL: NULL
 * - PDO::PARAM_INT: 整数
 * - PDO::PARAM_STR: 文字列（デフォルト）
 * - PDO::PARAM_LOB: ラージオブジェクト
 */

$stmt = $pdo->prepare("INSERT INTO users (username, email, password, age) VALUES (?, ?, ?, ?)");

// 明示的に型を指定
$stmt->bindValue(1, 'david', PDO::PARAM_STR);
$stmt->bindValue(2, 'david@example.com', PDO::PARAM_STR);
$stmt->bindValue(3, 'password4', PDO::PARAM_STR);
$stmt->bindValue(4, 28, PDO::PARAM_INT);
$stmt->execute();

echo "データ型を指定してユーザーを追加しました\n";

echo "\n";

// =====================================
// 10. フェッチモード
// =====================================

echo "--- 10. フェッチモード ---\n";

/**
 * 様々なフェッチモード
 */

$stmt = $pdo->query("SELECT * FROM users LIMIT 3");

echo "【FETCH_ASSOC - 連想配列】\n";
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['username']}: {$row['email']}\n";
}

echo "\n【FETCH_NUM - 数値インデックス配列】\n";
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "  [{$row[0]}] {$row[1]}: {$row[2]}\n";
}

echo "\n【FETCH_OBJ - オブジェクト】\n";
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "  {$row->username}: {$row->email}\n";
}

echo "\n【fetchAll - すべての行を取得】\n";
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "  取得件数: " . count($users) . "件\n";

echo "\n【fetchColumn - 特定のカラムを取得】\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$count = $stmt->fetchColumn();
echo "  ユーザー総数: {$count}人\n";

echo "\n";

// =====================================
// 11. クラスへのマッピング
// =====================================

echo "--- 11. クラスへのマッピング ---\n";

/**
 * ユーザーエンティティクラス
 */
class UserEntity
{
    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public ?int $age = null;
    public string $created_at;

    /**
     * ユーザー情報を表示
     */
    public function display(): string
    {
        return "User#{$this->id}: {$this->username} ({$this->email})";
    }

    /**
     * 年齢を取得（未設定の場合は"不明"）
     */
    public function getAgeDisplay(): string
    {
        return $this->age !== null ? "{$this->age}歳" : "不明";
    }
}

// FETCH_CLASS でクラスのインスタンスとして取得
$stmt = $pdo->query("SELECT * FROM users");
$stmt->setFetchMode(PDO::FETCH_CLASS, UserEntity::class);

echo "ユーザーエンティティ:\n";
while ($user = $stmt->fetch()) {
    echo "  {$user->display()} - {$user->getAgeDisplay()}\n";
}

echo "\n";

// =====================================
// 12. 最後に挿入されたID
// =====================================

echo "--- 12. 最後に挿入されたID ---\n";

$stmt = $pdo->prepare("INSERT INTO users (username, email, password, age) VALUES (?, ?, ?, ?)");
$stmt->execute(['eve', 'eve@example.com', 'password5', 22]);

$lastInsertId = $pdo->lastInsertId();
echo "挿入されたユーザーID: {$lastInsertId}\n";

echo "\n";

// =====================================
// 13. トランザクション
// =====================================

echo "--- 13. トランザクション ---\n";

/**
 * トランザクションの基本
 *
 * - beginTransaction(): トランザクション開始
 * - commit(): コミット（確定）
 * - rollBack(): ロールバック（取り消し）
 */

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // 複数の操作を実行
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, age) VALUES (?, ?, ?, ?)");
    $stmt->execute(['frank', 'frank@example.com', 'password6', 40]);
    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, 'My First Post', 'Hello, World!', 'published']);

    // コミット
    $pdo->commit();
    echo "トランザクションをコミットしました\n";
} catch (PDOException $e) {
    // エラーが発生した場合はロールバック
    $pdo->rollBack();
    echo "トランザクションをロールバックしました: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 14. エラーハンドリング
// =====================================

echo "--- 14. エラーハンドリング ---\n";

/**
 * PDOException を使ったエラーハンドリング
 */

try {
    // 存在しないテーブルにアクセス
    $stmt = $pdo->query("SELECT * FROM non_existent_table");
} catch (PDOException $e) {
    echo "データベースエラー:\n";
    echo "  メッセージ: {$e->getMessage()}\n";
    echo "  コード: {$e->getCode()}\n";
}

echo "\n";

try {
    // ユニーク制約違反
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['alice', 'alice2@example.com', 'password7']);
} catch (PDOException $e) {
    echo "挿入エラー（ユニーク制約違反）:\n";
    echo "  {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 15. 接続の管理
// =====================================

echo "--- 15. 接続の管理 ---\n";

/**
 * データベース接続クラス
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * コンストラクタ（シングルトンパターン）
     */
    private function __construct()
    {
    }

    /**
     * PDOインスタンスを取得（シングルトン）
     *
     * @param string $dsn DSN
     * @param string|null $username ユーザー名
     * @param string|null $password パスワード
     * @return PDO PDOインスタンス
     */
    public static function getInstance(
        string $dsn = 'sqlite::memory:',
        ?string $username = null,
        ?string $password = null
    ): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO($dsn, $username, $password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                throw new RuntimeException("データベース接続エラー: {$e->getMessage()}");
            }
        }

        return self::$instance;
    }

    /**
     * 接続をクローズ（インスタンスをnullに）
     */
    public static function closeConnection(): void
    {
        self::$instance = null;
    }
}

echo "Databaseクラス（シングルトンパターン）を定義しました\n";

echo "\n";

// =====================================
// 16. ベストプラクティス
// =====================================

echo "--- 16. PDOのベストプラクティス ---\n";

echo "
【セキュリティ】

1. プリペアドステートメントを使用
   - 常にプレースホルダーを使用
   - 文字列連結でSQL文を構築しない

2. エラーモードを例外に設定
   - setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)
   - try-catch でエラーハンドリング

3. エミュレートプリペアを無効化（MySQL）
   - setAttribute(PDO::ATTR_EMULATE_PREPARES, false)
   - 真のプリペアドステートメントを使用

4. 接続情報を環境変数で管理
   - ハードコードしない
   - .env ファイルや環境変数を使用

【パフォーマンス】

1. 適切なフェッチモードを選択
   - 必要なデータ形式に応じて選択
   - デフォルトフェッチモードを設定

2. トランザクションを活用
   - 複数の操作をまとめる
   - ロールバック可能に

3. プリペアドステートメントの再利用
   - 繰り返し実行する場合は1回だけprepare

4. 適切なインデックスを設定
   - 頻繁に検索するカラムにINDEX

【コーディング】

1. 名前付きプレースホルダーを使用
   - 可読性が向上
   - メンテナンスしやすい

2. シングルトンパターンで接続管理
   - 接続の再利用
   - リソースの節約

3. 型を明示的に指定
   - bindParam/bindValue で型を指定
   - 予期しない変換を防ぐ

4. トランザクションでデータ整合性を保つ
   - 関連する操作はトランザクション内で
   - エラー時は必ずロールバック
";

echo "=== Phase 3.1: PDOの基礎 - 完了 ===\n";
