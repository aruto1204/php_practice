<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem;

use App\Phase4\BlogSystem\Repositories\PostRepository;
use App\Phase4\BlogSystem\Repositories\UserRepository;
use App\Phase4\BlogSystem\Services\AuthService;
use App\Phase4\BlogSystem\Services\PostService;

require_once __DIR__ . '/helpers.php';

/**
 * ブログアプリケーション
 *
 * ブログシステムのメインアプリケーション
 * MVCパターンで構築された実践的なWebアプリケーション
 */
class BlogApp
{
    private AuthService $authService;
    private PostService $postService;
    private UserRepository $userRepository;
    private PostRepository $postRepository;

    public function __construct()
    {
        // データベースを初期化
        Database::initializeTables();

        // リポジトリを初期化
        $this->userRepository = new UserRepository();
        $this->postRepository = new PostRepository();

        // サービスを初期化
        $this->authService = new AuthService($this->userRepository);
        $this->postService = new PostService($this->postRepository);

        // セッションを開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => false, // HTTPS環境ではtrueに設定
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    /**
     * アプリケーションを実行
     */
    public function run(): void
    {
        echo "=== ブログシステムデモ ===\n\n";

        // デモデータをクリア
        Database::clearTables();

        // 1. ユーザー登録
        echo "【1. ユーザー登録】\n";
        try {
            $user1 = $this->authService->register(
                'alice',
                'alice@example.com',
                'password123',
                'Alice Smith'
            );
            echo "✓ ユーザー登録成功: {$user1->getDisplayName()} (@{$user1->getUsername()})\n";

            $user2 = $this->authService->register(
                'bob',
                'bob@example.com',
                'password456',
                'Bob Johnson'
            );
            echo "✓ ユーザー登録成功: {$user2->getDisplayName()} (@{$user2->getUsername()})\n";
        } catch (\InvalidArgumentException $e) {
            echo "✗ エラー: {$e->getMessage()}\n";
        }

        echo "\n";

        // 2. ログイン
        echo "【2. ログイン】\n";
        try {
            $loggedInUser = $this->authService->login('alice', 'password123');
            echo "✓ ログイン成功: {$loggedInUser->getDisplayName()}\n";
            echo "  セッションID: " . current_user_id() . "\n";
        } catch (\InvalidArgumentException $e) {
            echo "✗ ログイン失敗: {$e->getMessage()}\n";
        }

        echo "\n";

        // 3. 記事作成
        echo "【3. 記事作成】\n";
        try {
            $post1 = $this->postService->createPost(
                $user1->getId(),
                'PHPの基礎を学ぼう',
                "PHPは Web開発で広く使用されているサーバーサイドのスクリプト言語です。\n\n" .
                "変数、制御構造、関数など基本的な構文から、オブジェクト指向プログラミング、" .
                "データベース操作まで、幅広い機能を提供しています。",
                null,
                true // 公開
            );
            echo "✓ 記事作成成功: {$post1->getTitle()}\n";
            echo "  スラッグ: {$post1->getSlug()}\n";
            echo "  ステータス: " . ($post1->isPublished() ? '公開' : '下書き') . "\n";

            $post2 = $this->postService->createPost(
                $user1->getId(),
                'MVCパターンとは',
                "Model-View-Controller (MVC) は、アプリケーションを3つの主要コンポーネントに分離する設計パターンです。\n\n" .
                "- Model: データとビジネスロジック\n" .
                "- View: ユーザーインターフェース\n" .
                "- Controller: ユーザー入力の処理",
                null,
                false // 下書き
            );
            echo "✓ 記事作成成功: {$post2->getTitle()}\n";
            echo "  スラッグ: {$post2->getSlug()}\n";
            echo "  ステータス: " . ($post2->isPublished() ? '公開' : '下書き') . "\n";

            $post3 = $this->postService->createPost(
                $user2->getId(),
                'データベース設計のベストプラクティス',
                "良いデータベース設計は、パフォーマンス、保守性、スケーラビリティに大きく影響します。\n\n" .
                "正規化、インデックス、外部キー制約などの概念を理解することが重要です。",
                null,
                true
            );
            echo "✓ 記事作成成功: {$post3->getTitle()}\n";
            echo "  スラッグ: {$post3->getSlug()}\n";
        } catch (\Exception $e) {
            echo "✗ エラー: {$e->getMessage()}\n";
        }

        echo "\n";

        // 4. 公開記事一覧
        echo "【4. 公開記事一覧】\n";
        $publishedPosts = $this->postService->getPublishedPosts();
        echo "公開記事数: " . count($publishedPosts) . "件\n\n";

        foreach ($publishedPosts as $post) {
            echo "- {$post->getTitle()}\n";
            echo "  投稿者ID: {$post->getUserId()}\n";
            echo "  抜粋: {$post->getExcerpt()}\n";
            echo "  公開日: " . format_date($post->getPublishedAt()) . "\n\n";
        }

        // 5. 記事検索
        echo "【5. 記事検索】\n";
        $searchResults = $this->postService->searchPosts('PHP');
        echo "検索結果: " . count($searchResults) . "件\n";
        foreach ($searchResults as $post) {
            echo "- {$post->getTitle()}\n";
        }

        echo "\n";

        // 6. 記事更新
        echo "【6. 記事更新】\n";
        try {
            $updatedPost = $this->postService->updatePost(
                $post2->getId(),
                $user1->getId(),
                'MVCパターン完全ガイド',
                $post2->getContent() . "\n\n【追記】この記事は更新されました。"
            );
            echo "✓ 記事更新成功: {$updatedPost->getTitle()}\n";
        } catch (\Exception $e) {
            echo "✗ エラー: {$e->getMessage()}\n";
        }

        echo "\n";

        // 7. 記事公開
        echo "【7. 記事公開】\n";
        try {
            $this->postService->publishPost($post2->getId(), $user1->getId());
            echo "✓ 記事「{$post2->getTitle()}」を公開しました\n";
        } catch (\Exception $e) {
            echo "✗ エラー: {$e->getMessage()}\n";
        }

        echo "\n";

        // 8. ユーザーの記事一覧
        echo "【8. ユーザーの記事一覧】\n";
        $userPosts = $this->postService->getUserPosts($user1->getId());
        echo "{$user1->getDisplayName()}の記事: " . count($userPosts) . "件\n";
        foreach ($userPosts as $post) {
            $status = $post->isPublished() ? '公開' : '下書き';
            echo "- {$post->getTitle()} [{$status}]\n";
        }

        echo "\n";

        // 9. 権限チェック（他のユーザーの記事を編集しようとする）
        echo "【9. 権限チェック】\n";
        try {
            $this->postService->updatePost(
                $post3->getId(), // Bob's post
                $user1->getId(), // Aliceのユーザー ID
                'Hacked!',
                'This should not work'
            );
            echo "✗ 権限チェックが機能していません！\n";
        } catch (\InvalidArgumentException $e) {
            echo "✓ 権限チェック成功: {$e->getMessage()}\n";
        }

        echo "\n";

        // 10. ログアウト
        echo "【10. ログアウト】\n";
        $this->authService->logout();
        echo "✓ ログアウト成功\n";
        echo "  ログイン状態: " . (is_logged_in() ? 'ログイン中' : 'ログアウト') . "\n";

        echo "\n=== デモ終了 ===\n";
    }

    /**
     * 統計情報を表示
     */
    public function showStatistics(): void
    {
        echo "\n=== 統計情報 ===\n";

        $users = $this->userRepository->findAll();
        echo "総ユーザー数: " . count($users) . "\n";

        $allPosts = $this->postRepository->findPublished(1000, 0);
        echo "公開記事数: " . count($allPosts) . "\n";

        echo "\nユーザー別記事数:\n";
        foreach ($users as $user) {
            $posts = $this->postService->getUserPosts($user->getId());
            $published = array_filter($posts, fn($p) => $p->isPublished());
            echo "- {$user->getDisplayName()}: " . count($posts) . "件 (公開: " . count($published) . "件)\n";
        }
    }
}
