<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * コメントステータス
 */
enum CommentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}

/**
 * コメントエンティティ
 *
 * 記事へのコメントを表現するドメインモデル
 */
class Comment
{
    /**
     * コンストラクタ
     *
     * @param int $id コメントID
     * @param int $postId 記事ID
     * @param int|null $userId ユーザーID（ログインユーザーの場合）
     * @param string $authorName 投稿者名
     * @param string $authorEmail 投稿者メールアドレス
     * @param string $content コメント本文
     * @param CommentStatus $status ステータス
     * @param DateTimeImmutable $createdAt 作成日時
     */
    public function __construct(
        private readonly int $id,
        private readonly int $postId,
        private readonly ?int $userId,
        private string $authorName,
        private string $authorEmail,
        private string $content,
        private CommentStatus $status = CommentStatus::PENDING,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {
        $this->validateAuthorName($authorName);
        $this->validateAuthorEmail($authorEmail);
        $this->validateContent($content);
    }

    /**
     * 投稿者名のバリデーション
     */
    private function validateAuthorName(string $authorName): void
    {
        if (empty(trim($authorName))) {
            throw new InvalidArgumentException('投稿者名は空にできません');
        }

        if (mb_strlen($authorName) > 100) {
            throw new InvalidArgumentException('投稿者名は100文字以下で指定してください');
        }
    }

    /**
     * 投稿者メールアドレスのバリデーション
     */
    private function validateAuthorEmail(string $authorEmail): void
    {
        if (!filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('有効なメールアドレスを指定してください');
        }
    }

    /**
     * 本文のバリデーション
     */
    private function validateContent(string $content): void
    {
        if (empty(trim($content))) {
            throw new InvalidArgumentException('コメント本文は空にできません');
        }

        if (mb_strlen($content) > 1000) {
            throw new InvalidArgumentException('コメントは1000文字以下で指定してください');
        }
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): CommentStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Setters
    public function setContent(string $content): void
    {
        $this->validateContent($content);
        $this->content = $content;
    }

    /**
     * コメントを承認する
     */
    public function approve(): void
    {
        $this->status = CommentStatus::APPROVED;
    }

    /**
     * コメントを却下する
     */
    public function reject(): void
    {
        $this->status = CommentStatus::REJECTED;
    }

    /**
     * コメントを保留にする
     */
    public function setPending(): void
    {
        $this->status = CommentStatus::PENDING;
    }

    /**
     * コメントが承認されているか確認
     *
     * @return bool 承認されている場合true
     */
    public function isApproved(): bool
    {
        return $this->status === CommentStatus::APPROVED;
    }

    /**
     * コメントが却下されているか確認
     *
     * @return bool 却下されている場合true
     */
    public function isRejected(): bool
    {
        return $this->status === CommentStatus::REJECTED;
    }

    /**
     * コメントが保留中か確認
     *
     * @return bool 保留中の場合true
     */
    public function isPending(): bool
    {
        return $this->status === CommentStatus::PENDING;
    }

    /**
     * ログインユーザーのコメントか確認
     *
     * @return bool ログインユーザーの場合true
     */
    public function isFromRegisteredUser(): bool
    {
        return $this->userId !== null;
    }

    /**
     * ゲストユーザーのコメントか確認
     *
     * @return bool ゲストユーザーの場合true
     */
    public function isFromGuest(): bool
    {
        return $this->userId === null;
    }
}
