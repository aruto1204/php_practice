<?php

declare(strict_types=1);

namespace Tests;

use App\Phase4\User;
use PHPUnit\Framework\TestCase;

/**
 * User クラスのテスト
 */
class UserTest extends TestCase
{
    // ============================================
    // コンストラクタのテスト
    // ============================================

    public function testConstructorCreatesUser(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('john_doe', $user->getUsername());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertNull($user->getLastLoginAt());
    }

    public function testConstructorWithInvalidEmailThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("無効なメールアドレスです");

        new User(1, 'john_doe', 'invalid-email', 'Password123');
    }

    // ============================================
    // パスワード検証のテスト
    // ============================================

    public function testVerifyPasswordWithCorrectPassword(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $this->assertTrue($user->verifyPassword('Password123'));
    }

    public function testVerifyPasswordWithWrongPassword(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $this->assertFalse($user->verifyPassword('WrongPassword'));
    }

    // ============================================
    // メールアドレス更新のテスト
    // ============================================

    public function testUpdateEmailWithValidEmail(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $user->updateEmail('newemail@example.com');

        $this->assertEquals('newemail@example.com', $user->getEmail());
    }

    public function testUpdateEmailWithInvalidEmailThrowsException(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("無効なメールアドレスです");

        $user->updateEmail('invalid-email');
    }

    // ============================================
    // パスワード更新のテスト
    // ============================================

    public function testUpdatePassword(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'OldPassword123');

        $this->assertTrue($user->verifyPassword('OldPassword123'));

        $user->updatePassword('NewPassword456');

        $this->assertFalse($user->verifyPassword('OldPassword123'));
        $this->assertTrue($user->verifyPassword('NewPassword456'));
    }

    // ============================================
    // ログイン記録のテスト
    // ============================================

    public function testRecordLogin(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $this->assertNull($user->getLastLoginAt());

        $user->recordLogin();

        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getLastLoginAt());
    }

    public function testRecordLoginUpdatesTimestamp(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $user->recordLogin();
        $firstLogin = $user->getLastLoginAt();

        sleep(1); // 1秒待機

        $user->recordLogin();
        $secondLogin = $user->getLastLoginAt();

        $this->assertNotEquals($firstLogin, $secondLogin);
        $this->assertGreaterThan($firstLogin, $secondLogin);
    }

    // ============================================
    // 作成日からの日数のテスト
    // ============================================

    public function testGetDaysSinceCreation(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $days = $user->getDaysSinceCreation();

        $this->assertEquals(0, $days);
    }

    // ============================================
    // ゲッターのテスト
    // ============================================

    public function testGetters(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $this->assertIsInt($user->getId());
        $this->assertIsString($user->getUsername());
        $this->assertIsString($user->getEmail());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    // ============================================
    // イミュータブル性のテスト
    // ============================================

    public function testCreatedAtIsImmutable(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');

        $createdAt1 = $user->getCreatedAt();
        $createdAt2 = $user->getCreatedAt();

        // 同じインスタンスではないが、同じ時刻を表す
        $this->assertEquals($createdAt1, $createdAt2);
    }

    public function testLastLoginAtIsImmutable(): void
    {
        $user = new User(1, 'john_doe', 'john@example.com', 'Password123');
        $user->recordLogin();

        $lastLogin1 = $user->getLastLoginAt();
        $lastLogin2 = $user->getLastLoginAt();

        $this->assertEquals($lastLogin1, $lastLogin2);
    }
}
