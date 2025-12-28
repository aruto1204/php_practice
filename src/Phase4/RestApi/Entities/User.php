<?php

declare(strict_types=1);

namespace Phase4\RestApi\Entities;

use InvalidArgumentException;

/**
 * ユーザーエンティティ
 *
 * REST APIのユーザーリソースを表現するエンティティクラス
 */
class User
{
    /**
     * コンストラクタ
     *
     * @param int|null $id ユーザーID
     * @param string $username ユーザー名（3-20文字）
     * @param string $email メールアドレス
     * @param string $passwordHash パスワードハッシュ
     * @param string $fullName フルネーム
     * @param bool $isAdmin 管理者フラグ
     * @param string|null $createdAt 作成日時
     * @param string|null $updatedAt 更新日時
     */
    public function __construct(
        private ?int $id,
        private string $username,
        private string $email,
        private string $passwordHash,
        private string $fullName,
        private bool $isAdmin = false,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {
        $this->validate();

        if ($this->createdAt === null) {
            $this->createdAt = date('Y-m-d H:i:s');
        }
        if ($this->updatedAt === null) {
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    /**
     * バリデーション
     */
    private function validate(): void
    {
        // ユーザー名のバリデーション
        if (strlen($this->username) < 3 || strlen($this->username) > 20) {
            throw new InvalidArgumentException('ユーザー名は3文字以上20文字以下で指定してください');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->username)) {
            throw new InvalidArgumentException('ユーザー名は半角英数字とアンダースコアのみ使用できます');
        }

        // メールアドレスのバリデーション
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('有効なメールアドレスを指定してください');
        }

        // フルネームのバリデーション
        if (strlen(trim($this->fullName)) === 0) {
            throw new InvalidArgumentException('フルネームを指定してください');
        }
        if (strlen($this->fullName) > 100) {
            throw new InvalidArgumentException('フルネームは100文字以下で指定してください');
        }
    }

    /**
     * ファクトリーメソッド：新規ユーザーの作成
     *
     * @param string $username ユーザー名
     * @param string $email メールアドレス
     * @param string $password 平文パスワード
     * @param string $fullName フルネーム
     * @param bool $isAdmin 管理者フラグ
     * @return self
     */
    public static function create(
        string $username,
        string $email,
        string $password,
        string $fullName,
        bool $isAdmin = false,
    ): self {
        // パスワードのバリデーション
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('パスワードは8文字以上で指定してください');
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new InvalidArgumentException('パスワードは大文字、小文字、数字を含む必要があります');
        }

        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        return new self(null, $username, $email, $passwordHash, $fullName, $isAdmin);
    }

    /**
     * パスワードの検証
     *
     * @param string $password 平文パスワード
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * パスワードの変更
     *
     * @param string $newPassword 新しいパスワード
     * @return void
     */
    public function changePassword(string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('パスワードは8文字以上で指定してください');
        }
        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            throw new InvalidArgumentException('パスワードは大文字、小文字、数字を含む必要があります');
        }

        $this->passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $this->touch();
    }

    /**
     * ユーザー情報の更新
     *
     * @param string|null $email メールアドレス
     * @param string|null $fullName フルネーム
     * @return void
     */
    public function update(?string $email = null, ?string $fullName = null): void
    {
        if ($email !== null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('有効なメールアドレスを指定してください');
            }
            $this->email = $email;
        }

        if ($fullName !== null) {
            if (strlen(trim($fullName)) === 0) {
                throw new InvalidArgumentException('フルネームを指定してください');
            }
            if (strlen($fullName) > 100) {
                throw new InvalidArgumentException('フルネームは100文字以下で指定してください');
            }
            $this->fullName = $fullName;
        }

        $this->touch();
    }

    /**
     * 管理者権限の付与/剥奪
     *
     * @param bool $isAdmin 管理者フラグ
     * @return void
     */
    public function setAdminStatus(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
        $this->touch();
    }

    /**
     * 更新日時の更新
     */
    private function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    /**
     * APIレスポンス用の配列に変換（パスワードハッシュを除外）
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'full_name' => $this->fullName,
            'is_admin' => $this->isAdmin,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    // ゲッター

    public function getId(): ?int
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

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    // セッター（IDは外部から設定できるようにする - リポジトリで使用）

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
