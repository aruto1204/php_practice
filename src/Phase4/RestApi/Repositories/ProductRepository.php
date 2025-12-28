<?php

declare(strict_types=1);

namespace Phase4\RestApi\Repositories;

use PDO;
use Phase4\RestApi\Entities\Product;

/**
 * 商品リポジトリ
 *
 * 商品エンティティのデータアクセスを担当
 */
class ProductRepository
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * 商品を作成
     *
     * @param Product $product 商品エンティティ
     * @return Product
     */
    public function create(Product $product): Product
    {
        $sql = '
            INSERT INTO products (name, description, price, stock, category, image_url, is_active, created_at, updated_at)
            VALUES (:name, :description, :price, :stock, :category, :image_url, :is_active, :created_at, :updated_at)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'category' => $product->getCategory(),
            'image_url' => $product->getImageUrl(),
            'is_active' => $product->isActive() ? 1 : 0,
            'created_at' => $product->getCreatedAt(),
            'updated_at' => $product->getUpdatedAt(),
        ]);

        $product->setId((int) $this->pdo->lastInsertId());
        return $product;
    }

    /**
     * IDで商品を検索
     *
     * @param int $id 商品ID
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        $sql = 'SELECT * FROM products WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * すべての商品を取得（ページネーション対応）
     *
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @param bool $activeOnly 有効な商品のみ
     * @return Product[]
     */
    public function findAll(int $limit = 20, int $offset = 0, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM products';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch()) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    /**
     * カテゴリーで商品を検索
     *
     * @param string $category カテゴリー
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return Product[]
     */
    public function findByCategory(string $category, int $limit = 20, int $offset = 0): array
    {
        $sql = '
            SELECT * FROM products
            WHERE category = :category AND is_active = 1
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('category', $category);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch()) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    /**
     * キーワードで商品を検索
     *
     * @param string $keyword キーワード
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return Product[]
     */
    public function search(string $keyword, int $limit = 20, int $offset = 0): array
    {
        $sql = '
            SELECT * FROM products
            WHERE (name LIKE :keyword OR description LIKE :keyword) AND is_active = 1
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('keyword', "%{$keyword}%");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch()) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    /**
     * 商品の総数を取得
     *
     * @param bool $activeOnly 有効な商品のみ
     * @return int
     */
    public function count(bool $activeOnly = false): int
    {
        $sql = 'SELECT COUNT(*) FROM products';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }

        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * 商品を更新
     *
     * @param Product $product 商品エンティティ
     * @return Product
     */
    public function update(Product $product): Product
    {
        $sql = '
            UPDATE products
            SET name = :name,
                description = :description,
                price = :price,
                stock = :stock,
                category = :category,
                image_url = :image_url,
                is_active = :is_active,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'category' => $product->getCategory(),
            'image_url' => $product->getImageUrl(),
            'is_active' => $product->isActive() ? 1 : 0,
            'updated_at' => $product->getUpdatedAt(),
        ]);

        return $product;
    }

    /**
     * 商品を削除
     *
     * @param int $id 商品ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM products WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * すべてのカテゴリーを取得
     *
     * @return string[]
     */
    public function getAllCategories(): array
    {
        $sql = 'SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category';
        $stmt = $this->pdo->query($sql);

        $categories = [];
        while ($category = $stmt->fetchColumn()) {
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * データベースの行からProductエンティティを生成
     *
     * @param array<string, mixed> $row
     * @return Product
     */
    private function hydrate(array $row): Product
    {
        $product = new Product(
            id: (int) $row['id'],
            name: $row['name'],
            description: $row['description'],
            price: (float) $row['price'],
            stock: (int) $row['stock'],
            category: $row['category'],
            imageUrl: $row['image_url'],
            isActive: (bool) $row['is_active'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );

        return $product;
    }
}
