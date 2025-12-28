<?php

declare(strict_types=1);

namespace Tests;

use App\Phase4\ShoppingCart;
use App\Phase4\PriceCalculator;
use PHPUnit\Framework\TestCase;

/**
 * ShoppingCart クラスのテスト
 * モックを使った依存性のテスト
 */
class ShoppingCartTest extends TestCase
{
    // ============================================
    // 基本機能のテスト
    // ============================================

    public function testAddItem(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);

        $cart->addItem('商品A', 1000, 2);

        $this->assertCount(1, $cart->getItems());
        $this->assertEquals(2, $cart->getItemCount());
    }

    public function testAddMultipleItems(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);

        $cart->addItem('商品A', 1000, 2);
        $cart->addItem('商品B', 500, 1);
        $cart->addItem('商品C', 2000, 3);

        $this->assertCount(3, $cart->getItems());
        $this->assertEquals(6, $cart->getItemCount()); // 2 + 1 + 3
    }

    public function testAddItemWithNegativePriceThrowsException(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("価格は0以上である必要があります");
        $cart->addItem('商品A', -100, 1);
    }

    public function testAddItemWithZeroQuantityThrowsException(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("数量は1以上である必要があります");
        $cart->addItem('商品A', 1000, 0);
    }

    // ============================================
    // モックを使ったテスト
    // ============================================

    public function testGetSubtotalUsesCalculator(): void
    {
        // PriceCalculatorのモックを作成
        $priceCalculator = $this->createMock(PriceCalculator::class);

        // calculateTotal メソッドが呼ばれることを期待
        $priceCalculator
            ->expects($this->once())
            ->method('calculateTotal')
            ->willReturn(5000.0);

        $cart = new ShoppingCart($priceCalculator);
        $cart->addItem('商品A', 1000, 2);
        $cart->addItem('商品B', 3000, 1);

        $subtotal = $cart->getSubtotal();
        $this->assertEquals(5000.0, $subtotal);
    }

    public function testGetTaxUsesCalculator(): void
    {
        // PriceCalculatorのモックを作成
        $priceCalculator = $this->createMock(PriceCalculator::class);

        $priceCalculator
            ->method('calculateTotal')
            ->willReturn(10000.0);

        // calculateTax メソッドが10000.0で呼ばれることを期待
        $priceCalculator
            ->expects($this->once())
            ->method('calculateTax')
            ->with(10000.0)
            ->willReturn(1000.0);

        $cart = new ShoppingCart($priceCalculator);
        $cart->addItem('商品A', 10000, 1);

        $tax = $cart->getTax();
        $this->assertEquals(1000.0, $tax);
    }

    public function testGetTotal(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);

        $priceCalculator
            ->method('calculateTotal')
            ->willReturn(10000.0);

        $priceCalculator
            ->method('calculateTax')
            ->willReturn(1000.0);

        $cart = new ShoppingCart($priceCalculator);
        $cart->addItem('商品A', 10000, 1);

        $total = $cart->getTotal();
        $this->assertEquals(11000.0, $total);
    }

    // ============================================
    // その他の機能テスト
    // ============================================

    public function testIsEmptyWhenNew(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);

        $this->assertTrue($cart->isEmpty());
    }

    public function testIsEmptyAfterAddingItem(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);
        $cart->addItem('商品A', 1000, 1);

        $this->assertFalse($cart->isEmpty());
    }

    public function testClear(): void
    {
        $priceCalculator = $this->createMock(PriceCalculator::class);
        $cart = new ShoppingCart($priceCalculator);

        $cart->addItem('商品A', 1000, 1);
        $cart->addItem('商品B', 2000, 2);

        $this->assertFalse($cart->isEmpty());
        $this->assertEquals(2, count($cart->getItems()));

        $cart->clear();

        $this->assertTrue($cart->isEmpty());
        $this->assertEquals(0, count($cart->getItems()));
    }

    // ============================================
    // スタブを使ったテスト
    // ============================================

    public function testGetTotalWithStub(): void
    {
        // スタブ: 常に固定値を返すモック
        $priceCalculator = $this->createStub(PriceCalculator::class);

        $priceCalculator
            ->method('calculateTotal')
            ->willReturn(20000.0);

        $priceCalculator
            ->method('calculateTax')
            ->willReturn(2000.0);

        $cart = new ShoppingCart($priceCalculator);
        $cart->addItem('商品A', 10000, 2);

        $this->assertEquals(20000.0, $cart->getSubtotal());
        $this->assertEquals(2000.0, $cart->getTax());
        $this->assertEquals(22000.0, $cart->getTotal());
    }

    // ============================================
    // 統合テスト（実際のPriceCalculatorを使用）
    // ============================================

    public function testIntegrationWithRealCalculator(): void
    {
        // 実際のPriceCalculatorを使用
        $priceCalculator = new class implements PriceCalculator {
            public function calculateTotal(array $items): float
            {
                return array_reduce(
                    $items,
                    fn(float $total, array $item) => $total + ($item['price'] * $item['quantity']),
                    0.0
                );
            }

            public function calculateTax(float $amount): float
            {
                return $amount * 0.1;
            }
        };

        $cart = new ShoppingCart($priceCalculator);
        $cart->addItem('商品A', 1000, 2);
        $cart->addItem('商品B', 3000, 1);

        $this->assertEquals(5000.0, $cart->getSubtotal());
        $this->assertEquals(500.0, $cart->getTax());
        $this->assertEquals(5500.0, $cart->getTotal());
    }
}
