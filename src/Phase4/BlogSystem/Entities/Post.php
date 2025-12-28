<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * 記事ステータス
 */
enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}

/**
 * 記事エンティティ
 *
 * ブログ記事を表現するドメインモデル
 */
class Post
{
    /**
     * コンストラクタ
     *
     * @param int $id 記事ID
     * @param int $userId 投稿者ID
     * @param string $title タイトル
     * @param string $slug URL用スラッグ
     * @param string $content 本文
     * @param string|null $excerpt 抜粋
     * @param PostStatus $status ステータス
     * @param int|null $categoryId カテゴリーID
     * @param DateTimeImmutable|null $publishedAt 公開日時
     * @param DateTimeImmutable $createdAt 作成日時
     * @param DateTimeImmutable $updatedAt 更新日時
     */
    public function __construct(
        private readonly int $id,
        private readonly int $userId,
        private string $title,
        private string $slug,
        private string $content,
        private ?string $excerpt = null,
        private PostStatus $status = PostStatus::DRAFT,
        private ?int $categoryId = null,
        private ?DateTimeImmutable $publishedAt = null,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private DateTimeImmutable $updatedAt = new DateTimeImmutable(),
    ) {
        $this->validateTitle($title);
        $this->validateSlug($slug);
        $this->validateContent($content);
    }

    /**
     * タイトルのバリデーション
     */
    private function validateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('タイトルは空にできません');
        }

        if (mb_strlen($title) > 200) {
            throw new InvalidArgumentException('タイトルは200文字以下で指定してください');
        }
    }

    /**
     * スラッグのバリデーション
     */
    private function validateSlug(string $slug): void
    {
        if (empty(trim($slug))) {
            throw new InvalidArgumentException('スラッグは空にできません');
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new InvalidArgumentException('スラッグは小文字の英数字とハイフンのみ使用できます');
        }

        if (strlen($slug) > 200) {
            throw new InvalidArgumentException('スラッグは200文字以下で指定してください');
        }
    }

    /**
     * 本文のバリデーション
     */
    private function validateContent(string $content): void
    {
        if (empty(trim($content))) {
            throw new InvalidArgumentException('本文は空にできません');
        }
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function getStatus(): PostStatus
    {
        return $this->status;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Setters
    public function setTitle(string $title): void
    {
        $this->validateTitle($title);
        $this->title = $title;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setSlug(string $slug): void
    {
        $this->validateSlug($slug);
        $this->slug = $slug;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setContent(string $content): void
    {
        $this->validateContent($content);
        $this->content = $content;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setExcerpt(?string $excerpt): void
    {
        if ($excerpt !== null && mb_strlen($excerpt) > 500) {
            throw new InvalidArgumentException('抜粋は500文字以下で指定してください');
        }
        $this->excerpt = $excerpt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setCategoryId(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * 記事を公開する
     */
    public function publish(): void
    {
        $this->status = PostStatus::PUBLISHED;
        if ($this->publishedAt === null) {
            $this->publishedAt = new DateTimeImmutable();
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * 記事を下書きに戻す
     */
    public function unpublish(): void
    {
        $this->status = PostStatus::DRAFT;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * 記事が公開されているか確認
     *
     * @return bool 公開されている場合true
     */
    public function isPublished(): bool
    {
        return $this->status === PostStatus::PUBLISHED;
    }

    /**
     * 記事が下書きか確認
     *
     * @return bool 下書きの場合true
     */
    public function isDraft(): bool
    {
        return $this->status === PostStatus::DRAFT;
    }

    /**
     * 抜粋を自動生成
     *
     * @param int $length 抜粋の長さ（文字数）
     */
    public function generateExcerpt(int $length = 150): void
    {
        $text = strip_tags($this->content);
        $text = mb_substr($text, 0, $length);
        $this->excerpt = mb_strlen($this->content) > $length ? $text . '...' : $text;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * タイトルからスラッグを生成
     *
     * @param string $title タイトル
     * @return string スラッグ
     */
    public static function generateSlug(string $title): string
    {
        // タイトルを小文字に変換
        $slug = strtolower($title);

        // 英数字とハイフン以外を削除
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

        // スペースをハイフンに変換
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        // 前後のハイフンを削除
        $slug = trim($slug, '-');

        // 空の場合はタイムスタンプを使用
        if (empty($slug)) {
            $slug = 'post-' . time();
        }

        return $slug;
    }
}
