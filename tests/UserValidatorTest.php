<?php

declare(strict_types=1);

namespace Tests;

use App\Phase4\UserValidator;
use PHPUnit\Framework\TestCase;

/**
 * UserValidator クラスのテスト
 */
class UserValidatorTest extends TestCase
{
    private UserValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UserValidator();
    }

    // ============================================
    // validateEmail のテスト
    // ============================================

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidateEmailValid(string $email): void
    {
        $this->assertTrue($this->validator->validateEmail($email));
    }

    public static function validEmailProvider(): array
    {
        return [
            ['test@example.com'],
            ['user.name@example.com'],
            ['user+tag@example.co.jp'],
            ['user_name@example-site.com'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testValidateEmailInvalid(string $email): void
    {
        $this->assertFalse($this->validator->validateEmail($email));
    }

    public static function invalidEmailProvider(): array
    {
        return [
            ['invalid-email'],
            ['@example.com'],
            ['user@'],
            ['user @example.com'],
            [''],
        ];
    }

    // ============================================
    // validatePassword のテスト
    // ============================================

    /**
     * @dataProvider validPasswordProvider
     */
    public function testValidatePasswordValid(string $password): void
    {
        $this->assertTrue($this->validator->validatePassword($password));
    }

    public static function validPasswordProvider(): array
    {
        return [
            ['Password123'],
            ['Abcdef12'],
            ['MyP@ssw0rd'],
            ['SecurePass123'],
        ];
    }

    /**
     * @dataProvider invalidPasswordProvider
     */
    public function testValidatePasswordInvalid(string $password): void
    {
        $this->assertFalse($this->validator->validatePassword($password));
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            'too short' => ['Pass1'],
            'no uppercase' => ['password123'],
            'no lowercase' => ['PASSWORD123'],
            'no number' => ['Password'],
            'empty' => [''],
        ];
    }

    // ============================================
    // validateUsername のテスト
    // ============================================

    /**
     * @dataProvider validUsernameProvider
     */
    public function testValidateUsernameValid(string $username): void
    {
        $this->assertTrue($this->validator->validateUsername($username));
    }

    public static function validUsernameProvider(): array
    {
        return [
            ['john_doe'],
            ['user123'],
            ['alice'],
            ['test_user_name_123'],
        ];
    }

    /**
     * @dataProvider invalidUsernameProvider
     */
    public function testValidateUsernameInvalid(string $username): void
    {
        $this->assertFalse($this->validator->validateUsername($username));
    }

    public static function invalidUsernameProvider(): array
    {
        return [
            'too short' => ['ab'],
            'too long' => ['this_is_a_very_long_username_that_exceeds_limit'],
            'special chars' => ['user@name'],
            'spaces' => ['user name'],
            'hyphen' => ['user-name'],
            'empty' => [''],
        ];
    }

    // ============================================
    // validateAge のテスト
    // ============================================

    /**
     * @dataProvider validAgeProvider
     */
    public function testValidateAgeValid(int $age): void
    {
        $this->assertTrue($this->validator->validateAge($age));
    }

    public static function validAgeProvider(): array
    {
        return [
            [0],
            [1],
            [25],
            [100],
            [150],
        ];
    }

    /**
     * @dataProvider invalidAgeProvider
     */
    public function testValidateAgeInvalid(int $age): void
    {
        $this->assertFalse($this->validator->validateAge($age));
    }

    public static function invalidAgeProvider(): array
    {
        return [
            [-1],
            [-100],
            [151],
            [200],
        ];
    }
}
