<?php

declare(strict_types=1);

/**
 * Phase 1.1: 変数とデータ型の学習プログラム
 *
 * このプログラムでは以下を学習します:
 * - 変数の宣言と命名規則
 * - データ型（string, int, float, bool, array, null）
 * - var_dump() による型の確認
 * - 厳格な型宣言の重要性
 */

echo "==================================" . PHP_EOL;
echo "  Phase 1.1: 変数とデータ型" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 1. 変数の宣言と基本的なデータ型
// ============================================

echo "【1. 基本的なデータ型】" . PHP_EOL;
echo "---" . PHP_EOL;

// string型（文字列）
$userName = "山田太郎";
echo "名前（string）: {$userName}" . PHP_EOL;
var_dump($userName);
echo PHP_EOL;

// int型（整数）
$age = 25;
echo "年齢（int）: {$age}" . PHP_EOL;
var_dump($age);
echo PHP_EOL;

// float型（浮動小数点数）
$height = 175.5;
echo "身長（float）: {$height}cm" . PHP_EOL;
var_dump($height);
echo PHP_EOL;

// bool型（真偽値）
$isStudent = true;
echo "学生ステータス（bool）: " . ($isStudent ? "学生" : "非学生") . PHP_EOL;
var_dump($isStudent);
echo PHP_EOL;

// array型（配列）
$hobbies = ["読書", "プログラミング", "音楽"];
echo "趣味（array）: " . implode(", ", $hobbies) . PHP_EOL;
var_dump($hobbies);
echo PHP_EOL;

// null型
$nickname = null;
echo "ニックネーム（null）: " . ($nickname ?? "未設定") . PHP_EOL;
var_dump($nickname);
echo PHP_EOL;

// ============================================
// 2. 変数の命名規則
// ============================================

echo "【2. 変数の命名規則】" . PHP_EOL;
echo "---" . PHP_EOL;

// camelCase（推奨）
$firstName = "太郎";
$lastName = "山田";
$fullName = $lastName . " " . $firstName;
echo "フルネーム: {$fullName}" . PHP_EOL;
echo PHP_EOL;

// 真偽値は is, has, can で始める
$isActive = true;
$hasPermission = false;
$canEdit = true;

echo "アクティブ: " . ($isActive ? "はい" : "いいえ") . PHP_EOL;
echo "権限あり: " . ($hasPermission ? "はい" : "いいえ") . PHP_EOL;
echo "編集可能: " . ($canEdit ? "はい" : "いいえ") . PHP_EOL;
echo PHP_EOL;

// 定数は UPPER_CASE
const MAX_LOGIN_ATTEMPTS = 5;
const DATABASE_HOST = 'localhost';

echo "最大ログイン試行回数: " . MAX_LOGIN_ATTEMPTS . PHP_EOL;
echo "データベースホスト: " . DATABASE_HOST . PHP_EOL;
echo PHP_EOL;

// ============================================
// 3. 文字列の操作
// ============================================

echo "【3. 文字列の操作】" . PHP_EOL;
echo "---" . PHP_EOL;

$greeting = "こんにちは";
$name = "太郎";

// 文字列の結合（連結演算子）
$message1 = $greeting . "、" . $name . "さん";
echo "結合（.演算子）: {$message1}" . PHP_EOL;

// 文字列の展開（ダブルクォート）
$message2 = "{$greeting}、{$name}さん";
echo "展開（ダブルクォート）: {$message2}" . PHP_EOL;

// シングルクォートは変数展開しない
$message3 = '{$greeting}、{$name}さん';
echo "シングルクォート: {$message3}" . PHP_EOL;
echo PHP_EOL;

// 文字列関数
$text = "PHP Programming";
echo "元の文字列: {$text}" . PHP_EOL;
echo "小文字: " . strtolower($text) . PHP_EOL;
echo "大文字: " . strtoupper($text) . PHP_EOL;
echo "文字数: " . strlen($text) . PHP_EOL;
echo "部分文字列: " . substr($text, 0, 3) . PHP_EOL;
echo PHP_EOL;

// ============================================
// 4. 数値の操作
// ============================================

echo "【4. 数値の操作】" . PHP_EOL;
echo "---" . PHP_EOL;

$price = 1000;
$quantity = 3;
$taxRate = 0.1;

$subtotal = $price * $quantity;
$tax = $subtotal * $taxRate;
$total = $subtotal + $tax;

echo "単価: ¥{$price}" . PHP_EOL;
echo "数量: {$quantity}" . PHP_EOL;
echo "小計: ¥{$subtotal}" . PHP_EOL;
echo "税額: ¥{$tax}" . PHP_EOL;
echo "合計: ¥{$total}" . PHP_EOL;
echo PHP_EOL;

// 数学関数
$number = 3.7;
echo "元の数値: {$number}" . PHP_EOL;
echo "切り上げ: " . ceil($number) . PHP_EOL;
echo "切り捨て: " . floor($number) . PHP_EOL;
echo "四捨五入: " . round($number) . PHP_EOL;
echo "絶対値: " . abs(-5) . PHP_EOL;
echo "累乗: " . pow(2, 3) . PHP_EOL;
echo "平方根: " . sqrt(16) . PHP_EOL;
echo PHP_EOL;

// ============================================
// 5. 配列の基礎
// ============================================

echo "【5. 配列の基礎】" . PHP_EOL;
echo "---" . PHP_EOL;

// インデックス配列
$fruits = ["りんご", "バナナ", "オレンジ"];
echo "フルーツ: " . implode(", ", $fruits) . PHP_EOL;
echo "最初の要素: {$fruits[0]}" . PHP_EOL;
echo "配列の長さ: " . count($fruits) . PHP_EOL;
echo PHP_EOL;

// 連想配列
$user = [
    "id" => 1,
    "name" => "山田太郎",
    "email" => "taro@example.com",
    "age" => 25,
];

echo "ユーザー情報:" . PHP_EOL;
echo "  ID: {$user['id']}" . PHP_EOL;
echo "  名前: {$user['name']}" . PHP_EOL;
echo "  メール: {$user['email']}" . PHP_EOL;
echo "  年齢: {$user['age']}" . PHP_EOL;
echo PHP_EOL;

// 多次元配列
$users = [
    ["id" => 1, "name" => "山田太郎", "age" => 25],
    ["id" => 2, "name" => "佐藤花子", "age" => 22],
    ["id" => 3, "name" => "鈴木一郎", "age" => 30],
];

echo "ユーザーリスト:" . PHP_EOL;
foreach ($users as $u) {
    echo "  {$u['id']}: {$u['name']} ({$u['age']}歳)" . PHP_EOL;
}
echo PHP_EOL;

// ============================================
// 6. 型の確認と判定
// ============================================

echo "【6. 型の確認と判定】" . PHP_EOL;
echo "---" . PHP_EOL;

$value1 = "123";
$value2 = 123;
$value3 = 123.45;
$value4 = true;
$value5 = [];

echo "値: {$value1} -> 型: " . gettype($value1) . " -> 文字列?: " . (is_string($value1) ? "はい" : "いいえ") . PHP_EOL;
echo "値: {$value2} -> 型: " . gettype($value2) . " -> 整数?: " . (is_int($value2) ? "はい" : "いいえ") . PHP_EOL;
echo "値: {$value3} -> 型: " . gettype($value3) . " -> 浮動小数?: " . (is_float($value3) ? "はい" : "いいえ") . PHP_EOL;
echo "値: " . ($value4 ? "true" : "false") . " -> 型: " . gettype($value4) . " -> 真偽値?: " . (is_bool($value4) ? "はい" : "いいえ") . PHP_EOL;
echo "値: (空配列) -> 型: " . gettype($value5) . " -> 配列?: " . (is_array($value5) ? "はい" : "いいえ") . PHP_EOL;
echo PHP_EOL;

// ============================================
// 7. null合体演算子
// ============================================

echo "【7. null合体演算子（??）】" . PHP_EOL;
echo "---" . PHP_EOL;

$username = null;
$defaultUsername = "ゲスト";

// null合体演算子 - 左側がnullの場合は右側を返す
$displayName = $username ?? $defaultUsername;
echo "表示名: {$displayName}" . PHP_EOL;

// 連鎖も可能
$config = null;
$envConfig = null;
$defaultConfig = "デフォルト設定";

$finalConfig = $config ?? $envConfig ?? $defaultConfig;
echo "最終設定: {$finalConfig}" . PHP_EOL;
echo PHP_EOL;

// ============================================
// まとめ
// ============================================

echo "==================================" . PHP_EOL;
echo "  学習のポイント" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

echo "✅ 主要なデータ型:" . PHP_EOL;
echo "   - string: 文字列" . PHP_EOL;
echo "   - int: 整数" . PHP_EOL;
echo "   - float: 浮動小数点数" . PHP_EOL;
echo "   - bool: 真偽値（true/false）" . PHP_EOL;
echo "   - array: 配列" . PHP_EOL;
echo "   - null: 値なし" . PHP_EOL;
echo PHP_EOL;

echo "✅ 命名規則:" . PHP_EOL;
echo "   - 変数: camelCase ($userName, $totalPrice)" . PHP_EOL;
echo "   - 定数: UPPER_CASE (MAX_VALUE, DB_HOST)" . PHP_EOL;
echo "   - 真偽値: is/has/can で始める ($isActive, $hasPermission)" . PHP_EOL;
echo PHP_EOL;

echo "✅ 便利な機能:" . PHP_EOL;
echo "   - var_dump(): 変数の型と値を確認" . PHP_EOL;
echo "   - gettype(): 型を取得" . PHP_EOL;
echo "   - is_*(): 型判定関数（is_string, is_int, is_array など）" . PHP_EOL;
echo "   - ?? : null合体演算子" . PHP_EOL;
echo PHP_EOL;

echo "✅ 次のステップ:" . PHP_EOL;
echo "   → 02_type_casting.php で型変換を学習します" . PHP_EOL;
echo PHP_EOL;
