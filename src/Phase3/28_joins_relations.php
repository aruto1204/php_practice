<?php

declare(strict_types=1);

/**
 * Phase 3.2: データベース操作 - JOIN操作とリレーションシップ
 *
 * 様々なJOINの使い方とテーブルのリレーションシップ管理を学習します。
 * INNER JOIN、LEFT JOIN、複数テーブルの結合、サブクエリなどを実践します。
 */

echo "=== Phase 3.2: JOIN操作とリレーションシップ ===\n\n";

// データベース接続
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // テーブル作成
    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            status TEXT DEFAULT 'active',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            status TEXT DEFAULT 'draft',
            views INTEGER DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT
        )
    ");

    $pdo->exec("
        CREATE TABLE post_categories (
            post_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            PRIMARY KEY (post_id, category_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        )
    ");

    $pdo->exec("
        CREATE TABLE post_tags (
            post_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            PRIMARY KEY (post_id, tag_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ");

    echo "データベースとテーブルを準備しました\n\n";
} catch (PDOException $e) {
    echo "エラー: {$e->getMessage()}\n";
    exit(1);
}

// サンプルデータ挿入
$pdo->exec("INSERT INTO users (username, email) VALUES ('alice', 'alice@example.com')");
$pdo->exec("INSERT INTO users (username, email) VALUES ('bob', 'bob@example.com')");
$pdo->exec("INSERT INTO users (username, email) VALUES ('charlie', 'charlie@example.com')");
$pdo->exec("INSERT INTO users (username, email) VALUES ('david', 'david@example.com')");

$pdo->exec("INSERT INTO posts (user_id, title, content, status, views) VALUES (1, 'Hello World', 'My first post', 'published', 100)");
$pdo->exec("INSERT INTO posts (user_id, title, content, status, views) VALUES (1, 'PHP Tutorial', 'Learning PHP', 'published', 50)");
$pdo->exec("INSERT INTO posts (user_id, title, content, status, views) VALUES (2, 'Database Guide', 'SQL basics', 'published', 75)");
$pdo->exec("INSERT INTO posts (user_id, title, content, status, views) VALUES (3, 'Draft Post', 'Work in progress', 'draft', 0)");

$pdo->exec("INSERT INTO comments (post_id, user_id, content) VALUES (1, 2, 'Great post!')");
$pdo->exec("INSERT INTO comments (post_id, user_id, content) VALUES (1, 3, 'Thanks for sharing')");
$pdo->exec("INSERT INTO comments (post_id, user_id, content) VALUES (2, 2, 'Very helpful')");
$pdo->exec("INSERT INTO comments (post_id, user_id, content) VALUES (3, 1, 'Nice tutorial')");

$pdo->exec("INSERT INTO categories (name, description) VALUES ('Programming', 'Programming topics')");
$pdo->exec("INSERT INTO categories (name, description) VALUES ('Database', 'Database topics')");
$pdo->exec("INSERT INTO categories (name, description) VALUES ('Web', 'Web development')");

$pdo->exec("INSERT INTO post_categories (post_id, category_id) VALUES (1, 1)");
$pdo->exec("INSERT INTO post_categories (post_id, category_id) VALUES (1, 3)");
$pdo->exec("INSERT INTO post_categories (post_id, category_id) VALUES (2, 1)");
$pdo->exec("INSERT INTO post_categories (post_id, category_id) VALUES (3, 2)");

$pdo->exec("INSERT INTO tags (name) VALUES ('php')");
$pdo->exec("INSERT INTO tags (name) VALUES ('sql')");
$pdo->exec("INSERT INTO tags (name) VALUES ('beginner')");
$pdo->exec("INSERT INTO tags (name) VALUES ('tutorial')");

$pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (1, 1)");
$pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (1, 3)");
$pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (2, 1)");
$pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (2, 4)");
$pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (3, 2)");
$pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (3, 4)");

echo "サンプルデータを挿入しました\n\n";

// =====================================
// 1. INNER JOIN - 内部結合
// =====================================

echo "--- 1. INNER JOIN - 内部結合 ---\n";

/**
 * INNER JOIN: 両方のテーブルに一致するレコードのみを返す
 */

echo "【基本的なINNER JOIN】\n";
$stmt = $pdo->query("
    SELECT
        posts.id,
        posts.title,
        users.username,
        posts.views
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.status = 'published'
    ORDER BY posts.views DESC
");

echo "投稿一覧（ユーザー名付き）:\n";
while ($row = $stmt->fetch()) {
    echo "  [{$row['id']}] {$row['title']} by {$row['username']} ({$row['views']} views)\n";
}

echo "\n【複数テーブルのJOIN】\n";
$stmt = $pdo->query("
    SELECT
        comments.id,
        comments.content,
        posts.title AS post_title,
        users.username AS commenter
    FROM comments
    INNER JOIN posts ON comments.post_id = posts.id
    INNER JOIN users ON comments.user_id = users.id
    ORDER BY comments.created_at DESC
");

echo "コメント一覧:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['commenter']} on '{$row['post_title']}': {$row['content']}\n";
}

echo "\n";

// =====================================
// 2. LEFT JOIN - 左外部結合
// =====================================

echo "--- 2. LEFT JOIN - 左外部結合 ---\n";

/**
 * LEFT JOIN: 左側のテーブルのすべてのレコードを返す
 * 右側のテーブルに一致するレコードがない場合はNULLを返す
 */

echo "【ユーザーと投稿数】\n";
$stmt = $pdo->query("
    SELECT
        users.id,
        users.username,
        COUNT(posts.id) AS post_count
    FROM users
    LEFT JOIN posts ON users.id = posts.user_id
    GROUP BY users.id, users.username
    ORDER BY post_count DESC
");

echo "ユーザー別投稿数:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['username']}: {$row['post_count']}件\n";
}

echo "\n【投稿とコメント数】\n";
$stmt = $pdo->query("
    SELECT
        posts.id,
        posts.title,
        users.username,
        COUNT(comments.id) AS comment_count
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    LEFT JOIN comments ON posts.id = comments.post_id
    WHERE posts.status = 'published'
    GROUP BY posts.id, posts.title, users.username
    ORDER BY comment_count DESC
");

echo "投稿別コメント数:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['title']} by {$row['username']}: {$row['comment_count']}件\n";
}

echo "\n";

// =====================================
// 3. 多対多のリレーション
// =====================================

echo "--- 3. 多対多のリレーション ---\n";

/**
 * 中間テーブルを使った多対多の関係
 */

echo "【投稿とカテゴリ】\n";
$stmt = $pdo->query("
    SELECT
        posts.id,
        posts.title,
        GROUP_CONCAT(categories.name, ', ') AS categories
    FROM posts
    INNER JOIN post_categories ON posts.id = post_categories.post_id
    INNER JOIN categories ON post_categories.category_id = categories.id
    GROUP BY posts.id, posts.title
");

echo "投稿のカテゴリ:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['title']}: {$row['categories']}\n";
}

echo "\n【投稿とタグ】\n";
$stmt = $pdo->query("
    SELECT
        posts.id,
        posts.title,
        GROUP_CONCAT(tags.name, ', ') AS tags
    FROM posts
    LEFT JOIN post_tags ON posts.id = post_tags.post_id
    LEFT JOIN tags ON post_tags.tag_id = tags.id
    GROUP BY posts.id, posts.title
");

echo "投稿のタグ:\n";
while ($row = $stmt->fetch()) {
    $tagsDisplay = $row['tags'] ?? 'なし';
    echo "  {$row['title']}: {$tagsDisplay}\n";
}

echo "\n【カテゴリ別投稿数】\n";
$stmt = $pdo->query("
    SELECT
        categories.name,
        COUNT(post_categories.post_id) AS post_count
    FROM categories
    LEFT JOIN post_categories ON categories.id = post_categories.category_id
    GROUP BY categories.id, categories.name
    ORDER BY post_count DESC
");

echo "カテゴリ別投稿数:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['name']}: {$row['post_count']}件\n";
}

echo "\n";

// =====================================
// 4. サブクエリ
// =====================================

echo "--- 4. サブクエリ ---\n";

/**
 * サブクエリ: クエリの中にクエリを入れる
 */

echo "【最も閲覧数の多い投稿のユーザー】\n";
$stmt = $pdo->query("
    SELECT
        users.username,
        posts.title,
        posts.views
    FROM users
    INNER JOIN posts ON users.id = posts.user_id
    WHERE posts.views = (SELECT MAX(views) FROM posts)
");

$row = $stmt->fetch();
if ($row) {
    echo "  最多閲覧: {$row['title']} by {$row['username']} ({$row['views']} views)\n";
}

echo "\n【平均以上の閲覧数の投稿】\n";
$stmt = $pdo->query("
    SELECT
        title,
        views
    FROM posts
    WHERE views > (SELECT AVG(views) FROM posts)
    ORDER BY views DESC
");

echo "平均以上の閲覧数:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['title']}: {$row['views']} views\n";
}

echo "\n【コメントがある投稿のみ（IN句）】\n";
$stmt = $pdo->query("
    SELECT
        posts.title,
        users.username
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.id IN (SELECT DISTINCT post_id FROM comments)
");

echo "コメントがある投稿:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['title']} by {$row['username']}\n";
}

echo "\n【コメントがない投稿（NOT IN句）】\n";
$stmt = $pdo->query("
    SELECT
        posts.title,
        users.username
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.id NOT IN (SELECT DISTINCT post_id FROM comments)
");

echo "コメントがない投稿:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['title']} by {$row['username']}\n";
}

echo "\n";

// =====================================
// 5. 集計とグルーピング
// =====================================

echo "--- 5. 集計とグルーピング ---\n";

echo "【ユーザー別の統計】\n";
$stmt = $pdo->query("
    SELECT
        users.username,
        COUNT(DISTINCT posts.id) AS post_count,
        COUNT(DISTINCT comments.id) AS comment_count,
        COALESCE(SUM(posts.views), 0) AS total_views
    FROM users
    LEFT JOIN posts ON users.id = posts.user_id
    LEFT JOIN comments ON users.id = comments.user_id
    GROUP BY users.id, users.username
    ORDER BY post_count DESC, comment_count DESC
");

echo "ユーザー統計:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['username']}:\n";
    echo "    投稿: {$row['post_count']}件\n";
    echo "    コメント: {$row['comment_count']}件\n";
    echo "    総閲覧数: {$row['total_views']}\n";
}

echo "\n【投稿の詳細統計】\n";
$stmt = $pdo->query("
    SELECT
        posts.title,
        users.username AS author,
        posts.views,
        COUNT(comments.id) AS comment_count,
        posts.created_at
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    LEFT JOIN comments ON posts.id = comments.post_id
    WHERE posts.status = 'published'
    GROUP BY posts.id, posts.title, users.username, posts.views, posts.created_at
    HAVING comment_count > 0
    ORDER BY posts.views DESC
");

echo "投稿統計（コメントあり）:\n";
while ($row = $stmt->fetch()) {
    echo "  {$row['title']} by {$row['author']}\n";
    echo "    閲覧: {$row['views']}, コメント: {$row['comment_count']}\n";
}

echo "\n";

// =====================================
// 6. リレーションシップの管理
// =====================================

echo "--- 6. リレーションシップの管理 ---\n";

/**
 * リレーションシップ管理クラス
 */
class RelationshipManager
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 投稿にカテゴリを追加
     *
     * @param int $postId 投稿ID
     * @param int $categoryId カテゴリID
     * @return bool 成功した場合true
     */
    public function addCategoryToPost(int $postId, int $categoryId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT OR IGNORE INTO post_categories (post_id, category_id)
                VALUES (?, ?)
            ");
            return $stmt->execute([$postId, $categoryId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 投稿からカテゴリを削除
     *
     * @param int $postId 投稿ID
     * @param int $categoryId カテゴリID
     * @return bool 成功した場合true
     */
    public function removeCategoryFromPost(int $postId, int $categoryId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM post_categories
            WHERE post_id = ? AND category_id = ?
        ");
        $stmt->execute([$postId, $categoryId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * 投稿のカテゴリを取得
     *
     * @param int $postId 投稿ID
     * @return array カテゴリ配列
     */
    public function getPostCategories(int $postId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT categories.*
            FROM categories
            INNER JOIN post_categories ON categories.id = post_categories.category_id
            WHERE post_categories.post_id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /**
     * 投稿にタグを追加
     *
     * @param int $postId 投稿ID
     * @param int $tagId タグID
     * @return bool 成功した場合true
     */
    public function addTagToPost(int $postId, int $tagId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT OR IGNORE INTO post_tags (post_id, tag_id)
                VALUES (?, ?)
            ");
            return $stmt->execute([$postId, $tagId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 投稿からタグを削除
     *
     * @param int $postId 投稿ID
     * @param int $tagId タグID
     * @return bool 成功した場合true
     */
    public function removeTagFromPost(int $postId, int $tagId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM post_tags
            WHERE post_id = ? AND tag_id = ?
        ");
        $stmt->execute([$postId, $tagId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * 投稿のタグを取得
     *
     * @param int $postId 投稿ID
     * @return array タグ配列
     */
    public function getPostTags(int $postId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT tags.*
            FROM tags
            INNER JOIN post_tags ON tags.id = post_tags.tag_id
            WHERE post_tags.post_id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /**
     * カテゴリの投稿を取得
     *
     * @param int $categoryId カテゴリID
     * @return array 投稿配列
     */
    public function getCategoryPosts(int $categoryId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT posts.*, users.username
            FROM posts
            INNER JOIN post_categories ON posts.id = post_categories.post_id
            INNER JOIN users ON posts.user_id = users.id
            WHERE post_categories.category_id = ?
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    /**
     * タグの投稿を取得
     *
     * @param int $tagId タグID
     * @return array 投稿配列
     */
    public function getTagPosts(int $tagId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT posts.*, users.username
            FROM posts
            INNER JOIN post_tags ON posts.id = post_tags.post_id
            INNER JOIN users ON posts.user_id = users.id
            WHERE post_tags.tag_id = ?
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute([$tagId]);
        return $stmt->fetchAll();
    }
}

// 使用例
$relationManager = new RelationshipManager($pdo);

// 投稿にカテゴリを追加
$relationManager->addCategoryToPost(2, 3);
echo "投稿ID 2にカテゴリID 3を追加しました\n";

// 投稿のカテゴリを取得
$categories = $relationManager->getPostCategories(2);
echo "\n投稿ID 2のカテゴリ:\n";
foreach ($categories as $category) {
    echo "  - {$category['name']}\n";
}

// カテゴリの投稿を取得
$posts = $relationManager->getCategoryPosts(1);
echo "\nカテゴリID 1（Programming）の投稿:\n";
foreach ($posts as $post) {
    echo "  - {$post['title']} by {$post['username']}\n";
}

echo "\n";

// =====================================
// 7. 複雑なクエリ例
// =====================================

echo "--- 7. 複雑なクエリ例 ---\n";

echo "【投稿の完全な情報】\n";
$stmt = $pdo->prepare("
    SELECT
        posts.id,
        posts.title,
        posts.content,
        posts.views,
        users.username AS author,
        posts.created_at,
        (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count,
        (SELECT GROUP_CONCAT(categories.name, ', ')
         FROM categories
         INNER JOIN post_categories ON categories.id = post_categories.category_id
         WHERE post_categories.post_id = posts.id) AS categories,
        (SELECT GROUP_CONCAT(tags.name, ', ')
         FROM tags
         INNER JOIN post_tags ON tags.id = post_tags.tag_id
         WHERE post_tags.post_id = posts.id) AS tags
    FROM posts
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
");

$stmt->execute([1]);
$post = $stmt->fetch();

if ($post) {
    echo "投稿詳細:\n";
    echo "  タイトル: {$post['title']}\n";
    echo "  著者: {$post['author']}\n";
    echo "  閲覧数: {$post['views']}\n";
    echo "  コメント: {$post['comment_count']}件\n";
    echo "  カテゴリ: {$post['categories']}\n";
    echo "  タグ: {$post['tags']}\n";
}

echo "\n";

// =====================================
// 8. ベストプラクティス
// =====================================

echo "--- 8. JOINとリレーションのベストプラクティス ---\n";

echo "
【JOINの基本原則】

1. 適切なJOINタイプを選択
   - INNER JOIN: 両方に存在するレコードのみ
   - LEFT JOIN: 左側のすべて + 右側の一致するもの
   - RIGHT JOIN: 右側のすべて + 左側の一致するもの（SQLiteは非対応）
   - CROSS JOIN: デカルト積（注意して使用）

2. インデックスを活用
   - JOINのキーカラムにインデックスを設定
   - 外部キーカラムには必ずインデックス
   - WHERE句のカラムにもインデックス

3. SELECT句で必要なカラムのみ指定
   - SELECT * は避ける
   - 必要なカラムを明示的に指定
   - エイリアスで可読性を向上

4. N+1問題を避ける
   - ループ内でクエリを実行しない
   - JOINで一度に取得
   - Eager Loadingを活用

【リレーションシップ設計】

1. 正規化を適切に
   - 第1正規形: 繰り返しグループの排除
   - 第2正規形: 部分関数従属の排除
   - 第3正規形: 推移的関数従属の排除

2. 外部キー制約を設定
   - データの整合性を保証
   - CASCADE、RESTRICT、SET NULLを適切に選択
   - パフォーマンスへの影響も考慮

3. 多対多はテーブルを使用
   - 中間テーブル（ジャンクションテーブル）
   - 複合主キーで重複を防ぐ
   - 追加属性を持たせることも可能

4. 命名規則を統一
   - テーブル名: 複数形（users、posts）
   - 外部キー: テーブル名_id（user_id）
   - 中間テーブル: テーブル1_テーブル2（post_categories）

【パフォーマンス最適化】

1. EXPLAIN を使って実行計画を確認
   - JOINの順序
   - インデックスの使用状況
   - フルテーブルスキャンの有無

2. 適切なインデックス戦略
   - 単一カラムインデックス
   - 複合インデックス
   - カバリングインデックス

3. サブクエリの最適化
   - 相関サブクエリは遅い場合がある
   - JOINに書き換えられないか検討
   - EXISTS/NOT EXISTSの活用

4. GROUP BYとHAVINGの適切な使用
   - WHEREで絞り込んでからGROUP BY
   - HAVINGは集計後の絞り込み
";

echo "=== Phase 3.2: JOIN操作とリレーションシップ - 完了 ===\n";
