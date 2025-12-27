<?php

declare(strict_types=1);

/**
 * Phase 3.1: データベース操作 - 実践演習
 *
 * PDOとCRUD操作を使った実践的なシステムの実装
 */

echo "=== Phase 3.1: データベース操作 - 実践演習 ===\n\n";

// =====================================
// データベース接続クラス
// =====================================

/**
 * データベース接続管理クラス
 */
class DatabaseConnection
{
    private static ?PDO $instance = null;

    /**
     * PDOインスタンスを取得（シングルトン）
     *
     * @return PDO PDOインスタンス
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                // 実際のアプリケーションでは環境変数から取得
                $dsn = 'sqlite::memory:';

                self::$instance = new PDO($dsn);
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
     * 接続をクローズ
     */
    public static function closeConnection(): void
    {
        self::$instance = null;
    }
}

// データベース接続を取得
$pdo = DatabaseConnection::getInstance();

// テーブル作成
$pdo->exec("
    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        age INTEGER,
        status TEXT DEFAULT 'active',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
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
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

echo "データベースとテーブルを準備しました\n\n";

// =====================================
// 演習1: ユーザー管理リポジトリ
// =====================================

echo "--- 演習1: ユーザー管理リポジトリ ---\n";

/**
 * ユーザーエンティティ
 */
class User
{
    public function __construct(
        public readonly ?int $id,
        public string $username,
        public string $email,
        public string $password,
        public ?int $age = null,
        public string $status = 'active',
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    /**
     * パスワードをハッシュ化
     */
    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    /**
     * パスワードを検証
     *
     * @param string $password 検証するパスワード
     * @return bool 一致する場合true
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}

/**
 * ユーザーリポジトリ
 */
class UserRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * ユーザーを作成
     *
     * @param User $user ユーザー
     * @return int 作成されたユーザーID
     */
    public function create(User $user): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, age, status)
            VALUES (:username, :email, :password, :age, :status)
        ");

        $stmt->execute([
            ':username' => $user->username,
            ':email' => $user->email,
            ':password' => $user->password,
            ':age' => $user->age,
            ':status' => $user->status,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * IDでユーザーを取得
     *
     * @param int $id ユーザーID
     * @return User|null ユーザー
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    /**
     * ユーザー名でユーザーを取得
     *
     * @param string $username ユーザー名
     * @return User|null ユーザー
     */
    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    /**
     * メールアドレスでユーザーを取得
     *
     * @param string $email メールアドレス
     * @return User|null ユーザー
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    /**
     * すべてのユーザーを取得
     *
     * @return User[] ユーザー配列
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id ASC");
        $users = [];

        while ($data = $stmt->fetch()) {
            $users[] = $this->hydrate($data);
        }

        return $users;
    }

    /**
     * 条件に一致するユーザーを検索
     *
     * @param array $criteria 検索条件
     * @return User[] ユーザー配列
     */
    public function findBy(array $criteria): array
    {
        $whereClauses = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $whereClauses[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "SELECT * FROM users WHERE " . implode(' AND ', $whereClauses);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $users = [];
        while ($data = $stmt->fetch()) {
            $users[] = $this->hydrate($data);
        }

        return $users;
    }

    /**
     * ユーザーを更新
     *
     * @param User $user ユーザー
     * @return bool 成功した場合true
     */
    public function update(User $user): bool
    {
        if ($user->id === null) {
            throw new InvalidArgumentException('ユーザーIDが必要です');
        }

        $stmt = $this->pdo->prepare("
            UPDATE users
            SET username = :username,
                email = :email,
                password = :password,
                age = :age,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            ':username' => $user->username,
            ':email' => $user->email,
            ':password' => $user->password,
            ':age' => $user->age,
            ':status' => $user->status,
            ':id' => $user->id,
        ]);
    }

    /**
     * ユーザーを削除
     *
     * @param int $id ユーザーID
     * @return bool 成功した場合true
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * ユーザー数を取得
     *
     * @return int ユーザー数
     */
    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        return (int)$stmt->fetchColumn();
    }

    /**
     * データ配列からUserオブジェクトを作成
     *
     * @param array $data データ配列
     * @return User ユーザー
     */
    private function hydrate(array $data): User
    {
        return new User(
            id: (int)$data['id'],
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
            age: $data['age'] !== null ? (int)$data['age'] : null,
            status: $data['status'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
        );
    }
}

// 使用例
$userRepo = new UserRepository($pdo);

// ユーザー作成
$user1 = new User(null, 'alice', 'alice@example.com', 'password123', 25);
$user1->hashPassword();
$userId1 = $userRepo->create($user1);
echo "ユーザーを作成しました（ID: {$userId1}）\n";

$user2 = new User(null, 'bob', 'bob@example.com', 'password456', 30);
$user2->hashPassword();
$userId2 = $userRepo->create($user2);
echo "ユーザーを作成しました（ID: {$userId2}）\n";

$user3 = new User(null, 'charlie', 'charlie@example.com', 'password789', 35);
$user3->hashPassword();
$userId3 = $userRepo->create($user3);
echo "ユーザーを作成しました（ID: {$userId3}）\n";

// ユーザー検索
$foundUser = $userRepo->findByUsername('alice');
if ($foundUser) {
    echo "\nユーザー名 'alice' を検索: {$foundUser->email}\n";
}

// 全ユーザー取得
$allUsers = $userRepo->findAll();
echo "\n全ユーザー（{$userRepo->count()}人）:\n";
foreach ($allUsers as $user) {
    echo "  - {$user->username} ({$user->email})\n";
}

// ユーザー更新
$foundUser->age = 26;
$userRepo->update($foundUser);
echo "\nユーザー '{$foundUser->username}' の年齢を更新しました\n";

echo "\n";

// =====================================
// 演習2: ブログ投稿管理システム
// =====================================

echo "--- 演習2: ブログ投稿管理システム ---\n";

/**
 * 投稿エンティティ
 */
class Post
{
    public function __construct(
        public readonly ?int $id,
        public int $user_id,
        public string $title,
        public string $content,
        public string $status = 'draft',
        public int $views = 0,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }
}

/**
 * 投稿リポジトリ
 */
class PostRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * 投稿を作成
     *
     * @param Post $post 投稿
     * @return int 作成された投稿ID
     */
    public function create(Post $post): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (user_id, title, content, status, views)
            VALUES (:user_id, :title, :content, :status, :views)
        ");

        $stmt->execute([
            ':user_id' => $post->user_id,
            ':title' => $post->title,
            ':content' => $post->content,
            ':status' => $post->status,
            ':views' => $post->views,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * IDで投稿を取得
     *
     * @param int $id 投稿ID
     * @return Post|null 投稿
     */
    public function findById(int $id): ?Post
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    /**
     * 公開済みの投稿を取得
     *
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return Post[] 投稿配列
     */
    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts
            WHERE status = 'published'
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$limit, $offset]);

        $posts = [];
        while ($data = $stmt->fetch()) {
            $posts[] = $this->hydrate($data);
        }

        return $posts;
    }

    /**
     * ユーザーの投稿を取得
     *
     * @param int $userId ユーザーID
     * @return Post[] 投稿配列
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([$userId]);

        $posts = [];
        while ($data = $stmt->fetch()) {
            $posts[] = $this->hydrate($data);
        }

        return $posts;
    }

    /**
     * 投稿を更新
     *
     * @param Post $post 投稿
     * @return bool 成功した場合true
     */
    public function update(Post $post): bool
    {
        if ($post->id === null) {
            throw new InvalidArgumentException('投稿IDが必要です');
        }

        $stmt = $this->pdo->prepare("
            UPDATE posts
            SET title = :title,
                content = :content,
                status = :status,
                views = :views,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            ':title' => $post->title,
            ':content' => $post->content,
            ':status' => $post->status,
            ':views' => $post->views,
            ':id' => $post->id,
        ]);
    }

    /**
     * 閲覧数を増やす
     *
     * @param int $id 投稿ID
     * @return bool 成功した場合true
     */
    public function incrementViews(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE posts
            SET views = views + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    /**
     * 投稿を削除
     *
     * @param int $id 投稿ID
     * @return bool 成功した場合true
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * ユーザーと投稿を結合して取得
     *
     * @param int $limit 取得件数
     * @return array 投稿とユーザー情報の配列
     */
    public function findWithUser(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                posts.*,
                users.username,
                users.email
            FROM posts
            INNER JOIN users ON posts.user_id = users.id
            WHERE posts.status = 'published'
            ORDER BY posts.created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * データ配列からPostオブジェクトを作成
     *
     * @param array $data データ配列
     * @return Post 投稿
     */
    private function hydrate(array $data): Post
    {
        return new Post(
            id: (int)$data['id'],
            user_id: (int)$data['user_id'],
            title: $data['title'],
            content: $data['content'],
            status: $data['status'],
            views: (int)$data['views'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
        );
    }
}

// 使用例
$postRepo = new PostRepository($pdo);

// 投稿作成
$post1 = new Post(null, $userId1, 'Hello World', 'This is my first post!', 'published');
$postId1 = $postRepo->create($post1);
echo "投稿を作成しました（ID: {$postId1}）\n";

$post2 = new Post(null, $userId1, 'PHP Learning', 'Learning PHP is fun!', 'published');
$postId2 = $postRepo->create($post2);
echo "投稿を作成しました（ID: {$postId2}）\n";

$post3 = new Post(null, $userId2, 'Draft Post', 'This is a draft', 'draft');
$postId3 = $postRepo->create($post3);
echo "投稿を作成しました（ID: {$postId3}）\n";

// 閲覧数を増やす
$postRepo->incrementViews($postId1);
$postRepo->incrementViews($postId1);
$postRepo->incrementViews($postId2);
echo "\n閲覧数を更新しました\n";

// 公開済み投稿を取得
$publishedPosts = $postRepo->findPublished();
echo "\n公開済み投稿（" . count($publishedPosts) . "件）:\n";
foreach ($publishedPosts as $post) {
    echo "  - {$post->title} ({$post->views} views)\n";
}

// ユーザーと結合して取得
$postsWithUser = $postRepo->findWithUser(5);
echo "\n投稿とユーザー情報:\n";
foreach ($postsWithUser as $data) {
    echo "  - '{$data['title']}' by {$data['username']}\n";
}

echo "\n";

// =====================================
// 演習3: コメントシステム
// =====================================

echo "--- 演習3: コメントシステム ---\n";

/**
 * コメントエンティティ
 */
class Comment
{
    public function __construct(
        public readonly ?int $id,
        public int $post_id,
        public int $user_id,
        public string $content,
        public ?string $created_at = null,
    ) {
    }
}

/**
 * コメントリポジトリ
 */
class CommentRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * コメントを作成
     *
     * @param Comment $comment コメント
     * @return int 作成されたコメントID
     */
    public function create(Comment $comment): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (post_id, user_id, content)
            VALUES (:post_id, :user_id, :content)
        ");

        $stmt->execute([
            ':post_id' => $comment->post_id,
            ':user_id' => $comment->user_id,
            ':content' => $comment->content,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * 投稿のコメントを取得
     *
     * @param int $postId 投稿ID
     * @return array コメントとユーザー情報の配列
     */
    public function findByPost(int $postId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                comments.*,
                users.username,
                users.email
            FROM comments
            INNER JOIN users ON comments.user_id = users.id
            WHERE comments.post_id = ?
            ORDER BY comments.created_at ASC
        ");

        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /**
     * コメント数を取得
     *
     * @param int $postId 投稿ID
     * @return int コメント数
     */
    public function countByPost(int $postId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * コメントを削除
     *
     * @param int $id コメントID
     * @return bool 成功した場合true
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}

// 使用例
$commentRepo = new CommentRepository($pdo);

// コメント作成
$comment1 = new Comment(null, $postId1, $userId2, 'Great post!');
$commentId1 = $commentRepo->create($comment1);
echo "コメントを作成しました（ID: {$commentId1}）\n";

$comment2 = new Comment(null, $postId1, $userId3, 'Thanks for sharing!');
$commentId2 = $commentRepo->create($comment2);
echo "コメントを作成しました（ID: {$commentId2}）\n";

// 投稿のコメントを取得
$comments = $commentRepo->findByPost($postId1);
$commentCount = $commentRepo->countByPost($postId1);

echo "\n投稿ID {$postId1} のコメント（{$commentCount}件）:\n";
foreach ($comments as $comment) {
    echo "  - {$comment['username']}: {$comment['content']}\n";
}

echo "\n";

// =====================================
// 演習4: トランザクションを使った複雑な操作
// =====================================

echo "--- 演習4: トランザクションを使った複雑な操作 ---\n";

/**
 * ブログサービス
 */
class BlogService
{
    public function __construct(
        private PDO $pdo,
        private UserRepository $userRepo,
        private PostRepository $postRepo,
        private CommentRepository $commentRepo,
    ) {
    }

    /**
     * 新しいユーザーを登録して初回投稿を作成
     *
     * @param User $user ユーザー
     * @param Post $post 投稿
     * @return array ユーザーIDと投稿ID
     */
    public function registerUserWithPost(User $user, Post $post): array
    {
        try {
            $this->pdo->beginTransaction();

            // ユーザー作成
            $userId = $this->userRepo->create($user);
            $post->user_id = $userId;

            // 投稿作成
            $postId = $this->postRepo->create($post);

            $this->pdo->commit();

            return ['user_id' => $userId, 'post_id' => $postId];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("ユーザー登録エラー: {$e->getMessage()}");
        }
    }

    /**
     * ユーザーを削除（関連する投稿とコメントも削除）
     *
     * @param int $userId ユーザーID
     * @return bool 成功した場合true
     */
    public function deleteUserWithRelations(int $userId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 外部キー制約でCASCADE設定されているため、
            // ユーザーを削除すれば投稿とコメントも自動削除される
            $result = $this->userRepo->delete($userId);

            $this->pdo->commit();

            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("ユーザー削除エラー: {$e->getMessage()}");
        }
    }

    /**
     * 投稿の統計情報を取得
     *
     * @param int $postId 投稿ID
     * @return array 統計情報
     */
    public function getPostStatistics(int $postId): array
    {
        $post = $this->postRepo->findById($postId);
        if (!$post) {
            throw new RuntimeException("投稿が見つかりません");
        }

        $commentCount = $this->commentRepo->countByPost($postId);

        return [
            'post' => $post,
            'comment_count' => $commentCount,
            'views' => $post->views,
        ];
    }
}

// 使用例
$blogService = new BlogService($pdo, $userRepo, $postRepo, $commentRepo);

// 新規ユーザー登録と初回投稿
$newUser = new User(null, 'dave', 'dave@example.com', 'password000', 28);
$newUser->hashPassword();
$welcomePost = new Post(null, 0, 'Welcome!', 'Nice to meet you all!', 'published');

$result = $blogService->registerUserWithPost($newUser, $welcomePost);
echo "新規ユーザーと投稿を作成しました\n";
echo "  ユーザーID: {$result['user_id']}\n";
echo "  投稿ID: {$result['post_id']}\n";

// 投稿の統計情報を取得
$stats = $blogService->getPostStatistics($postId1);
echo "\n投稿 '{$stats['post']->title}' の統計:\n";
echo "  閲覧数: {$stats['views']}\n";
echo "  コメント数: {$stats['comment_count']}\n";

echo "\n";

// =====================================
// まとめ
// =====================================

echo "--- まとめ ---\n";

echo "
【実装したシステム】

1. ユーザー管理リポジトリ
   - CRUD操作の完全な実装
   - パスワードのハッシュ化
   - 柔軟な検索機能
   - エンティティとリポジトリの分離

2. ブログ投稿管理システム
   - 投稿のCRUD操作
   - ステータス管理（draft/published）
   - 閲覧数のカウント
   - ユーザーとの結合クエリ

3. コメントシステム
   - コメントの作成・削除
   - 投稿との関連付け
   - コメント数の集計

4. トランザクション管理
   - 複数テーブルの同時操作
   - エラー時のロールバック
   - データ整合性の保証

【学んだこと】

- リポジトリパターンの実装
- エンティティクラスの設計
- プリペアドステートメントの活用
- JOIN を使ったテーブル結合
- トランザクションによるデータ整合性
- 外部キー制約とCASCADE
- シングルトンパターンでの接続管理
- セキュアなパスワード管理

【設計パターン】

- Repository Pattern: データアクセスの抽象化
- Singleton Pattern: データベース接続の管理
- Entity Pattern: データとビジネスロジックの分離
- Service Layer: 複雑なビジネスロジックの集約
";

echo "\n=== Phase 3.1: データベース操作 - 実践演習完了 ===\n";
