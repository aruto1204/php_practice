<?php

declare(strict_types=1);

namespace Tests\BlogSystem;

use App\Phase4\BlogSystem\Entities\User;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Userエンティティのテスト
 */
class UserTest extends TestCase
{
    /**
     * ユーザーの作成をテスト
     */
    public function testCreateUser(): void
    {
        $user = new User(
            1,
            'testuser',
            'test@example.com',
            password_hash('password123', PASSWORD_ARGON2ID),
            'Test User'
        );

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getDisplayName());
        $this->assertNull($user->getBio());
    }

    /**
     * パスワードの検証をテスト
     */
    public function testVerifyPassword(): void
    {
        $password = 'password123';
        $user = new User(
            1,
            'testuser',
            'test@example.com',
            password_hash($password, PASSWORD_ARGON2ID),
            'Test User'
        );

        $this->assertTrue($user->verifyPassword($password));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    /**
     * パスワードの変更をテスト
     */
    public function testChangePassword(): void
    {
        $user = new User(
            1,
            'testuser',
            'test@example.com',
            password_hash('oldpassword', PASSWORD_ARGON2ID),
            'Test User'
        );

        $user->changePassword('newpassword123');

        $this->assertFalse($user->verifyPassword('oldpassword'));
        $this->assertTrue($user->verifyPassword('newpassword123'));
    }

    /**
     * 短いパスワードで例外が発生することをテスト
     */
    public function testChangePasswordThrowsExceptionForShortPassword(): void
    {
        $user = new User(
            1,
            'testuser',
            'test@example.com',
            password_hash('password123', PASSWORD_ARGON2ID),
            'Test User'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('パスワードは8文字以上で指定してください');

        $user->changePassword('short');
    }

    /**
     * 無効なユーザー名で例外が発生することをテスト
     */
    public function testInvalidUsername(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ユーザー名は英数字とアンダースコアのみ使用できます');

        new User(
            1,
            'invalid-user!',
            'test@example.com',
            'hash',
            'Test User'
        );
    }

    /**
     * 無効なメールアドレスで例外が発生することをテスト
     */
    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('有効なメールアドレスを指定してください');

        new User(
            1,
            'testuser',
            'invalid-email',
            'hash',
            'Test User'
        );
    }

    /**
     * 空の表示名で例外が発生することをテスト
     */
    public function testEmptyDisplayName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('表示名は空にできません');

        new User(
            1,
            'testuser',
            'test@example.com',
            'hash',
            ''
        );
    }

    /**
     * ユーザー情報の更新をテスト
     */
    public function testUpdateUserInfo(): void
    {
        $user = new User(
            1,
            'testuser',
            'test@example.com',
            'hash',
            'Test User'
        );

        $user->setDisplayName('Updated Name');
        $user->setBio('This is my bio');

        $this->assertEquals('Updated Name', $user->getDisplayName());
        $this->assertEquals('This is my bio', $user->getBio());
    }
}
