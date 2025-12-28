<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Repositories;

use App\Phase4\BlogSystem\Database;
use App\Phase4\BlogSystem\Entities\Post;
use App\Phase4\BlogSystem\Entities\PostStatus;
use DateTimeImmutable;
use PDO;

/**
 * 記事リポジトリ
 *
 * 記事データへのアクセスを提供
 */
class PostRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * 記事を作成
     */
    public function create(
        int $userId,
        string $title,
        string $slug,
        string $content,
        ?string $excerpt = null,
        PostStatus $status = PostStatus::DRAFT,
        ?int $categoryId = null
    ): Post {
        $now = new DateTimeImmutable();
        $publishedAt = $status === PostStatus::PUBLISHED ? $now : null;

        $sql = '
            INSERT INTO posts (user_id, title, slug, content, excerpt, status, category_id, published_at, created_at, updated_at)
            VALUES (:user_id, :title, :slug, :content, :excerpt, :status, :category_id, :published_at, :created_at, :updated_at)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status->value,
            'category_id' => $categoryId,
            'published_at' => $publishedAt?->format('Y-m-d H:i:s'),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return new Post(
            (int) $this->pdo->lastInsertId(),
            $userId,
            $title,
            $slug,
            $content,
            $excerpt,
            $status,
            $categoryId,
            $publishedAt,
            $now,
            $now
        );
    }

    /**
     * IDで記事を検索
     */
    public function findById(int $id): ?Post
    {
        $sql = 'SELECT * FROM posts WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * スラッグで記事を検索
     */
    public function findBySlug(string $slug): ?Post
    {
        $sql = 'SELECT * FROM posts WHERE slug = :slug';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * 公開記事を取得（ページネーション付き）
     */
    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        $sql = 'SELECT * FROM posts WHERE status = :status ORDER BY published_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('status', PostStatus::PUBLISHED->value);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->hydrate($row);
        }
        return $posts;
    }

    /**
     * ユーザーの記事を取得
     */
    public function findByUserId(int $userId): array
    {
        $sql = 'SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->hydrate($row);
        }
        return $posts;
    }

    /**
     * カテゴリーの記事を取得
     */
    public function findByCategoryId(int $categoryId): array
    {
        $sql = 'SELECT * FROM posts WHERE category_id = :category_id AND status = :status ORDER BY published_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'category_id' => $categoryId,
            'status' => PostStatus::PUBLISHED->value,
        ]);

        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->hydrate($row);
        }
        return $posts;
    }

    /**
     * 記事を検索（タイトルと本文）
     */
    public function search(string $keyword): array
    {
        $sql = '
            SELECT * FROM posts
            WHERE (title LIKE :keyword OR content LIKE :keyword)
            AND status = :status
            ORDER BY published_at DESC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'keyword' => "%{$keyword}%",
            'status' => PostStatus::PUBLISHED->value,
        ]);

        $posts = [];
        while ($row = $stmt->fetch()) {
            $posts[] = $this->hydrate($row);
        }
        return $posts;
    }

    /**
     * 記事を更新
     */
    public function update(Post $post): void
    {
        $sql = '
            UPDATE posts
            SET title = :title, slug = :slug, content = :content, excerpt = :excerpt,
                status = :status, category_id = :category_id, published_at = :published_at, updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'excerpt' => $post->getExcerpt(),
            'status' => $post->getStatus()->value,
            'category_id' => $post->getCategoryId(),
            'published_at' => $post->getPublishedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 記事を削除
     */
    public function delete(int $id): void
    {
        $sql = 'DELETE FROM posts WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * 記事にタグを追加
     */
    public function addTag(int $postId, int $tagId): void
    {
        $sql = 'INSERT OR IGNORE INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId, 'tag_id' => $tagId]);
    }

    /**
     * 記事のタグを削除
     */
    public function removeTag(int $postId, int $tagId): void
    {
        $sql = 'DELETE FROM post_tags WHERE post_id = :post_id AND tag_id = :tag_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId, 'tag_id' => $tagId]);
    }

    /**
     * 記事のタグIDを取得
     */
    public function getTagIds(int $postId): array
    {
        $sql = 'SELECT tag_id FROM post_tags WHERE post_id = :post_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function hydrate(array $row): Post
    {
        return new Post(
            (int) $row['id'],
            (int) $row['user_id'],
            $row['title'],
            $row['slug'],
            $row['content'],
            $row['excerpt'],
            PostStatus::from($row['status']),
            $row['category_id'] !== null ? (int) $row['category_id'] : null,
            $row['published_at'] !== null ? new DateTimeImmutable($row['published_at']) : null,
            new DateTimeImmutable($row['created_at']),
            new DateTimeImmutable($row['updated_at'])
        );
    }
}
