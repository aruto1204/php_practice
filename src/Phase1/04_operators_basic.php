<?php

declare(strict_types=1);

/**
 * Phase 1.2: 演算子の基本学習プログラム
 *
 * このファイルでは、PHPの基本的な演算子について学習します：
 * - 算術演算子
 * - 比較演算子
 * - 論理演算子
 */

echo "=== Phase 1.2: 演算子の基本 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 1. 算術演算子（Arithmetic Operators）
// ============================================================

echo "【1. 算術演算子】" . PHP_EOL;

$a = 10;
$b = 3;

echo "a = {$a}, b = {$b}" . PHP_EOL;
echo "加算 (a + b): " . ($a + $b) . PHP_EOL;      // 13
echo "減算 (a - b): " . ($a - $b) . PHP_EOL;      // 7
echo "乗算 (a * b): " . ($a * $b) . PHP_EOL;      // 30
echo "除算 (a / b): " . ($a / $b) . PHP_EOL;      // 3.333...
echo "剰余 (a % b): " . ($a % $b) . PHP_EOL;      // 1
echo "べき乗 (a ** b): " . ($a ** $b) . PHP_EOL;  // 1000

echo PHP_EOL;

// 整数除算（intdiv関数）
$quotient = intdiv($a, $b);
echo "整数除算 intdiv({$a}, {$b}): {$quotient}" . PHP_EOL;  // 3

echo PHP_EOL;

// 実用例: 商品価格の計算
$productPrice = 1980;      // 商品価格
$taxRate = 0.10;           // 消費税率（10%）
$priceWithTax = $productPrice * (1 + $taxRate);

echo "【実用例: 価格計算】" . PHP_EOL;
echo "税抜価格: ¥{$productPrice}" . PHP_EOL;
echo "税率: " . ($taxRate * 100) . "%" . PHP_EOL;
echo "税込価格: ¥" . number_format($priceWithTax) . PHP_EOL;

echo PHP_EOL;

// 実用例: 割り勘計算
$totalAmount = 12500;      // 合計金額
$numberOfPeople = 4;       // 人数
$perPersonAmount = intdiv($totalAmount, $numberOfPeople);
$remainder = $totalAmount % $numberOfPeople;

echo "【実用例: 割り勘計算】" . PHP_EOL;
echo "合計金額: ¥{$totalAmount}" . PHP_EOL;
echo "人数: {$numberOfPeople}人" . PHP_EOL;
echo "1人あたり: ¥{$perPersonAmount}" . PHP_EOL;
echo "余り: ¥{$remainder}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 2. インクリメント・デクリメント演算子
// ============================================================

echo "【2. インクリメント・デクリメント演算子】" . PHP_EOL;

$counter = 5;

echo "初期値: counter = {$counter}" . PHP_EOL;
echo "後置インクリメント counter++: " . ($counter++) . " → counter = {$counter}" . PHP_EOL;
// 5を表示してから6になる

$counter = 5;
echo "前置インクリメント ++counter: " . (++$counter) . " → counter = {$counter}" . PHP_EOL;
// 6になってから6を表示

$counter = 5;
echo "後置デクリメント counter--: " . ($counter--) . " → counter = {$counter}" . PHP_EOL;
// 5を表示してから4になる

$counter = 5;
echo "前置デクリメント --counter: " . (--$counter) . " → counter = {$counter}" . PHP_EOL;
// 4になってから4を表示

echo PHP_EOL;

// ============================================================
// 3. 比較演算子（Comparison Operators）
// ============================================================

echo "【3. 比較演算子】" . PHP_EOL;

$x = 10;
$y = 20;
$z = "10";  // 文字列の"10"

echo "x = {$x} (int), y = {$y} (int), z = \"{$z}\" (string)" . PHP_EOL . PHP_EOL;

// 等価演算子
echo "等価 (x == z): " . var_export($x == $z, true) . PHP_EOL;    // true（型は無視）
echo "厳密等価 (x === z): " . var_export($x === $z, true) . PHP_EOL;  // false（型も比較）

echo PHP_EOL;

// 不等価演算子
echo "不等価 (x != y): " . var_export($x != $y, true) . PHP_EOL;      // true
echo "厳密不等価 (x !== z): " . var_export($x !== $z, true) . PHP_EOL;  // true

echo PHP_EOL;

// 大小比較
echo "より小さい (x < y): " . var_export($x < $y, true) . PHP_EOL;        // true
echo "以下 (x <= y): " . var_export($x <= $y, true) . PHP_EOL;            // true
echo "より大きい (x > y): " . var_export($x > $y, true) . PHP_EOL;        // false
echo "以上 (x >= y): " . var_export($x >= $y, true) . PHP_EOL;            // false

echo PHP_EOL;

// 宇宙船演算子（Spaceship Operator - PHP 7.0+）
echo "宇宙船演算子 (x <=> y): " . ($x <=> $y) . PHP_EOL;  // -1（xがyより小さい）
echo "宇宙船演算子 (y <=> x): " . ($y <=> $x) . PHP_EOL;  //  1（yがxより大きい）
echo "宇宙船演算子 (x <=> 10): " . ($x <=> 10) . PHP_EOL; //  0（等しい）

echo PHP_EOL;

// 重要: == と === の違い
echo "【重要: == と === の違い】" . PHP_EOL;
$value1 = 0;
$value2 = false;
$value3 = "";
$value4 = null;

echo "0 == false: " . var_export($value1 == $value2, true) . PHP_EOL;      // true
echo "0 === false: " . var_export($value1 === $value2, true) . PHP_EOL;    // false
echo "\"\" == false: " . var_export($value3 == $value2, true) . PHP_EOL;   // true
echo "\"\" === false: " . var_export($value3 === $value2, true) . PHP_EOL; // false
echo "null == false: " . var_export($value4 == $value2, true) . PHP_EOL;   // true
echo "null === false: " . var_export($value4 === $value2, true) . PHP_EOL; // false

echo PHP_EOL;
echo "💡 ベストプラクティス: 常に === と !== を使用することを推奨" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 4. 論理演算子（Logical Operators）
// ============================================================

echo "【4. 論理演算子】" . PHP_EOL;

$isLoggedIn = true;
$isAdmin = false;
$age = 25;
$hasPermission = true;

echo "isLoggedIn = " . var_export($isLoggedIn, true) . PHP_EOL;
echo "isAdmin = " . var_export($isAdmin, true) . PHP_EOL;
echo "age = {$age}" . PHP_EOL;
echo "hasPermission = " . var_export($hasPermission, true) . PHP_EOL . PHP_EOL;

// AND演算子
echo "論理AND (isLoggedIn && isAdmin): " . var_export($isLoggedIn && $isAdmin, true) . PHP_EOL;  // false
echo "論理AND (isLoggedIn && hasPermission): " . var_export($isLoggedIn && $hasPermission, true) . PHP_EOL;  // true

echo PHP_EOL;

// OR演算子
echo "論理OR (isLoggedIn || isAdmin): " . var_export($isLoggedIn || $isAdmin, true) . PHP_EOL;  // true
echo "論理OR (isAdmin || hasPermission): " . var_export($isAdmin || $hasPermission, true) . PHP_EOL;  // true

echo PHP_EOL;

// NOT演算子
echo "論理NOT (!isAdmin): " . var_export(!$isAdmin, true) . PHP_EOL;  // true
echo "論理NOT (!isLoggedIn): " . var_export(!$isLoggedIn, true) . PHP_EOL;  // false

echo PHP_EOL;

// 複合条件
$canEdit = $isLoggedIn && ($isAdmin || $hasPermission);
echo "複合条件 (isLoggedIn && (isAdmin || hasPermission)): " . var_export($canEdit, true) . PHP_EOL;

$isAdult = $age >= 18;
$canAccess = $isLoggedIn && $isAdult && $hasPermission;
echo "複合条件 (isLoggedIn && age >= 18 && hasPermission): " . var_export($canAccess, true) . PHP_EOL;

echo PHP_EOL;

// 短絡評価（Short-circuit Evaluation）
echo "【短絡評価】" . PHP_EOL;
echo "論理ANDは最初のfalseで評価を停止" . PHP_EOL;
echo "論理ORは最初のtrueで評価を停止" . PHP_EOL;

/**
 * 短絡評価を利用した関数実行
 *
 * @param string $message メッセージ
 * @return bool 常にtrueを返す
 */
function logMessage(string $message): bool
{
    echo "  → ログ出力: {$message}" . PHP_EOL;
    return true;
}

echo PHP_EOL;
echo "例1: false && logMessage('test')" . PHP_EOL;
$result = false && logMessage('test');  // logMessage()は実行されない

echo PHP_EOL;
echo "例2: true && logMessage('test')" . PHP_EOL;
$result = true && logMessage('test');   // logMessage()が実行される

echo PHP_EOL;
echo "例3: true || logMessage('test')" . PHP_EOL;
$result = true || logMessage('test');   // logMessage()は実行されない

echo PHP_EOL;
echo "例4: false || logMessage('test')" . PHP_EOL;
$result = false || logMessage('test');  // logMessage()が実行される

echo PHP_EOL;

// ============================================================
// 5. 文字列連結演算子
// ============================================================

echo "【5. 文字列連結演算子】" . PHP_EOL;

$firstName = "山田";
$lastName = "太郎";

// ドット演算子（.）による連結
$fullName = $lastName . " " . $firstName;
echo "フルネーム: {$fullName}" . PHP_EOL;

// 連結代入演算子（.=）
$message = "こんにちは、";
$message .= $fullName;
$message .= "さん";
echo $message . PHP_EOL;

echo PHP_EOL;

// 文字列展開（推奨）
$greeting = "こんにちは、{$fullName}さん";
echo $greeting . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 6. 三項演算子（Ternary Operator）
// ============================================================

echo "【6. 三項演算子】" . PHP_EOL;

$score = 75;
$result = $score >= 60 ? "合格" : "不合格";
echo "点数: {$score} → {$result}" . PHP_EOL;

// ネストした三項演算子（可読性が低いため非推奨）
$grade = $score >= 80 ? "優" : ($score >= 70 ? "良" : ($score >= 60 ? "可" : "不可"));
echo "評価: {$grade}" . PHP_EOL;

echo PHP_EOL;
echo "💡 ヒント: 複雑な条件分岐はif-elseやmatch式を使用することを推奨" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 7. 実用的な演算子の組み合わせ例
// ============================================================

echo "【7. 実用的な演算子の組み合わせ例】" . PHP_EOL . PHP_EOL;

/**
 * BMI計算と判定
 *
 * @param float $weight 体重（kg）
 * @param float $height 身長（cm）
 * @return array{bmi: float, category: string}
 */
function calculateBMI(float $weight, float $height): array
{
    // BMI = 体重(kg) / (身長(m) ** 2)
    $heightInMeters = $height / 100;
    $bmi = $weight / ($heightInMeters ** 2);

    // 判定ロジック
    $category = match (true) {
        $bmi < 18.5 => "低体重",
        $bmi < 25.0 => "普通体重",
        $bmi < 30.0 => "肥満（1度）",
        $bmi < 35.0 => "肥満（2度）",
        default => "肥満（3度以上）",
    };

    return [
        'bmi' => round($bmi, 2),
        'category' => $category,
    ];
}

$result = calculateBMI(70, 175);
echo "身長: 175cm, 体重: 70kg" . PHP_EOL;
echo "BMI: {$result['bmi']}" . PHP_EOL;
echo "判定: {$result['category']}" . PHP_EOL;

echo PHP_EOL;

/**
 * 割引価格を計算
 *
 * @param int $price 元の価格
 * @param int $discountPercent 割引率（%）
 * @return int 割引後の価格
 */
function calculateDiscountedPrice(int $price, int $discountPercent): int
{
    $discountAmount = ($price * $discountPercent) / 100;
    return $price - (int)$discountAmount;
}

$originalPrice = 10000;
$discountRate = 20;
$finalPrice = calculateDiscountedPrice($originalPrice, $discountRate);

echo "元の価格: ¥" . number_format($originalPrice) . PHP_EOL;
echo "割引率: {$discountRate}%" . PHP_EOL;
echo "割引後: ¥" . number_format($finalPrice) . PHP_EOL;

echo PHP_EOL;

/**
 * 年齢から世代を判定
 *
 * @param int $birthYear 生まれ年
 * @return string 世代名
 */
function determineGeneration(int $birthYear): string
{
    return match (true) {
        $birthYear >= 2013 => "α世代（Alpha Generation）",
        $birthYear >= 1997 => "Z世代（Generation Z）",
        $birthYear >= 1981 => "ミレニアル世代（Millennials）",
        $birthYear >= 1965 => "X世代（Generation X）",
        $birthYear >= 1946 => "ベビーブーマー世代",
        default => "沈黙の世代",
    };
}

$birthYear = 1995;
$currentYear = 2025;
$age = $currentYear - $birthYear;
$generation = determineGeneration($birthYear);

echo "生まれ年: {$birthYear}年" . PHP_EOL;
echo "年齢: {$age}歳" . PHP_EOL;
echo "世代: {$generation}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 学習のポイント
// ============================================================

echo "【学習のポイント】" . PHP_EOL;
echo "1. 算術演算子: +, -, *, /, %, ** を理解する" . PHP_EOL;
echo "2. 比較演算子: 常に === と !== を使用する（型安全性）" . PHP_EOL;
echo "3. 論理演算子: &&, ||, ! を使った条件組み合わせ" . PHP_EOL;
echo "4. 短絡評価: パフォーマンスと安全性に影響" . PHP_EOL;
echo "5. 演算子の優先順位を理解し、必要に応じて括弧を使う" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.2: 演算子の基本 完了 ===" . PHP_EOL;
