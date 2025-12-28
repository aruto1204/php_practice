<?php

declare(strict_types=1);

namespace Phase4\RestApi\Entities;

use InvalidArgumentException;

/**
 * 注文エンティティ
 *
 * REST APIの注文リソースを表現するエンティティクラス
 */
class Order
{
    /** @var OrderItem[] */
    private array $items = [];

    /**
     * コンストラクタ
     *
     * @param int|null $id 注文ID
     * @param int $userId ユーザーID
     * @param OrderStatus $status ステータス
     * @param float $totalAmount 合計金額
     * @param string $shippingAddress 配送先住所
     * @param string|null $createdAt 作成日時
     * @param string|null $updatedAt 更新日時
     */
    public function __construct(
        private ?int $id,
        private int $userId,
        private OrderStatus $status,
        private float $totalAmount,
        private string $shippingAddress,
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
        // 合計金額のバリデーション
        if ($this->totalAmount < 0) {
            throw new InvalidArgumentException('合計金額は0以上で指定してください');
        }

        // 配送先住所のバリデーション
        if (strlen(trim($this->shippingAddress)) === 0) {
            throw new InvalidArgumentException('配送先住所を指定してください');
        }
        if (strlen($this->shippingAddress) > 500) {
            throw new InvalidArgumentException('配送先住所は500文字以下で指定してください');
        }
    }

    /**
     * ファクトリーメソッド：新規注文の作成
     *
     * @param int $userId ユーザーID
     * @param string $shippingAddress 配送先住所
     * @param OrderItem[] $items 注文アイテム
     * @return self
     */
    public static function create(int $userId, string $shippingAddress, array $items): self
    {
        if (empty($items)) {
            throw new InvalidArgumentException('注文アイテムが指定されていません');
        }

        // 合計金額を計算
        $totalAmount = array_reduce(
            $items,
            fn(float $sum, OrderItem $item) => $sum + $item->getSubtotal(),
            0.0
        );

        $order = new self(null, $userId, OrderStatus::PENDING, $totalAmount, $shippingAddress);
        $order->items = $items;

        return $order;
    }

    /**
     * ステータスの更新
     *
     * @param OrderStatus $newStatus 新しいステータス
     * @return void
     * @throws InvalidArgumentException 無効な遷移の場合
     */
    public function updateStatus(OrderStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                sprintf(
                    'ステータスを「%s」から「%s」に変更することはできません',
                    $this->status->label(),
                    $newStatus->label()
                )
            );
        }

        $this->status = $newStatus;
        $this->touch();
    }

    /**
     * 注文のキャンセル
     *
     * @return void
     * @throws InvalidArgumentException キャンセル不可の場合
     */
    public function cancel(): void
    {
        if (!$this->status->isCancellable()) {
            throw new InvalidArgumentException(
                sprintf('ステータスが「%s」の注文はキャンセルできません', $this->status->label())
            );
        }

        $this->status = OrderStatus::CANCELLED;
        $this->touch();
    }

    /**
     * 注文アイテムを追加
     *
     * @param OrderItem $item 注文アイテム
     * @return void
     */
    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
        $this->recalculateTotal();
    }

    /**
     * 注文アイテムをセット
     *
     * @param OrderItem[] $items 注文アイテムの配列
     * @return void
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * 合計金額の再計算
     */
    private function recalculateTotal(): void
    {
        $this->totalAmount = array_reduce(
            $this->items,
            fn(float $sum, OrderItem $item) => $sum + $item->getSubtotal(),
            0.0
        );
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
     * @param bool $includeItems アイテム情報を含めるか
     * @return array<string, mixed>
     */
    public function toArray(bool $includeItems = false): array
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->userId,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_amount' => $this->totalAmount,
            'shipping_address' => $this->shippingAddress,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        if ($includeItems) {
            $data['items'] = array_map(fn(OrderItem $item) => $item->toArray(), $this->items);
            $data['items_count'] = count($this->items);
        }

        return $data;
    }

    // ゲッター

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getShippingAddress(): string
    {
        return $this->shippingAddress;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    // セッター

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
