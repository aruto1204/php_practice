<?php

declare(strict_types=1);

/**
 * Phase 1.1: 型キャストと型変換の学習プログラム
 *
 * このプログラムでは以下を学習します:
 * - 明示的な型キャスト
 * - 暗黙的な型変換
 * - 厳格な型宣言の効果
 * - 型変換関数
 */

echo "==================================" . PHP_EOL;
echo "  Phase 1.1: 型キャストと型変換" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 1. 明示的な型キャスト
// ============================================

echo "【1. 明示的な型キャスト】" . PHP_EOL;
echo "---" . PHP_EOL;

// 文字列から整数へ
$stringNumber = "123";
$intNumber = (int) $stringNumber;
echo "文字列 '{$stringNumber}' → 整数 {$intNumber}" . PHP_EOL;
var_dump($stringNumber, $intNumber);
echo PHP_EOL;

// 文字列から浮動小数点へ
$stringFloat = "123.45";
$floatNumber = (float) $stringFloat;
echo "文字列 '{$stringFloat}' → 浮動小数 {$floatNumber}" . PHP_EOL;
var_dump($stringFloat, $floatNumber);
echo PHP_EOL;

// 整数から文字列へ
$number = 999;
$stringFromNumber = (string) $number;
echo "整数 {$number} → 文字列 '{$stringFromNumber}'" . PHP_EOL;
var_dump($number, $stringFromNumber);
echo PHP_EOL;

// 浮動小数から整数へ（小数部分は切り捨て）
$decimal = 123.99;
$intFromDecimal = (int) $decimal;
echo "浮動小数 {$decimal} → 整数 {$intFromDecimal} （小数部分は切り捨て）" . PHP_EOL;
var_dump($decimal, $intFromDecimal);
echo PHP_EOL;

// 真偽値への型キャスト
$zero = 0;
$one = 1;
$emptyString = "";
$nonEmptyString = "hello";
$emptyArray = [];
$nonEmptyArray = [1, 2, 3];

echo "真偽値への型キャスト:" . PHP_EOL;
echo "  0 → bool: " . ((bool) $zero ? "true" : "false") . PHP_EOL;
echo "  1 → bool: " . ((bool) $one ? "true" : "false") . PHP_EOL;
echo "  '' → bool: " . ((bool) $emptyString ? "true" : "false") . PHP_EOL;
echo "  'hello' → bool: " . ((bool) $nonEmptyString ? "true" : "false") . PHP_EOL;
echo "  [] → bool: " . ((bool) $emptyArray ? "true" : "false") . PHP_EOL;
echo "  [1,2,3] → bool: " . ((bool) $nonEmptyArray ? "true" : "false") . PHP_EOL;
echo PHP_EOL;

// ============================================
// 2. 型変換関数
// ============================================

echo "【2. 型変換関数】" . PHP_EOL;
echo "---" . PHP_EOL;

$value = "456";

// intval() - 整数に変換
$intValue = intval($value);
echo "intval('{$value}') = {$intValue}" . PHP_EOL;

// floatval() - 浮動小数点に変換
$floatValue = floatval($value);
echo "floatval('{$value}') = {$floatValue}" . PHP_EOL;

// strval() - 文字列に変換
$num = 789;
$strValue = strval($num);
echo "strval({$num}) = '{$strValue}'" . PHP_EOL;

// boolval() - 真偽値に変換
$nonZero = 1;
$boolValue = boolval($nonZero);
echo "boolval({$nonZero}) = " . ($boolValue ? "true" : "false") . PHP_EOL;
echo PHP_EOL;

// ============================================
// 3. 文字列から数値への変換（注意点）
// ============================================

echo "【3. 文字列から数値への変換（注意点）】" . PHP_EOL;
echo "---" . PHP_EOL;

// 数値で始まる文字列
$str1 = "123abc";
$num1 = (int) $str1;
echo "'{$str1}' → {$num1} （数値部分のみ抽出）" . PHP_EOL;

// 非数値文字列
$str2 = "abc123";
$num2 = (int) $str2;
echo "'{$str2}' → {$num2} （数値に変換できない場合は0）" . PHP_EOL;

// 空文字列
$str3 = "";
$num3 = (int) $str3;
echo "'{$str3}' → {$num3} （空文字列は0）" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 4. 厳格な型宣言の効果
// ============================================

echo "【4. 厳格な型宣言の効果】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 整数を2倍にする関数
 *
 * @param int $number 整数
 * @return int 2倍した値
 */
function doubleNumber(int $number): int
{
    return $number * 2;
}

// 正しい型で呼び出し
$result1 = doubleNumber(5);
echo "doubleNumber(5) = {$result1}" . PHP_EOL;

// 厳格な型宣言があるため、文字列を渡すとエラー
// 以下のコメントを外すとTypeErrorが発生します
// $result2 = doubleNumber("10");

echo "※ 厳格な型宣言（declare(strict_types=1)）により、" . PHP_EOL;
echo "  型が一致しない引数を渡すとTypeErrorが発生します" . PHP_EOL;
echo PHP_EOL;

/**
 * 文字列の長さを返す関数
 *
 * @param string $text 文字列
 * @return int 文字列の長さ
 */
function getLength(string $text): int
{
    return strlen($text);
}

$text = "Hello, PHP!";
$length = getLength($text);
echo "getLength('{$text}') = {$length}" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 5. 型の安全性を保つベストプラクティス
// ============================================

echo "【5. 型の安全性を保つベストプラクティス】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * ユーザー情報を整形する関数
 *
 * @param string $name 名前
 * @param int $age 年齢
 * @param float $height 身長
 * @return string 整形されたユーザー情報
 */
function formatUserInfo(string $name, int $age, float $height): string
{
    return sprintf(
        "名前: %s、年齢: %d歳、身長: %.1fcm",
        $name,
        $age,
        $height
    );
}

$userInfo = formatUserInfo("山田太郎", 25, 175.5);
echo $userInfo . PHP_EOL;
echo PHP_EOL;

/**
 * 価格に税込み金額を計算する関数
 *
 * @param int|float $price 価格
 * @param float $taxRate 税率
 * @return float 税込み価格
 */
function calculatePriceWithTax(int|float $price, float $taxRate): float
{
    return $price * (1 + $taxRate);
}

// Union型（PHP 8.0+）により、intまたはfloatを受け付ける
$price1 = calculatePriceWithTax(1000, 0.1);
$price2 = calculatePriceWithTax(1500.50, 0.1);

echo "税込み価格（整数）: ¥" . number_format($price1) . PHP_EOL;
echo "税込み価格（浮動小数）: ¥" . number_format($price2) . PHP_EOL;
echo PHP_EOL;

// ============================================
// 6. 型判定と条件分岐
// ============================================

echo "【6. 型判定と条件分岐】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 値の型を判定して処理する関数
 *
 * @param mixed $value 任意の値
 * @return string 型に応じたメッセージ
 */
function processValue(mixed $value): string
{
    if (is_int($value)) {
        return "整数: {$value}";
    } elseif (is_float($value)) {
        return "浮動小数: {$value}";
    } elseif (is_string($value)) {
        return "文字列: '{$value}'";
    } elseif (is_bool($value)) {
        return "真偽値: " . ($value ? "true" : "false");
    } elseif (is_array($value)) {
        return "配列（要素数: " . count($value) . "）";
    } elseif (is_null($value)) {
        return "null";
    } else {
        return "不明な型";
    }
}

echo processValue(100) . PHP_EOL;
echo processValue(3.14) . PHP_EOL;
echo processValue("Hello") . PHP_EOL;
echo processValue(true) . PHP_EOL;
echo processValue([1, 2, 3]) . PHP_EOL;
echo processValue(null) . PHP_EOL;
echo PHP_EOL;

// ============================================
// まとめ
// ============================================

echo "==================================" . PHP_EOL;
echo "  学習のポイント" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

echo "✅ 型キャストの方法:" . PHP_EOL;
echo "   - (int), (float), (string), (bool), (array)" . PHP_EOL;
echo "   - intval(), floatval(), strval(), boolval()" . PHP_EOL;
echo PHP_EOL;

echo "✅ 型変換の注意点:" . PHP_EOL;
echo "   - 文字列→数値: 数値部分のみ抽出、非数値文字列は0" . PHP_EOL;
echo "   - 浮動小数→整数: 小数部分は切り捨て" . PHP_EOL;
echo "   - bool変換: 0, '', [], null → false / その他 → true" . PHP_EOL;
echo PHP_EOL;

echo "✅ 厳格な型宣言（declare(strict_types=1)）:" . PHP_EOL;
echo "   - 関数の引数・戻り値で型チェックを厳格化" . PHP_EOL;
echo "   - 型が一致しない場合はTypeErrorを発生" . PHP_EOL;
echo "   - より安全なコードを書ける" . PHP_EOL;
echo PHP_EOL;

echo "✅ ベストプラクティス:" . PHP_EOL;
echo "   - 常に型宣言を使う" . PHP_EOL;
echo "   - Union型（int|float）で柔軟性を持たせる" . PHP_EOL;
echo "   - 型判定関数（is_int, is_string など）で安全に処理" . PHP_EOL;
echo PHP_EOL;

echo "✅ 次のステップ:" . PHP_EOL;
echo "   → 03_variable_scope.php で変数スコープを学習します" . PHP_EOL;
echo PHP_EOL;
