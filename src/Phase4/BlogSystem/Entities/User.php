<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * ユーザーエンティティ
 *
 * ユーザー情報を表現するドメインモデル
 */
class User
{
    /**
     * コンストラクタ
     *
     * @param int $id ユーザーID
     * @param string $username ユーザー名（ログイン用）
     * @param string $email メールアドレス
     * @param string $passwordHash パスワードハッシュ
     * @param string $displayName 表示名
     * @param string|null $bio 自己紹介
     * @param DateTimeImmutable $createdAt 作成日時
     * @param DateTimeImmutable $updatedAt 更新日時
     */
    public function __construct(
        private readonly int $id,
        private string $username,
        private string $email,
        private string $passwordHash,
        private string $displayName,
        private ?string $bio = null,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private DateTimeImmutable $updatedAt = new DateTimeImmutable(),
    ) {
        $this->validateUsername($username);
        $this->validateEmail($email);
        $this->validateDisplayName($displayName);
    }

    /**
     * ユーザー名のバリデーション
     */
    private function validateUsername(string $username): void
    {
        if (empty(trim($username))) {
            throw new InvalidArgumentException('ユーザー名は空にできません');
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new InvalidArgumentException('ユーザー名は3文字以上50文字以下で指定してください');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new InvalidArgumentException('ユーザー名は英数字とアンダースコアのみ使用できます');
        }
    }

    /**
     * メールアドレスのバリデーション
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('有効なメールアドレスを指定してください');
        }
    }

    /**
     * 表示名のバリデーション
     */
    private function validateDisplayName(string $displayName): void
    {
        if (empty(trim($displayName))) {
            throw new InvalidArgumentException('表示名は空にできません');
        }

        if (mb_strlen($displayName) > 100) {
            throw new InvalidArgumentException('表示名は100文字以下で指定してください');
        }
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getBio(): ?string
    {
        return $this->bio;
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
    public function setUsername(string $username): void
    {
        $this->validateUsername($username);
        $this->username = $username;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setEmail(string $email): void
    {
        $this->validateEmail($email);
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setDisplayName(string $displayName): void
    {
        $this->validateDisplayName($displayName);
        $this->displayName = $displayName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * パスワードの検証
     *
     * @param string $password 平文パスワード
     * @return bool 検証結果
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * パスワードの変更
     *
     * @param string $newPassword 新しいパスワード
     */
    public function changePassword(string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('パスワードは8文字以上で指定してください');
        }

        $this->passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * パスワードハッシュが再ハッシュ化を必要とするか確認
     *
     * @return bool 再ハッシュ化が必要な場合true
     */
    public function needsRehash(): bool
    {
        return password_needs_rehash($this->passwordHash, PASSWORD_ARGON2ID);
    }
}
