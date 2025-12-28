<?php

declare(strict_types=1);

namespace Phase4\RestApi\Repositories;

use PDO;
use Phase4\RestApi\Entities\User;

/**
 * ユーザーリポジトリ
 *
 * ユーザーエンティティのデータアクセスを担当
 */
class UserRepository
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * ユーザーを作成
     *
     * @param User $user ユーザーエンティティ
     * @return User
     */
    public function create(User $user): User
    {
        $sql = '
            INSERT INTO users (username, email, password_hash, full_name, is_admin, created_at, updated_at)
            VALUES (:username, :email, :password_hash, :full_name, :is_admin, :created_at, :updated_at)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'full_name' => $user->getFullName(),
            'is_admin' => $user->isAdmin() ? 1 : 0,
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ]);

        $user->setId((int) $this->pdo->lastInsertId());
        return $user;
    }

    /**
     * IDでユーザーを検索
     *
     * @param int $id ユーザーID
     * @return User|null
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
     * @return User|null
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
     * @return User|null
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
     * すべてのユーザーを取得（ページネーション対応）
     *
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return User[]
     */
    public function findAll(int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = $this->hydrate($row);
        }

        return $users;
    }

    /**
     * ユーザーの総数を取得
     *
     * @return int
     */
    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM users';
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * ユーザーを更新
     *
     * @param User $user ユーザーエンティティ
     * @return User
     */
    public function update(User $user): User
    {
        $sql = '
            UPDATE users
            SET email = :email,
                password_hash = :password_hash,
                full_name = :full_name,
                is_admin = :is_admin,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'full_name' => $user->getFullName(),
            'is_admin' => $user->isAdmin() ? 1 : 0,
            'updated_at' => $user->getUpdatedAt(),
        ]);

        return $user;
    }

    /**
     * ユーザーを削除
     *
     * @param int $id ユーザーID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * ユーザー名の重複をチェック
     *
     * @param string $username ユーザー名
     * @param int|null $excludeId 除外するユーザーID（更新時に自分自身を除外）
     * @return bool
     */
    public function existsByUsername(string $username, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $sql = 'SELECT COUNT(*) FROM users WHERE username = :username';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['username' => $username]);
        } else {
            $sql = 'SELECT COUNT(*) FROM users WHERE username = :username AND id != :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'id' => $excludeId]);
        }

        return $stmt->fetchColumn() > 0;
    }

    /**
     * メールアドレスの重複をチェック
     *
     * @param string $email メールアドレス
     * @param int|null $excludeId 除外するユーザーID（更新時に自分自身を除外）
     * @return bool
     */
    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
        } else {
            $sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND id != :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
        }

        return $stmt->fetchColumn() > 0;
    }

    /**
     * データベースの行からUserエンティティを生成
     *
     * @param array<string, mixed> $row
     * @return User
     */
    private function hydrate(array $row): User
    {
        $user = new User(
            id: (int) $row['id'],
            username: $row['username'],
            email: $row['email'],
            passwordHash: $row['password_hash'],
            fullName: $row['full_name'],
            isAdmin: (bool) $row['is_admin'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );

        return $user;
    }
}
