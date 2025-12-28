<?php

declare(strict_types=1);

namespace Phase4\RestApi\Entities;

use InvalidArgumentException;

/**
 * 注文アイテムエンティティ
 *
 * 注文に含まれる個々の商品情報を表現するエンティティクラス
 */
class OrderItem
{
    /**
     * コンストラクタ
     *
     * @param int|null $id 注文アイテムID
     * @param int|null $orderId 注文ID
     * @param int $productId 商品ID
     * @param int $quantity 数量
     * @param float $price 単価
     * @param float $subtotal 小計
     */
    public function __construct(
        private ?int $id,
        private ?int $orderId,
        private int $productId,
        private int $quantity,
        private float $price,
        private float $subtotal,
    ) {
        $this->validate();
    }

    /**
     * バリデーション
     */
    private function validate(): void
    {
        // 数量のバリデーション
        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('数量は1以上で指定してください');
        }

        // 単価のバリデーション
        if ($this->price < 0) {
            throw new InvalidArgumentException('単価は0以上で指定してください');
        }

        // 小計のバリデーション
        if ($this->subtotal < 0) {
            throw new InvalidArgumentException('小計は0以上で指定してください');
        }

        // 小計の整合性チェック
        $expectedSubtotal = $this->price * $this->quantity;
        if (abs($this->subtotal - $expectedSubtotal) > 0.01) { // 浮動小数点の誤差を考慮
            throw new InvalidArgumentException('小計が価格×数量と一致しません');
        }
    }

    /**
     * ファクトリーメソッド：新規注文アイテムの作成
     *
     * @param int $productId 商品ID
     * @param int $quantity 数量
     * @param float $price 単価
     * @return self
     */
    public static function create(int $productId, int $quantity, float $price): self
    {
        $subtotal = $price * $quantity;
        return new self(null, null, $productId, $quantity, $price, $subtotal);
    }

    /**
     * Productエンティティから注文アイテムを作成
     *
     * @param Product $product 商品
     * @param int $quantity 数量
     * @return self
     */
    public static function fromProduct(Product $product, int $quantity): self
    {
        if ($product->getId() === null) {
            throw new InvalidArgumentException('商品IDが設定されていません');
        }

        return self::create($product->getId(), $quantity, $product->getPrice());
    }

    /**
     * 数量の変更
     *
     * @param int $quantity 新しい数量
     * @return void
     */
    public function updateQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('数量は1以上で指定してください');
        }

        $this->quantity = $quantity;
        $this->subtotal = $this->price * $this->quantity;
    }

    /**
     * APIレスポンス用の配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
        ];
    }

    // ゲッター

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    // セッター

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }
}
