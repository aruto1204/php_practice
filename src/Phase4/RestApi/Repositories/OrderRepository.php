<?php

declare(strict_types=1);

namespace Phase4\RestApi\Repositories;

use PDO;
use Phase4\RestApi\Entities\Order;
use Phase4\RestApi\Entities\OrderItem;
use Phase4\RestApi\Entities\OrderStatus;

/**
 * 注文リポジトリ
 *
 * 注文エンティティのデータアクセスを担当
 */
class OrderRepository
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * 注文を作成（トランザクション内で注文アイテムも作成）
     *
     * @param Order $order 注文エンティティ
     * @return Order
     */
    public function create(Order $order): Order
    {
        $this->pdo->beginTransaction();

        try {
            // 注文を作成
            $sql = '
                INSERT INTO orders (user_id, status, total_amount, shipping_address, created_at, updated_at)
                VALUES (:user_id, :status, :total_amount, :shipping_address, :created_at, :updated_at)
            ';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $order->getUserId(),
                'status' => $order->getStatus()->value,
                'total_amount' => $order->getTotalAmount(),
                'shipping_address' => $order->getShippingAddress(),
                'created_at' => $order->getCreatedAt(),
                'updated_at' => $order->getUpdatedAt(),
            ]);

            $orderId = (int) $this->pdo->lastInsertId();
            $order->setId($orderId);

            // 注文アイテムを作成
            foreach ($order->getItems() as $item) {
                $this->createOrderItem($orderId, $item);
            }

            $this->pdo->commit();
            return $order;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * 注文アイテムを作成
     *
     * @param int $orderId 注文ID
     * @param OrderItem $item 注文アイテム
     * @return void
     */
    private function createOrderItem(int $orderId, OrderItem $item): void
    {
        $sql = '
            INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
            VALUES (:order_id, :product_id, :quantity, :price, :subtotal)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
            'price' => $item->getPrice(),
            'subtotal' => $item->getSubtotal(),
        ]);

        $item->setId((int) $this->pdo->lastInsertId());
        $item->setOrderId($orderId);
    }

    /**
     * IDで注文を検索（注文アイテムも含む）
     *
     * @param int $id 注文ID
     * @return Order|null
     */
    public function findById(int $id): ?Order
    {
        $sql = 'SELECT * FROM orders WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $order = $this->hydrate($row);

        // 注文アイテムを取得
        $items = $this->findOrderItems($id);
        $order->setItems($items);

        return $order;
    }

    /**
     * 注文IDで注文アイテムを検索
     *
     * @param int $orderId 注文ID
     * @return OrderItem[]
     */
    private function findOrderItems(int $orderId): array
    {
        $sql = 'SELECT * FROM order_items WHERE order_id = :order_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);

        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = $this->hydrateOrderItem($row);
        }

        return $items;
    }

    /**
     * ユーザーIDで注文を検索
     *
     * @param int $userId ユーザーID
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return Order[]
     */
    public function findByUserId(int $userId, int $limit = 20, int $offset = 0): array
    {
        $sql = '
            SELECT * FROM orders
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $orders = [];
        while ($row = $stmt->fetch()) {
            $order = $this->hydrate($row);
            $order->setItems($this->findOrderItems($order->getId()));
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * ステータスで注文を検索
     *
     * @param OrderStatus $status ステータス
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return Order[]
     */
    public function findByStatus(OrderStatus $status, int $limit = 20, int $offset = 0): array
    {
        $sql = '
            SELECT * FROM orders
            WHERE status = :status
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('status', $status->value);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $orders = [];
        while ($row = $stmt->fetch()) {
            $order = $this->hydrate($row);
            $order->setItems($this->findOrderItems($order->getId()));
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * すべての注文を取得（管理者用）
     *
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return Order[]
     */
    public function findAll(int $limit = 20, int $offset = 0): array
    {
        $sql = '
            SELECT * FROM orders
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $orders = [];
        while ($row = $stmt->fetch()) {
            $order = $this->hydrate($row);
            $order->setItems($this->findOrderItems($order->getId()));
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * 注文の総数を取得
     *
     * @param int|null $userId ユーザーID（指定した場合はそのユーザーの注文数）
     * @return int
     */
    public function count(?int $userId = null): int
    {
        if ($userId === null) {
            $sql = 'SELECT COUNT(*) FROM orders';
            $stmt = $this->pdo->query($sql);
        } else {
            $sql = 'SELECT COUNT(*) FROM orders WHERE user_id = :user_id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * 注文を更新
     *
     * @param Order $order 注文エンティティ
     * @return Order
     */
    public function update(Order $order): Order
    {
        $sql = '
            UPDATE orders
            SET status = :status,
                total_amount = :total_amount,
                shipping_address = :shipping_address,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $order->getId(),
            'status' => $order->getStatus()->value,
            'total_amount' => $order->getTotalAmount(),
            'shipping_address' => $order->getShippingAddress(),
            'updated_at' => $order->getUpdatedAt(),
        ]);

        return $order;
    }

    /**
     * 注文を削除（注文アイテムもカスケード削除される）
     *
     * @param int $id 注文ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM orders WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * データベースの行からOrderエンティティを生成
     *
     * @param array<string, mixed> $row
     * @return Order
     */
    private function hydrate(array $row): Order
    {
        $order = new Order(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            status: OrderStatus::from($row['status']),
            totalAmount: (float) $row['total_amount'],
            shippingAddress: $row['shipping_address'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );

        return $order;
    }

    /**
     * データベースの行からOrderItemエンティティを生成
     *
     * @param array<string, mixed> $row
     * @return OrderItem
     */
    private function hydrateOrderItem(array $row): OrderItem
    {
        $item = new OrderItem(
            id: (int) $row['id'],
            orderId: (int) $row['order_id'],
            productId: (int) $row['product_id'],
            quantity: (int) $row['quantity'],
            price: (float) $row['price'],
            subtotal: (float) $row['subtotal'],
        );

        return $item;
    }
}
