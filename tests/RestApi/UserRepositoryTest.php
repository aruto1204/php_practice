<?php

declare(strict_types=1);

namespace Tests\RestApi;

use PHPUnit\Framework\TestCase;
use Phase4\RestApi\Database;
use Phase4\RestApi\Repositories\UserRepository;
use Phase4\RestApi\Entities\User;

/**
 * ユーザーリポジトリのテスト
 */
class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        Database::resetConnection();
        $pdo = Database::getConnection();
        $this->repository = new UserRepository($pdo);

        // テーブルをクリア
        Database::clearAllTables();
    }

    /**
     * @test
     */
    public function ユーザーを作成できる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $created = $this->repository->create($user);

        $this->assertNotNull($created->getId());
        $this->assertEquals('testuser', $created->getUsername());
        $this->assertEquals('test@example.com', $created->getEmail());
    }

    /**
     * @test
     */
    public function IDでユーザーを検索できる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $created = $this->repository->create($user);

        $found = $this->repository->findById($created->getId());

        $this->assertNotNull($found);
        $this->assertEquals($created->getId(), $found->getId());
        $this->assertEquals('testuser', $found->getUsername());
    }

    /**
     * @test
     */
    public function ユーザー名で検索できる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $this->repository->create($user);

        $found = $this->repository->findByUsername('testuser');

        $this->assertNotNull($found);
        $this->assertEquals('testuser', $found->getUsername());
    }

    /**
     * @test
     */
    public function メールアドレスで検索できる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $this->repository->create($user);

        $found = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals('test@example.com', $found->getEmail());
    }

    /**
     * @test
     */
    public function ユーザーを更新できる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $created = $this->repository->create($user);

        $created->update('newemail@example.com', 'New Name');
        $this->repository->update($created);

        $updated = $this->repository->findById($created->getId());

        $this->assertEquals('newemail@example.com', $updated->getEmail());
        $this->assertEquals('New Name', $updated->getFullName());
    }

    /**
     * @test
     */
    public function ユーザーを削除できる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $created = $this->repository->create($user);

        $deleted = $this->repository->delete($created->getId());

        $this->assertTrue($deleted);
        $this->assertNull($this->repository->findById($created->getId()));
    }

    /**
     * @test
     */
    public function ユーザー名の重複をチェックできる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $this->repository->create($user);

        $this->assertTrue($this->repository->existsByUsername('testuser'));
        $this->assertFalse($this->repository->existsByUsername('otheruser'));
    }

    /**
     * @test
     */
    public function メールアドレスの重複をチェックできる(): void
    {
        $user = User::create('testuser', 'test@example.com', 'Password123', 'Test User');
        $this->repository->create($user);

        $this->assertTrue($this->repository->existsByEmail('test@example.com'));
        $this->assertFalse($this->repository->existsByEmail('other@example.com'));
    }

    /**
     * @test
     */
    public function ユーザー一覧を取得できる(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create("user{$i}", "user{$i}@example.com", 'Password123', "User {$i}");
            $this->repository->create($user);
        }

        $users = $this->repository->findAll(3, 0);

        $this->assertCount(3, $users);
        $this->assertInstanceOf(User::class, $users[0]);
    }

    /**
     * @test
     */
    public function ユーザー総数を取得できる(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create("user{$i}", "user{$i}@example.com", 'Password123', "User {$i}");
            $this->repository->create($user);
        }

        $count = $this->repository->count();

        $this->assertEquals(5, $count);
    }
}
