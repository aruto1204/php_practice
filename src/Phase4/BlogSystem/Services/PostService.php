<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Services;

use App\Phase4\BlogSystem\Entities\Post;
use App\Phase4\BlogSystem\Entities\PostStatus;
use App\Phase4\BlogSystem\Repositories\PostRepository;
use InvalidArgumentException;

/**
 * 記事サービス
 *
 * 記事管理のビジネスロジックを担当
 */
class PostService
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * 記事を作成
     *
     * @param int $userId ユーザーID
     * @param string $title タイトル
     * @param string $content 本文
     * @param int|null $categoryId カテゴリーID
     * @param bool $publish 公開するか
     * @return Post 作成された記事
     */
    public function createPost(
        int $userId,
        string $title,
        string $content,
        ?int $categoryId = null,
        bool $publish = false
    ): Post {
        // スラッグを自動生成
        $slug = Post::generateSlug($title);

        // スラッグの重複チェック
        $existingPost = $this->postRepository->findBySlug($slug);
        if ($existingPost !== null) {
            // 重複する場合はタイムスタンプを追加
            $slug .= '-' . time();
        }

        // ステータスを決定
        $status = $publish ? PostStatus::PUBLISHED : PostStatus::DRAFT;

        // 記事を作成
        $post = $this->postRepository->create(
            $userId,
            $title,
            $slug,
            $content,
            null,
            $status,
            $categoryId
        );

        // 抜粋を自動生成
        $post->generateExcerpt();
        $this->postRepository->update($post);

        return $post;
    }

    /**
     * 記事を更新
     *
     * @param int $postId 記事ID
     * @param int $userId ユーザーID（権限チェック用）
     * @param string $title タイトル
     * @param string $content 本文
     * @param int|null $categoryId カテゴリーID
     * @return Post 更新された記事
     * @throws InvalidArgumentException 権限エラー
     */
    public function updatePost(
        int $postId,
        int $userId,
        string $title,
        string $content,
        ?int $categoryId = null
    ): Post {
        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            throw new InvalidArgumentException('記事が見つかりません');
        }

        // 記事の作成者かチェック
        if ($post->getUserId() !== $userId) {
            throw new InvalidArgumentException('この記事を編集する権限がありません');
        }

        // 記事を更新
        $post->setTitle($title);
        $post->setContent($content);
        $post->setCategoryId($categoryId);
        $post->generateExcerpt();

        $this->postRepository->update($post);

        return $post;
    }

    /**
     * 記事を公開
     *
     * @param int $postId 記事ID
     * @param int $userId ユーザーID（権限チェック用）
     * @throws InvalidArgumentException 権限エラー
     */
    public function publishPost(int $postId, int $userId): void
    {
        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            throw new InvalidArgumentException('記事が見つかりません');
        }

        if ($post->getUserId() !== $userId) {
            throw new InvalidArgumentException('この記事を公開する権限がありません');
        }

        $post->publish();
        $this->postRepository->update($post);
    }

    /**
     * 記事を下書きに戻す
     *
     * @param int $postId 記事ID
     * @param int $userId ユーザーID（権限チェック用）
     * @throws InvalidArgumentException 権限エラー
     */
    public function unpublishPost(int $postId, int $userId): void
    {
        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            throw new InvalidArgumentException('記事が見つかりません');
        }

        if ($post->getUserId() !== $userId) {
            throw new InvalidArgumentException('この記事を非公開にする権限がありません');
        }

        $post->unpublish();
        $this->postRepository->update($post);
    }

    /**
     * 記事を削除
     *
     * @param int $postId 記事ID
     * @param int $userId ユーザーID（権限チェック用）
     * @throws InvalidArgumentException 権限エラー
     */
    public function deletePost(int $postId, int $userId): void
    {
        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            throw new InvalidArgumentException('記事が見つかりません');
        }

        if ($post->getUserId() !== $userId) {
            throw new InvalidArgumentException('この記事を削除する権限がありません');
        }

        $this->postRepository->delete($postId);
    }

    /**
     * 公開記事一覧を取得
     *
     * @param int $page ページ番号（1始まり）
     * @param int $perPage 1ページあたりの件数
     * @return Post[] 記事配列
     */
    public function getPublishedPosts(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->postRepository->findPublished($perPage, $offset);
    }

    /**
     * ユーザーの記事を取得
     *
     * @param int $userId ユーザーID
     * @return Post[] 記事配列
     */
    public function getUserPosts(int $userId): array
    {
        return $this->postRepository->findByUserId($userId);
    }

    /**
     * 記事を検索
     *
     * @param string $keyword キーワード
     * @return Post[] 検索結果
     */
    public function searchPosts(string $keyword): array
    {
        if (empty(trim($keyword))) {
            return [];
        }

        return $this->postRepository->search($keyword);
    }

    /**
     * タグを記事に追加
     *
     * @param int $postId 記事ID
     * @param int $tagId タグID
     * @param int $userId ユーザーID（権限チェック用）
     * @throws InvalidArgumentException 権限エラー
     */
    public function addTagToPost(int $postId, int $tagId, int $userId): void
    {
        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            throw new InvalidArgumentException('記事が見つかりません');
        }

        if ($post->getUserId() !== $userId) {
            throw new InvalidArgumentException('この記事を編集する権限がありません');
        }

        $this->postRepository->addTag($postId, $tagId);
    }

    /**
     * 記事からタグを削除
     *
     * @param int $postId 記事ID
     * @param int $tagId タグID
     * @param int $userId ユーザーID（権限チェック用）
     * @throws InvalidArgumentException 権限エラー
     */
    public function removeTagFromPost(int $postId, int $tagId, int $userId): void
    {
        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            throw new InvalidArgumentException('記事が見つかりません');
        }

        if ($post->getUserId() !== $userId) {
            throw new InvalidArgumentException('この記事を編集する権限がありません');
        }

        $this->postRepository->removeTag($postId, $tagId);
    }
}
