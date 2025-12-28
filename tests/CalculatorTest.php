<?php

declare(strict_types=1);

namespace Tests;

use App\Phase4\Calculator;
use PHPUnit\Framework\TestCase;

/**
 * Calculator クラスのテスト
 */
class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    /**
     * 各テストメソッド実行前に呼ばれる
     */
    protected function setUp(): void
    {
        $this->calculator = new Calculator();
    }

    /**
     * 各テストメソッド実行後に呼ばれる
     */
    protected function tearDown(): void
    {
        // クリーンアップ処理（必要に応じて）
    }

    // ============================================
    // 加算のテスト
    // ============================================

    public function testAddPositiveNumbers(): void
    {
        $result = $this->calculator->add(5, 3);
        $this->assertEquals(8, $result);
    }

    public function testAddNegativeNumbers(): void
    {
        $result = $this->calculator->add(-5, -3);
        $this->assertEquals(-8, $result);
    }

    public function testAddMixedNumbers(): void
    {
        $result = $this->calculator->add(10, -5);
        $this->assertEquals(5, $result);
    }

    public function testAddFloatNumbers(): void
    {
        $result = $this->calculator->add(1.5, 2.3);
        $this->assertEquals(3.8, $result, '', 0.0001);
    }

    public function testAddZero(): void
    {
        $result = $this->calculator->add(5, 0);
        $this->assertEquals(5, $result);
    }

    // ============================================
    // 減算のテスト
    // ============================================

    public function testSubtractPositiveNumbers(): void
    {
        $result = $this->calculator->subtract(10, 3);
        $this->assertEquals(7, $result);
    }

    public function testSubtractNegativeNumbers(): void
    {
        $result = $this->calculator->subtract(-5, -3);
        $this->assertEquals(-2, $result);
    }

    public function testSubtractResultNegative(): void
    {
        $result = $this->calculator->subtract(3, 10);
        $this->assertEquals(-7, $result);
    }

    // ============================================
    // 乗算のテスト
    // ============================================

    public function testMultiplyPositiveNumbers(): void
    {
        $result = $this->calculator->multiply(5, 3);
        $this->assertEquals(15, $result);
    }

    public function testMultiplyByZero(): void
    {
        $result = $this->calculator->multiply(5, 0);
        $this->assertEquals(0, $result);
    }

    public function testMultiplyNegativeNumbers(): void
    {
        $result = $this->calculator->multiply(-5, -3);
        $this->assertEquals(15, $result);
    }

    public function testMultiplyMixedNumbers(): void
    {
        $result = $this->calculator->multiply(-5, 3);
        $this->assertEquals(-15, $result);
    }

    // ============================================
    // 除算のテスト
    // ============================================

    public function testDividePositiveNumbers(): void
    {
        $result = $this->calculator->divide(10, 2);
        $this->assertEquals(5, $result);
    }

    public function testDivideWithRemainder(): void
    {
        $result = $this->calculator->divide(10, 3);
        $this->assertEquals(3.333333, $result, '', 0.0001);
    }

    public function testDivideByZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("ゼロで割ることはできません");
        $this->calculator->divide(10, 0);
    }

    public function testDivideNegativeNumbers(): void
    {
        $result = $this->calculator->divide(-10, -2);
        $this->assertEquals(5, $result);
    }

    // ============================================
    // 累乗のテスト
    // ============================================

    public function testPowerPositiveExponent(): void
    {
        $result = $this->calculator->power(2, 3);
        $this->assertEquals(8, $result);
    }

    public function testPowerZeroExponent(): void
    {
        $result = $this->calculator->power(5, 0);
        $this->assertEquals(1, $result);
    }

    public function testPowerNegativeExponent(): void
    {
        $result = $this->calculator->power(2, -2);
        $this->assertEquals(0.25, $result);
    }

    // ============================================
    // 平方根のテスト
    // ============================================

    public function testSquareRootPositiveNumber(): void
    {
        $result = $this->calculator->squareRoot(16);
        $this->assertEquals(4, $result);
    }

    public function testSquareRootZero(): void
    {
        $result = $this->calculator->squareRoot(0);
        $this->assertEquals(0, $result);
    }

    public function testSquareRootNegativeNumberThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("負の数の平方根は計算できません");
        $this->calculator->squareRoot(-1);
    }

    public function testSquareRootFloat(): void
    {
        $result = $this->calculator->squareRoot(2);
        $this->assertEquals(1.41421, $result, '', 0.0001);
    }

    // ============================================
    // データプロバイダーを使ったテスト
    // ============================================

    /**
     * @dataProvider additionProvider
     */
    public function testAdditionWithDataProvider(int|float $a, int|float $b, int|float $expected): void
    {
        $result = $this->calculator->add($a, $b);
        $this->assertEquals($expected, $result);
    }

    public static function additionProvider(): array
    {
        return [
            'positive numbers' => [2, 3, 5],
            'negative numbers' => [-2, -3, -5],
            'mixed numbers' => [10, -5, 5],
            'with zero' => [5, 0, 5],
            'float numbers' => [1.5, 2.5, 4.0],
        ];
    }
}
