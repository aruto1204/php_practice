<?php

declare(strict_types=1);

/**
 * Phase 1.5: 配列操作の演習課題
 *
 * このファイルでは、配列操作の実践的な演習を行います。
 */

echo "=== 配列操作の演習課題 ===" . PHP_EOL . PHP_EOL;

// =============================================================================
// 1. 配列の基本操作
// =============================================================================

echo "=== 1. 配列の基本操作 ===" . PHP_EOL;

/**
 * 配列の統計情報を取得する
 *
 * @param array<int|float> $numbers 数値の配列
 * @return array<string, int|float> 統計情報（合計、平均、最大、最小）
 */
function getArrayStats(array $numbers): array
{
    if (count($numbers) === 0) {
        return [
            'count' => 0,
            'sum' => 0,
            'average' => 0,
            'max' => 0,
            'min' => 0,
        ];
    }

    return [
        'count' => count($numbers),
        'sum' => array_sum($numbers),
        'average' => array_sum($numbers) / count($numbers),
        'max' => max($numbers),
        'min' => min($numbers),
    ];
}

$testNumbers = [10, 25, 18, 42, 33, 15, 28];
$stats = getArrayStats($testNumbers);
echo "配列: " . implode(", ", $testNumbers) . PHP_EOL;
echo "要素数: {$stats['count']}" . PHP_EOL;
echo "合計: {$stats['sum']}" . PHP_EOL;
echo "平均: {$stats['average']}" . PHP_EOL;
echo "最大値: {$stats['max']}" . PHP_EOL;
echo "最小値: {$stats['min']}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================
// 2. 配列のフィルタリング
// =============================================================================

echo "=== 2. 配列のフィルタリング ===" . PHP_EOL;

/**
 * 価格範囲で商品をフィルタリングする
 *
 * @param array<int, array<string, mixed>> $products 商品の配列
 * @param int $minPrice 最小価格
 * @param int $maxPrice 最大価格
 * @return array<int, array<string, mixed>> フィルタリングされた商品の配列
 */
function filterByPriceRange(array $products, int $minPrice, int $maxPrice): array
{
    return array_filter(
        $products,
        fn($product) => $product['price'] >= $minPrice && $product['price'] <= $maxPrice
    );
}

$products = [
    ["name" => "ノートPC", "price" => 120000, "category" => "電子機器"],
    ["name" => "マウス", "price" => 2500, "category" => "電子機器"],
    ["name" => "キーボード", "price" => 8000, "category" => "電子機器"],
    ["name" => "モニター", "price" => 45000, "category" => "電子機器"],
    ["name" => "USBケーブル", "price" => 800, "category" => "アクセサリ"],
];

$filtered = filterByPriceRange($products, 1000, 50000);
echo "価格1,000円〜50,000円の商品:" . PHP_EOL;
foreach ($filtered as $product) {
    echo "- {$product['name']}: {$product['price']}円" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 3. 配列の変換（map）
// =============================================================================

echo "=== 3. 配列の変換（map） ===" . PHP_EOL;

/**
 * 商品に消費税を適用する
 *
 * @param array<int, array<string, mixed>> $products 商品の配列
 * @param float $taxRate 消費税率（例: 0.1 = 10%）
 * @return array<int, array<string, mixed>> 税込価格を含む商品の配列
 */
function applyTax(array $products, float $taxRate): array
{
    return array_map(function ($product) use ($taxRate) {
        $product['price_with_tax'] = (int)($product['price'] * (1 + $taxRate));
        return $product;
    }, $products);
}

$productsWithTax = applyTax($products, 0.1);
echo "税込価格:" . PHP_EOL;
foreach ($productsWithTax as $product) {
    echo "- {$product['name']}: {$product['price']}円 → {$product['price_with_tax']}円（税込）" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 4. 配列の集計（reduce）
// =============================================================================

echo "=== 4. 配列の集計（reduce） ===" . PHP_EOL;

/**
 * カテゴリー別の合計金額を計算する
 *
 * @param array<int, array<string, mixed>> $products 商品の配列
 * @return array<string, int> カテゴリー別の合計金額
 */
function getTotalByCategory(array $products): array
{
    return array_reduce($products, function ($carry, $product) {
        $category = $product['category'];
        if (!isset($carry[$category])) {
            $carry[$category] = 0;
        }
        $carry[$category] += $product['price'];
        return $carry;
    }, []);
}

$totalByCategory = getTotalByCategory($products);
echo "カテゴリー別合計:" . PHP_EOL;
foreach ($totalByCategory as $category => $total) {
    echo "- {$category}: {$total}円" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 5. 配列のソート
// =============================================================================

echo "=== 5. 配列のソート ===" . PHP_EOL;

/**
 * 商品を価格で並び替える
 *
 * @param array<int, array<string, mixed>> $products 商品の配列
 * @param bool $ascending 昇順の場合true、降順の場合false
 * @return array<int, array<string, mixed>> 並び替えられた商品の配列
 */
function sortByPrice(array $products, bool $ascending = true): array
{
    $sorted = $products;
    usort($sorted, function ($a, $b) use ($ascending) {
        $comparison = $a['price'] <=> $b['price'];
        return $ascending ? $comparison : -$comparison;
    });
    return $sorted;
}

$sortedAsc = sortByPrice($products, true);
echo "価格の安い順:" . PHP_EOL;
foreach ($sortedAsc as $product) {
    echo "- {$product['name']}: {$product['price']}円" . PHP_EOL;
}

echo PHP_EOL;

$sortedDesc = sortByPrice($products, false);
echo "価格の高い順:" . PHP_EOL;
foreach ($sortedDesc as $product) {
    echo "- {$product['name']}: {$product['price']}円" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 6. 配列の検索
// =============================================================================

echo "=== 6. 配列の検索 ===" . PHP_EOL;

/**
 * 商品名で検索する
 *
 * @param array<int, array<string, mixed>> $products 商品の配列
 * @param string $searchTerm 検索キーワード
 * @return array<int, array<string, mixed>> 検索結果
 */
function searchProducts(array $products, string $searchTerm): array
{
    return array_filter(
        $products,
        fn($product) => str_contains(strtolower($product['name']), strtolower($searchTerm))
    );
}

$searchResults = searchProducts($products, "USB");
echo "「USB」で検索:" . PHP_EOL;
foreach ($searchResults as $product) {
    echo "- {$product['name']}: {$product['price']}円" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 7. 配列のグループ化
// =============================================================================

echo "=== 7. 配列のグループ化 ===" . PHP_EOL;

/**
 * カテゴリー別に商品をグループ化する
 *
 * @param array<int, array<string, mixed>> $products 商品の配列
 * @return array<string, array<int, array<string, mixed>>> カテゴリー別の商品配列
 */
function groupByCategory(array $products): array
{
    $grouped = [];
    foreach ($products as $product) {
        $category = $product['category'];
        if (!isset($grouped[$category])) {
            $grouped[$category] = [];
        }
        $grouped[$category][] = $product;
    }
    return $grouped;
}

$grouped = groupByCategory($products);
echo "カテゴリー別:" . PHP_EOL;
foreach ($grouped as $category => $items) {
    echo "{$category}:" . PHP_EOL;
    foreach ($items as $product) {
        echo "  - {$product['name']}: {$product['price']}円" . PHP_EOL;
    }
}

echo PHP_EOL;

// =============================================================================
// 8. 配列の重複削除
// =============================================================================

echo "=== 8. 配列の重複削除 ===" . PHP_EOL;

/**
 * タグの重複を削除してソートする
 *
 * @param array<int, string> $tags タグの配列
 * @return array<int, string> 重複削除・ソート済みのタグ配列
 */
function uniqueTags(array $tags): array
{
    $unique = array_unique($tags);
    sort($unique);
    return array_values($unique);
}

$tags = ["PHP", "JavaScript", "Python", "PHP", "Java", "JavaScript", "Go"];
$uniqueTagsList = uniqueTags($tags);
echo "元のタグ: " . implode(", ", $tags) . PHP_EOL;
echo "重複削除後: " . implode(", ", $uniqueTagsList) . PHP_EOL;

echo PHP_EOL;

// =============================================================================
// 9. 配列の結合と分割
// =============================================================================

echo "=== 9. 配列の結合と分割 ===" . PHP_EOL;

/**
 * 配列をページネーション用に分割する
 *
 * @param array<mixed> $items アイテムの配列
 * @param int $itemsPerPage 1ページあたりのアイテム数
 * @return array<int, array<mixed>> ページ別のアイテム配列
 */
function paginate(array $items, int $itemsPerPage): array
{
    return array_chunk($items, $itemsPerPage);
}

$allProducts = range(1, 23);  // 1から23までの商品ID
$pages = paginate($allProducts, 10);
echo "全{$allProducts[count($allProducts) - 1]}件の商品を1ページ10件で表示:" . PHP_EOL;
foreach ($pages as $pageNum => $pageItems) {
    $actualPage = $pageNum + 1;
    echo "ページ{$actualPage}: " . implode(", ", $pageItems) . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 10. 配列の深いマージ
// =============================================================================

echo "=== 10. 配列の深いマージ ===" . PHP_EOL;

/**
 * 配列を再帰的にマージする
 *
 * @param array<mixed> $array1 配列1
 * @param array<mixed> $array2 配列2
 * @return array<mixed> マージされた配列
 */
function deepMerge(array $array1, array $array2): array
{
    $merged = $array1;

    foreach ($array2 as $key => $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = deepMerge($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

$config1 = [
    "database" => [
        "host" => "localhost",
        "port" => 3306,
        "username" => "user1",
    ],
    "cache" => [
        "enabled" => true,
    ],
];

$config2 = [
    "database" => [
        "port" => 5432,
        "password" => "secret",
    ],
    "cache" => [
        "ttl" => 3600,
    ],
];

$mergedConfig = deepMerge($config1, $config2);
echo "設定1: " . print_r($config1, true);
echo "設定2: " . print_r($config2, true);
echo "マージ後: " . print_r($mergedConfig, true);

echo PHP_EOL;

// =============================================================================
// 11. 配列の平坦化
// =============================================================================

echo "=== 11. 配列の平坦化 ===" . PHP_EOL;

/**
 * 多次元配列を1次元配列に平坦化する
 *
 * @param array<mixed> $array 多次元配列
 * @return array<mixed> 平坦化された配列
 */
function flatten(array $array): array
{
    $result = [];
    foreach ($array as $item) {
        if (is_array($item)) {
            $result = array_merge($result, flatten($item));
        } else {
            $result[] = $item;
        }
    }
    return $result;
}

$nested = [1, [2, 3, [4, 5]], 6, [7, [8, 9]]];
$flattened = flatten($nested);
echo "ネストされた配列: " . print_r($nested, true);
echo "平坦化後: " . print_r($flattened, true);

echo PHP_EOL;

// =============================================================================
// 12. ショッピングカートシステム
// =============================================================================

echo "=== 12. ショッピングカートシステム ===" . PHP_EOL;

/**
 * ショッピングカートクラス
 */
class ShoppingCart
{
    /**
     * @var array<int, array<string, mixed>> カート内のアイテム
     */
    private array $items = [];

    /**
     * アイテムをカートに追加
     *
     * @param string $name 商品名
     * @param int $price 価格
     * @param int $quantity 数量
     * @return void
     */
    public function addItem(string $name, int $price, int $quantity = 1): void
    {
        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    /**
     * カート内のアイテム一覧を取得
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * 小計を計算
     *
     * @return int
     */
    public function getSubtotal(): int
    {
        return array_reduce(
            $this->items,
            fn($carry, $item) => $carry + ($item['price'] * $item['quantity']),
            0
        );
    }

    /**
     * 消費税を計算
     *
     * @param float $taxRate 消費税率
     * @return int
     */
    public function getTax(float $taxRate = 0.1): int
    {
        return (int)($this->getSubtotal() * $taxRate);
    }

    /**
     * 合計金額を計算
     *
     * @param float $taxRate 消費税率
     * @return int
     */
    public function getTotal(float $taxRate = 0.1): int
    {
        return $this->getSubtotal() + $this->getTax($taxRate);
    }

    /**
     * アイテム数を取得
     *
     * @return int
     */
    public function getItemCount(): int
    {
        return array_reduce(
            $this->items,
            fn($carry, $item) => $carry + $item['quantity'],
            0
        );
    }

    /**
     * カートをクリア
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
    }
}

$cart = new ShoppingCart();
$cart->addItem("ノートPC", 120000, 1);
$cart->addItem("マウス", 2500, 2);
$cart->addItem("キーボード", 8000, 1);

echo "ショッピングカート:" . PHP_EOL;
foreach ($cart->getItems() as $item) {
    $itemTotal = $item['price'] * $item['quantity'];
    echo "- {$item['name']}: {$item['price']}円 × {$item['quantity']} = {$itemTotal}円" . PHP_EOL;
}

echo PHP_EOL;
echo "商品数: {$cart->getItemCount()}点" . PHP_EOL;
echo "小計: " . number_format($cart->getSubtotal()) . "円" . PHP_EOL;
echo "消費税: " . number_format($cart->getTax()) . "円" . PHP_EOL;
echo "合計: " . number_format($cart->getTotal()) . "円" . PHP_EOL;

echo PHP_EOL;

// =============================================================================
// 13. データ集計とレポート
// =============================================================================

echo "=== 13. データ集計とレポート ===" . PHP_EOL;

$salesData = [
    ["product" => "ノートPC", "month" => "1月", "quantity" => 10, "revenue" => 1200000],
    ["product" => "ノートPC", "month" => "2月", "quantity" => 15, "revenue" => 1800000],
    ["product" => "マウス", "month" => "1月", "quantity" => 50, "revenue" => 125000],
    ["product" => "マウス", "month" => "2月", "quantity" => 45, "revenue" => 112500],
    ["product" => "キーボード", "month" => "1月", "quantity" => 30, "revenue" => 240000],
    ["product" => "キーボード", "month" => "2月", "quantity" => 35, "revenue" => 280000],
];

// 商品別の合計売上を計算
$revenueByProduct = [];
foreach ($salesData as $sale) {
    $product = $sale['product'];
    if (!isset($revenueByProduct[$product])) {
        $revenueByProduct[$product] = 0;
    }
    $revenueByProduct[$product] += $sale['revenue'];
}

echo "商品別売上:" . PHP_EOL;
arsort($revenueByProduct);
foreach ($revenueByProduct as $product => $revenue) {
    echo "- {$product}: " . number_format($revenue) . "円" . PHP_EOL;
}

echo PHP_EOL;

// 月別の合計売上を計算
$revenueByMonth = array_reduce($salesData, function ($carry, $sale) {
    $month = $sale['month'];
    if (!isset($carry[$month])) {
        $carry[$month] = 0;
    }
    $carry[$month] += $sale['revenue'];
    return $carry;
}, []);

echo "月別売上:" . PHP_EOL;
foreach ($revenueByMonth as $month => $revenue) {
    echo "- {$month}: " . number_format($revenue) . "円" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// 14. 配列の差分検出
// =============================================================================

echo "=== 14. 配列の差分検出 ===" . PHP_EOL;

/**
 * 2つの配列の差分を検出する
 *
 * @param array<string, mixed> $old 古い配列
 * @param array<string, mixed> $new 新しい配列
 * @return array<string, array<string, mixed>> 差分情報
 */
function detectChanges(array $old, array $new): array
{
    $changes = [
        'added' => [],
        'removed' => [],
        'modified' => [],
    ];

    // 追加されたキー
    $changes['added'] = array_diff_key($new, $old);

    // 削除されたキー
    $changes['removed'] = array_diff_key($old, $new);

    // 変更されたキー
    foreach ($old as $key => $value) {
        if (isset($new[$key]) && $new[$key] !== $value) {
            $changes['modified'][$key] = [
                'old' => $value,
                'new' => $new[$key],
            ];
        }
    }

    return $changes;
}

$oldUser = ["name" => "山田太郎", "age" => 28, "city" => "東京"];
$newUser = ["name" => "山田太郎", "age" => 29, "email" => "yamada@example.com"];

$changes = detectChanges($oldUser, $newUser);
echo "ユーザー情報の変更:" . PHP_EOL;
echo "追加: " . print_r($changes['added'], true);
echo "削除: " . print_r($changes['removed'], true);
echo "変更: " . print_r($changes['modified'], true);

echo PHP_EOL;

// =============================================================================

echo "=== 演習課題完了 ===" . PHP_EOL;
echo "配列操作の実践的な演習が完了しました！" . PHP_EOL;
echo "学習した内容:" . PHP_EOL;
echo "- 配列の統計処理" . PHP_EOL;
echo "- フィルタリング、マッピング、集計" . PHP_EOL;
echo "- ソート、検索、グループ化" . PHP_EOL;
echo "- ページネーション、深いマージ、平坦化" . PHP_EOL;
echo "- ショッピングカートシステムの実装" . PHP_EOL;
echo "- データ集計とレポート" . PHP_EOL;
echo "- 配列の差分検出" . PHP_EOL;
