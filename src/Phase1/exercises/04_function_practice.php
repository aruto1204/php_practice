<?php

declare(strict_types=1);

/**
 * Phase 1.4: 関数の演習課題
 *
 * このファイルでは、関数を使った実践的な演習を行います：
 * - 演習1: 文字列操作関数
 * - 演習2: 数学計算関数
 * - 演習3: データ変換関数
 * - 演習4: バリデーション関数
 * - 演習5: ユーティリティ関数
 */

echo "=== Phase 1.4: 関数の演習課題 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 演習1: 文字列操作関数
// ============================================================

echo "【演習1: 文字列操作関数】" . PHP_EOL . PHP_EOL;

/**
 * 文字列を逆順にする
 *
 * @param string $text 文字列
 * @return string 逆順の文字列
 */
function reverseString(string $text): string
{
    return strrev($text);
}

/**
 * 文字列の最初の文字を大文字にする
 *
 * @param string $text 文字列
 * @return string 最初が大文字の文字列
 */
function capitalizeFirst(string $text): string
{
    return ucfirst($text);
}

/**
 * 文字列から特定の文字を削除
 *
 * @param string $text 文字列
 * @param string $char 削除する文字
 * @return string 削除後の文字列
 */
function removeChar(string $text, string $char): string
{
    return str_replace($char, '', $text);
}

/**
 * 文字列の単語数をカウント
 *
 * @param string $text 文字列
 * @return int 単語数
 */
function countWords(string $text): int
{
    return str_word_count($text);
}

echo "文字列操作のテスト:" . PHP_EOL;
echo "逆順: " . reverseString("Hello") . PHP_EOL;
echo "最初を大文字: " . capitalizeFirst("hello world") . PHP_EOL;
echo "文字削除: " . removeChar("Hello World", "o") . PHP_EOL;
echo "単語数: " . countWords("Hello World from PHP") . " words" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 演習2: 数学計算関数
// ============================================================

echo "【演習2: 数学計算関数】" . PHP_EOL . PHP_EOL;

/**
 * 階乗を計算
 *
 * @param int $n 数値
 * @return int 階乗
 */
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }
    return $n * factorial($n - 1);
}

/**
 * 最大公約数を計算（ユークリッドの互除法）
 *
 * @param int $a 1つ目の数値
 * @param int $b 2つ目の数値
 * @return int 最大公約数
 */
function gcd(int $a, int $b): int
{
    if ($b === 0) {
        return $a;
    }
    return gcd($b, $a % $b);
}

/**
 * 最小公倍数を計算
 *
 * @param int $a 1つ目の数値
 * @param int $b 2つ目の数値
 * @return int 最小公倍数
 */
function lcm(int $a, int $b): int
{
    return ($a * $b) / gcd($a, $b);
}

/**
 * 平均値を計算
 *
 * @param float ...$numbers 数値（可変長引数）
 * @return float 平均値
 */
function average(float ...$numbers): float
{
    if (count($numbers) === 0) {
        return 0.0;
    }

    $sum = array_sum($numbers);
    return $sum / count($numbers);
}

echo "数学計算のテスト:" . PHP_EOL;
echo "5の階乗: " . factorial(5) . PHP_EOL;
echo "gcd(48, 18) = " . gcd(48, 18) . PHP_EOL;
echo "lcm(12, 18) = " . lcm(12, 18) . PHP_EOL;
echo "average(10, 20, 30, 40, 50) = " . average(10, 20, 30, 40, 50) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 演習3: データ変換関数
// ============================================================

echo "【演習3: データ変換関数】" . PHP_EOL . PHP_EOL;

/**
 * 摂氏を華氏に変換
 *
 * @param float $celsius 摂氏温度
 * @return float 華氏温度
 */
function celsiusToFahrenheit(float $celsius): float
{
    return ($celsius * 9 / 5) + 32;
}

/**
 * 華氏を摂氏に変換
 *
 * @param float $fahrenheit 華氏温度
 * @return float 摂氏温度
 */
function fahrenheitToCelsius(float $fahrenheit): float
{
    return ($fahrenheit - 32) * 5 / 9;
}

/**
 * バイト数を人間が読みやすい形式に変換
 *
 * @param int $bytes バイト数
 * @param int $precision 小数点以下の桁数
 * @return string フォーマット済み文字列
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * 秒数を時間:分:秒の形式に変換
 *
 * @param int $seconds 秒数
 * @return string 時間:分:秒
 */
function secondsToTime(int $seconds): string
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

echo "データ変換のテスト:" . PHP_EOL;
echo "25°C = " . round(celsiusToFahrenheit(25), 2) . "°F" . PHP_EOL;
echo "77°F = " . round(fahrenheitToCelsius(77), 2) . "°C" . PHP_EOL;
echo formatBytes(1536) . PHP_EOL;
echo formatBytes(1048576) . PHP_EOL;
echo formatBytes(1073741824) . PHP_EOL;
echo "3665秒 = " . secondsToTime(3665) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 演習4: バリデーション関数
// ============================================================

echo "【演習4: バリデーション関数】" . PHP_EOL . PHP_EOL;

/**
 * 年齢が有効範囲内かチェック
 *
 * @param int $age 年齢
 * @param int $min 最小値
 * @param int $max 最大値
 * @return bool 有効ならtrue
 */
function isValidAge(int $age, int $min = 0, int $max = 120): bool
{
    return $age >= $min && $age <= $max;
}

/**
 * 電話番号の形式をチェック（日本の固定電話）
 *
 * @param string $phone 電話番号
 * @return bool 有効ならtrue
 */
function isValidJapanPhone(string $phone): bool
{
    // 03-1234-5678 や 0312345678 の形式
    $pattern = '/^0\d{1,4}-?\d{1,4}-?\d{4}$/';
    return preg_match($pattern, $phone) === 1;
}

/**
 * URLの形式をチェック
 *
 * @param string $url URL
 * @return bool 有効ならtrue
 */
function isValidUrl(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * 日付の形式をチェック（YYYY-MM-DD）
 *
 * @param string $date 日付文字列
 * @return bool 有効ならtrue
 */
function isValidDate(string $date): bool
{
    $parts = explode('-', $date);

    if (count($parts) !== 3) {
        return false;
    }

    [$year, $month, $day] = $parts;

    return checkdate((int)$month, (int)$day, (int)$year);
}

echo "バリデーションのテスト:" . PHP_EOL;
echo "年齢25: " . (isValidAge(25) ? '✅' : '❌') . PHP_EOL;
echo "年齢150: " . (isValidAge(150) ? '✅' : '❌') . PHP_EOL;
echo "電話番号03-1234-5678: " . (isValidJapanPhone('03-1234-5678') ? '✅' : '❌') . PHP_EOL;
echo "URL https://example.com: " . (isValidUrl('https://example.com') ? '✅' : '❌') . PHP_EOL;
echo "日付2025-12-25: " . (isValidDate('2025-12-25') ? '✅' : '❌') . PHP_EOL;
echo "日付2025-13-45: " . (isValidDate('2025-13-45') ? '✅' : '❌') . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 演習5: ユーティリティ関数
// ============================================================

echo "【演習5: ユーティリティ関数】" . PHP_EOL . PHP_EOL;

/**
 * 配列から重複を削除
 *
 * @param array<mixed> $array 配列
 * @return array<mixed> 重複削除後の配列
 */
function removeDuplicates(array $array): array
{
    return array_values(array_unique($array));
}

/**
 * 配列をシャッフル（元の配列は変更しない）
 *
 * @param array<mixed> $array 配列
 * @return array<mixed> シャッフル後の配列
 */
function shuffleArray(array $array): array
{
    $shuffled = $array;
    shuffle($shuffled);
    return $shuffled;
}

/**
 * ランダムな文字列を生成
 *
 * @param int $length 長さ
 * @param string $characters 使用する文字セット
 * @return string ランダム文字列
 */
function generateRandomString(
    int $length = 10,
    string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

/**
 * 配列を指定サイズのチャンクに分割
 *
 * @param array<mixed> $array 配列
 * @param int $size チャンクサイズ
 * @return array<array<mixed>> チャンク配列
 */
function chunkArray(array $array, int $size): array
{
    return array_chunk($array, $size);
}

echo "ユーティリティ関数のテスト:" . PHP_EOL;

$numbers = [1, 2, 3, 2, 4, 3, 5];
echo "重複削除: " . implode(', ', removeDuplicates($numbers)) . PHP_EOL;

$items = ['A', 'B', 'C', 'D', 'E'];
echo "シャッフル: " . implode(', ', shuffleArray($items)) . PHP_EOL;

echo "ランダム文字列（8文字）: " . generateRandomString(8) . PHP_EOL;
echo "ランダム文字列（12文字、数字のみ）: " . generateRandomString(12, '0123456789') . PHP_EOL;

$data = range(1, 10);
$chunks = chunkArray($data, 3);
echo "チャンク分割（サイズ3）:" . PHP_EOL;
foreach ($chunks as $i => $chunk) {
    echo "  チャンク" . ($i + 1) . ": " . implode(', ', $chunk) . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// ボーナス演習: 複雑な関数の組み合わせ
// ============================================================

echo "【ボーナス演習: ショッピングカート】" . PHP_EOL . PHP_EOL;

/**
 * 商品アイテムを作成
 *
 * @param string $name 商品名
 * @param int $price 価格
 * @param int $quantity 数量
 * @return array<string, mixed> 商品アイテム
 */
function createCartItem(string $name, int $price, int $quantity = 1): array
{
    return [
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'subtotal' => $price * $quantity,
    ];
}

/**
 * カートの合計を計算
 *
 * @param array<array<string, mixed>> $items カートアイテム
 * @param int $discountPercent 割引率（%）
 * @param float $taxRate 税率
 * @return array<string, mixed> 合計情報
 */
function calculateCartTotal(array $items, int $discountPercent = 0, float $taxRate = 0.10): array
{
    // 小計
    $subtotal = array_reduce(
        $items,
        fn($carry, $item) => $carry + $item['subtotal'],
        0
    );

    // 割引額
    $discountAmount = (int)(($subtotal * $discountPercent) / 100);

    // 割引後の金額
    $afterDiscount = $subtotal - $discountAmount;

    // 税額
    $taxAmount = (int)($afterDiscount * $taxRate);

    // 最終合計
    $total = $afterDiscount + $taxAmount;

    return [
        'subtotal' => $subtotal,
        'discount_percent' => $discountPercent,
        'discount_amount' => $discountAmount,
        'after_discount' => $afterDiscount,
        'tax_rate' => $taxRate * 100,
        'tax_amount' => $taxAmount,
        'total' => $total,
    ];
}

/**
 * カート情報を表示
 *
 * @param array<array<string, mixed>> $items カートアイテム
 * @param array<string, mixed> $totals 合計情報
 * @return void
 */
function displayCart(array $items, array $totals): void
{
    echo "【カート内容】" . PHP_EOL;
    foreach ($items as $item) {
        echo "  {$item['name']} x {$item['quantity']} = ¥" . number_format($item['subtotal']) . PHP_EOL;
    }

    echo PHP_EOL . "【会計】" . PHP_EOL;
    echo "小計: ¥" . number_format($totals['subtotal']) . PHP_EOL;

    if ($totals['discount_amount'] > 0) {
        echo "割引（{$totals['discount_percent']}%）: -¥" . number_format($totals['discount_amount']) . PHP_EOL;
        echo "割引後: ¥" . number_format($totals['after_discount']) . PHP_EOL;
    }

    echo "消費税（{$totals['tax_rate']}%）: ¥" . number_format($totals['tax_amount']) . PHP_EOL;
    echo "合計: ¥" . number_format($totals['total']) . PHP_EOL;
}

// ショッピングカートのシミュレーション
$cart = [
    createCartItem('ノートPC', 98000, 1),
    createCartItem('マウス', 1500, 2),
    createCartItem('キーボード', 8000, 1),
];

$totals = calculateCartTotal($cart, discountPercent: 15);
displayCart($cart, $totals);

echo PHP_EOL;

// ============================================================
// 学習のまとめ
// ============================================================

echo "【学習のまとめ】" . PHP_EOL;
echo "演習1: 文字列操作 - 実用的な文字列処理関数" . PHP_EOL;
echo "演習2: 数学計算 - 再帰関数、可変長引数の活用" . PHP_EOL;
echo "演習3: データ変換 - 様々な形式への変換" . PHP_EOL;
echo "演習4: バリデーション - 入力チェック関数" . PHP_EOL;
echo "演習5: ユーティリティ - 汎用的なヘルパー関数" . PHP_EOL;
echo "ボーナス: ショッピングカート - 複雑なロジックの関数化と組み合わせ" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.4: 関数の演習課題 完了 ===" . PHP_EOL;
