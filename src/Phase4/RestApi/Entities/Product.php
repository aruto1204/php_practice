<?php

declare(strict_types=1);

namespace Phase4\RestApi\Entities;

use InvalidArgumentException;

/**
 * 商品エンティティ
 *
 * REST APIの商品リソースを表現するエンティティクラス
 */
class Product
{
    /**
     * コンストラクタ
     *
     * @param int|null $id 商品ID
     * @param string $name 商品名
     * @param string $description 商品説明
     * @param float $price 価格
     * @param int $stock 在庫数
     * @param string $category カテゴリー
     * @param string|null $imageUrl 画像URL
     * @param bool $isActive 有効フラグ
     * @param string|null $createdAt 作成日時
     * @param string|null $updatedAt 更新日時
     */
    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private float $price,
        private int $stock,
        private string $category,
        private ?string $imageUrl = null,
        private bool $isActive = true,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {
        $this->validate();

        if ($this->createdAt === null) {
            $this->createdAt = date('Y-m-d H:i:s');
        }
        if ($this->updatedAt === null) {
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    /**
     * バリデーション
     */
    private function validate(): void
    {
        // 商品名のバリデーション
        if (strlen(trim($this->name)) === 0) {
            throw new InvalidArgumentException('商品名を指定してください');
        }
        if (strlen($this->name) > 200) {
            throw new InvalidArgumentException('商品名は200文字以下で指定してください');
        }

        // 商品説明のバリデーション
        if (strlen(trim($this->description)) === 0) {
            throw new InvalidArgumentException('商品説明を指定してください');
        }

        // 価格のバリデーション
        if ($this->price < 0) {
            throw new InvalidArgumentException('価格は0以上で指定してください');
        }

        // 在庫数のバリデーション
        if ($this->stock < 0) {
            throw new InvalidArgumentException('在庫数は0以上で指定してください');
        }

        // カテゴリーのバリデーション
        if (strlen(trim($this->category)) === 0) {
            throw new InvalidArgumentException('カテゴリーを指定してください');
        }
    }

    /**
     * ファクトリーメソッド：新規商品の作成
     *
     * @param string $name 商品名
     * @param string $description 商品説明
     * @param float $price 価格
     * @param int $stock 在庫数
     * @param string $category カテゴリー
     * @param string|null $imageUrl 画像URL
     * @return self
     */
    public static function create(
        string $name,
        string $description,
        float $price,
        int $stock,
        string $category,
        ?string $imageUrl = null,
    ): self {
        return new self(null, $name, $description, $price, $stock, $category, $imageUrl);
    }

    /**
     * 商品情報の更新
     *
     * @param string|null $name 商品名
     * @param string|null $description 商品説明
     * @param float|null $price 価格
     * @param int|null $stock 在庫数
     * @param string|null $category カテゴリー
     * @param string|null $imageUrl 画像URL
     * @return void
     */
    public function update(
        ?string $name = null,
        ?string $description = null,
        ?float $price = null,
        ?int $stock = null,
        ?string $category = null,
        ?string $imageUrl = null,
    ): void {
        if ($name !== null) {
            if (strlen(trim($name)) === 0) {
                throw new InvalidArgumentException('商品名を指定してください');
            }
            if (strlen($name) > 200) {
                throw new InvalidArgumentException('商品名は200文字以下で指定してください');
            }
            $this->name = $name;
        }

        if ($description !== null) {
            if (strlen(trim($description)) === 0) {
                throw new InvalidArgumentException('商品説明を指定してください');
            }
            $this->description = $description;
        }

        if ($price !== null) {
            if ($price < 0) {
                throw new InvalidArgumentException('価格は0以上で指定してください');
            }
            $this->price = $price;
        }

        if ($stock !== null) {
            if ($stock < 0) {
                throw new InvalidArgumentException('在庫数は0以上で指定してください');
            }
            $this->stock = $stock;
        }

        if ($category !== null) {
            if (strlen(trim($category)) === 0) {
                throw new InvalidArgumentException('カテゴリーを指定してください');
            }
            $this->category = $category;
        }

        if ($imageUrl !== null) {
            $this->imageUrl = $imageUrl;
        }

        $this->touch();
    }

    /**
     * 在庫の追加
     *
     * @param int $quantity 追加数量
     * @return void
     */
    public function addStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('追加数量は1以上で指定してください');
        }
        $this->stock += $quantity;
        $this->touch();
    }

    /**
     * 在庫の減少（購入時）
     *
     * @param int $quantity 減少数量
     * @return void
     * @throws InvalidArgumentException 在庫不足の場合
     */
    public function reduceStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('減少数量は1以上で指定してください');
        }
        if ($this->stock < $quantity) {
            throw new InvalidArgumentException('在庫が不足しています');
        }
        $this->stock -= $quantity;
        $this->touch();
    }

    /**
     * 在庫があるかチェック
     *
     * @param int $quantity 必要数量
     * @return bool
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * 商品の有効化/無効化
     *
     * @param bool $isActive 有効フラグ
     * @return void
     */
    public function setActiveStatus(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    /**
     * 更新日時の更新
     */
    private function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->category,
            'image_url' => $this->imageUrl,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    // ゲッター

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    // セッター

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
