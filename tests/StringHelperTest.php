<?php

declare(strict_types=1);

namespace Tests;

use App\Phase4\StringHelper;
use PHPUnit\Framework\TestCase;

/**
 * StringHelper クラスのテスト
 */
class StringHelperTest extends TestCase
{
    private StringHelper $stringHelper;

    protected function setUp(): void
    {
        $this->stringHelper = new StringHelper();
    }

    // ============================================
    // reverse のテスト
    // ============================================

    public function testReverse(): void
    {
        $result = $this->stringHelper->reverse('hello');
        $this->assertEquals('olleh', $result);
    }

    public function testReverseEmptyString(): void
    {
        $result = $this->stringHelper->reverse('');
        $this->assertEquals('', $result);
    }

    public function testReverseSingleCharacter(): void
    {
        $result = $this->stringHelper->reverse('a');
        $this->assertEquals('a', $result);
    }

    // ============================================
    // isPalindrome のテスト
    // ============================================

    public function testIsPalindromeTrue(): void
    {
        $this->assertTrue($this->stringHelper->isPalindrome('racecar'));
        $this->assertTrue($this->stringHelper->isPalindrome('A man a plan a canal Panama'));
        $this->assertTrue($this->stringHelper->isPalindrome('Was it a car or a cat I saw'));
    }

    public function testIsPalindromeFalse(): void
    {
        $this->assertFalse($this->stringHelper->isPalindrome('hello'));
        $this->assertFalse($this->stringHelper->isPalindrome('world'));
    }

    public function testIsPalindromeEmptyString(): void
    {
        $this->assertTrue($this->stringHelper->isPalindrome(''));
    }

    // ============================================
    // countWords のテスト
    // ============================================

    public function testCountWords(): void
    {
        $result = $this->stringHelper->countWords('Hello World PHP');
        $this->assertEquals(3, $result);
    }

    public function testCountWordsEmptyString(): void
    {
        $result = $this->stringHelper->countWords('');
        $this->assertEquals(0, $result);
    }

    public function testCountWordsSingleWord(): void
    {
        $result = $this->stringHelper->countWords('Hello');
        $this->assertEquals(1, $result);
    }

    // ============================================
    // toUpperCase のテスト
    // ============================================

    public function testToUpperCase(): void
    {
        $result = $this->stringHelper->toUpperCase('hello');
        $this->assertEquals('HELLO', $result);
    }

    public function testToUpperCaseMultibyte(): void
    {
        $result = $this->stringHelper->toUpperCase('こんにちは');
        $this->assertEquals('こんにちは', $result);
    }

    // ============================================
    // toLowerCase のテスト
    // ============================================

    public function testToLowerCase(): void
    {
        $result = $this->stringHelper->toLowerCase('HELLO');
        $this->assertEquals('hello', $result);
    }

    // ============================================
    // toCamelCase のテスト
    // ============================================

    public function testToCamelCaseFromSnakeCase(): void
    {
        $result = $this->stringHelper->toCamelCase('hello_world_php');
        $this->assertEquals('helloWorldPhp', $result);
    }

    public function testToCamelCaseFromKebabCase(): void
    {
        $result = $this->stringHelper->toCamelCase('hello-world-php');
        $this->assertEquals('helloWorldPhp', $result);
    }

    public function testToCamelCaseFromSpaces(): void
    {
        $result = $this->stringHelper->toCamelCase('hello world php');
        $this->assertEquals('helloWorldPhp', $result);
    }

    // ============================================
    // toSnakeCase のテスト
    // ============================================

    public function testToSnakeCaseFromCamelCase(): void
    {
        $result = $this->stringHelper->toSnakeCase('helloWorldPhp');
        $this->assertEquals('hello_world_php', $result);
    }

    public function testToSnakeCaseFromPascalCase(): void
    {
        $result = $this->stringHelper->toSnakeCase('HelloWorldPhp');
        $this->assertEquals('hello_world_php', $result);
    }

    public function testToSnakeCaseLowerCase(): void
    {
        $result = $this->stringHelper->toSnakeCase('hello');
        $this->assertEquals('hello', $result);
    }

    // ============================================
    // データプロバイダーを使ったテスト
    // ============================================

    /**
     * @dataProvider reverseProvider
     */
    public function testReverseWithDataProvider(string $input, string $expected): void
    {
        $result = $this->stringHelper->reverse($input);
        $this->assertEquals($expected, $result);
    }

    public static function reverseProvider(): array
    {
        return [
            ['hello', 'olleh'],
            ['world', 'dlrow'],
            ['PHP', 'PHP'],
            ['a', 'a'],
            ['', ''],
        ];
    }
}
