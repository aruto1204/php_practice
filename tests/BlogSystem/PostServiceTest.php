<?php

declare(strict_types=1);

namespace Tests\BlogSystem;

use App\Phase4\BlogSystem\Database;
use App\Phase4\BlogSystem\Entities\PostStatus;
use App\Phase4\BlogSystem\Repositories\PostRepository;
use App\Phase4\BlogSystem\Repositories\UserRepository;
use App\Phase4\BlogSystem\Services\PostService;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * PostServiceのテスト
 */
class PostServiceTest extends TestCase
{
    private PostService $postService;
    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private int $userId;

    protected function setUp(): void
    {
        // データベースを初期化
        Database::initializeTables();
        Database::clearTables();

        // リポジトリとサービスを作成
        $this->userRepository = new UserRepository();
        $this->postRepository = new PostRepository();
        $this->postService = new PostService($this->postRepository);

        // テスト用ユーザーを作成
        $user = $this->userRepository->create(
            'testuser',
            'test@example.com',
            'password123',
            'Test User'
        );
        $this->userId = $user->getId();
    }

    /**
     * 記事作成のテスト
     */
    public function testCreatePost(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'Test Post',
            'This is a test post content.',
            null,
            true
        );

        $this->assertEquals('Test Post', $post->getTitle());
        $this->assertEquals('test-post', $post->getSlug());
        $this->assertTrue($post->isPublished());
        $this->assertNotNull($post->getExcerpt());
    }

    /**
     * 下書き記事作成のテスト
     */
    public function testCreateDraftPost(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'Draft Post',
            'This is a draft.',
            null,
            false
        );

        $this->assertTrue($post->isDraft());
        $this->assertNull($post->getPublishedAt());
    }

    /**
     * スラッグの自動生成テスト
     */
    public function testSlugGeneration(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'Hello World!',
            'Content',
            null,
            true
        );

        $this->assertEquals('hello-world', $post->getSlug());
    }

    /**
     * スラッグの重複処理テスト
     */
    public function testDuplicateSlugHandling(): void
    {
        $post1 = $this->postService->createPost(
            $this->userId,
            'Same Title',
            'Content 1',
            null,
            true
        );

        $post2 = $this->postService->createPost(
            $this->userId,
            'Same Title',
            'Content 2',
            null,
            true
        );

        $this->assertEquals('same-title', $post1->getSlug());
        $this->assertStringStartsWith('same-title-', $post2->getSlug());
        $this->assertNotEquals($post1->getSlug(), $post2->getSlug());
    }

    /**
     * 記事更新のテスト
     */
    public function testUpdatePost(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'Original Title',
            'Original content',
            null,
            false
        );

        $updatedPost = $this->postService->updatePost(
            $post->getId(),
            $this->userId,
            'Updated Title',
            'Updated content'
        );

        $this->assertEquals('Updated Title', $updatedPost->getTitle());
        $this->assertEquals('Updated content', $updatedPost->getContent());
    }

    /**
     * 権限のない記事更新でエラーが発生することをテスト
     */
    public function testUpdatePostWithoutPermission(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'Test Post',
            'Content',
            null,
            true
        );

        // 別のユーザーを作成
        $otherUser = $this->userRepository->create(
            'otheruser',
            'other@example.com',
            'password',
            'Other User'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('この記事を編集する権限がありません');

        $this->postService->updatePost(
            $post->getId(),
            $otherUser->getId(),
            'Hacked',
            'Should not work'
        );
    }

    /**
     * 記事公開のテスト
     */
    public function testPublishPost(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'Draft Post',
            'Content',
            null,
            false
        );

        $this->assertTrue($post->isDraft());

        $this->postService->publishPost($post->getId(), $this->userId);

        $publishedPost = $this->postRepository->findById($post->getId());
        $this->assertTrue($publishedPost->isPublished());
        $this->assertNotNull($publishedPost->getPublishedAt());
    }

    /**
     * 記事削除のテスト
     */
    public function testDeletePost(): void
    {
        $post = $this->postService->createPost(
            $this->userId,
            'To Be Deleted',
            'Content',
            null,
            true
        );

        $this->postService->deletePost($post->getId(), $this->userId);

        $deletedPost = $this->postRepository->findById($post->getId());
        $this->assertNull($deletedPost);
    }

    /**
     * 公開記事一覧取得のテスト
     */
    public function testGetPublishedPosts(): void
    {
        // 3件の記事を作成（2件公開、1件下書き）
        $this->postService->createPost($this->userId, 'Post 1', 'Content 1', null, true);
        $this->postService->createPost($this->userId, 'Post 2', 'Content 2', null, true);
        $this->postService->createPost($this->userId, 'Post 3', 'Content 3', null, false);

        $publishedPosts = $this->postService->getPublishedPosts();

        $this->assertCount(2, $publishedPosts);
        foreach ($publishedPosts as $post) {
            $this->assertTrue($post->isPublished());
        }
    }

    /**
     * 記事検索のテスト
     */
    public function testSearchPosts(): void
    {
        $this->postService->createPost($this->userId, 'PHP Tutorial', 'Learn PHP programming', null, true);
        $this->postService->createPost($this->userId, 'JavaScript Guide', 'Learn JavaScript', null, true);
        $this->postService->createPost($this->userId, 'PHP Advanced', 'Advanced PHP topics', null, true);

        $results = $this->postService->searchPosts('PHP');

        $this->assertCount(2, $results);
        foreach ($results as $post) {
            $this->assertStringContainsStringIgnoringCase('PHP', $post->getTitle() . $post->getContent());
        }
    }

    /**
     * 空のキーワードで検索した場合のテスト
     */
    public function testSearchWithEmptyKeyword(): void
    {
        $results = $this->postService->searchPosts('');
        $this->assertCount(0, $results);
    }
}
