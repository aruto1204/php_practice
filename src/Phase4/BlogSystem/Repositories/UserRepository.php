<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Repositories;

use App\Phase4\BlogSystem\Database;
use App\Phase4\BlogSystem\Entities\User;
use DateTimeImmutable;
use PDO;

/**
 * ユーザーリポジトリ
 *
 * ユーザーデータへのアクセスを提供
 */
class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * ユーザーを作成
     *
     * @param string $username ユーザー名
     * @param string $email メールアドレス
     * @param string $password 平文パスワード
     * @param string $displayName 表示名
     * @param string|null $bio 自己紹介
     * @return User 作成されたユーザー
     */
    public function create(
        string $username,
        string $email,
        string $password,
        string $displayName,
        ?string $bio = null
    ): User {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $now = new DateTimeImmutable();

        $sql = '
            INSERT INTO users (username, email, password_hash, display_name, bio, created_at, updated_at)
            VALUES (:username, :email, :password_hash, :display_name, :bio, :created_at, :updated_at)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'display_name' => $displayName,
            'bio' => $bio,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return new User(
            $id,
            $username,
            $email,
            $passwordHash,
            $displayName,
            $bio,
            $now,
            $now
        );
    }

    /**
     * IDでユーザーを検索
     *
     * @param int $id ユーザーID
     * @return User|null ユーザー
     */
    public function findById(int $id): ?User
    {
        $sql = 'SELECT * FROM users WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * ユーザー名でユーザーを検索
     *
     * @param string $username ユーザー名
     * @return User|null ユーザー
     */
    public function findByUsername(string $username): ?User
    {
        $sql = 'SELECT * FROM users WHERE username = :username';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);

        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * メールアドレスでユーザーを検索
     *
     * @param string $email メールアドレス
     * @return User|null ユーザー
     */
    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM users WHERE email = :email';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * すべてのユーザーを取得
     *
     * @return User[] ユーザー配列
     */
    public function findAll(): array
    {
        $sql = 'SELECT * FROM users ORDER BY created_at DESC';
        $stmt = $this->pdo->query($sql);

        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = $this->hydrate($row);
        }

        return $users;
    }

    /**
     * ユーザーを更新
     *
     * @param User $user ユーザー
     */
    public function update(User $user): void
    {
        $sql = '
            UPDATE users
            SET username = :username,
                email = :email,
                password_hash = :password_hash,
                display_name = :display_name,
                bio = :bio,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'display_name' => $user->getDisplayName(),
            'bio' => $user->getBio(),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * ユーザーを削除
     *
     * @param int $id ユーザーID
     */
    public function delete(int $id): void
    {
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * データベースの行からUserオブジェクトを生成
     *
     * @param array<string, mixed> $row データベースの行
     * @return User ユーザー
     */
    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['username'],
            $row['email'],
            $row['password_hash'],
            $row['display_name'],
            $row['bio'],
            new DateTimeImmutable($row['created_at']),
            new DateTimeImmutable($row['updated_at'])
        );
    }
}
