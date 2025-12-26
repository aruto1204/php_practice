<?php

declare(strict_types=1);

/**
 * Phase 1.4: 関数の基本
 *
 * このファイルでは、PHPの関数について学習します：
 * - 関数の定義と呼び出し
 * - 引数と戻り値
 * - 型宣言（Type Hints）
 * - 参照渡しと値渡し
 * - スコープ
 */

echo "=== Phase 1.4: 関数の基本 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 1. 関数の基本的な定義と呼び出し
// ============================================================

echo "【1. 関数の基本的な定義と呼び出し】" . PHP_EOL;

/**
 * 挨拶メッセージを表示する
 *
 * @return void
 */
function sayHello(): void
{
    echo "こんにちは！" . PHP_EOL;
}

sayHello();
sayHello();  // 何度でも呼び出せる

echo PHP_EOL;

// ============================================================
// 2. 引数を受け取る関数
// ============================================================

echo "【2. 引数を受け取る関数】" . PHP_EOL;

/**
 * 名前を指定して挨拶する
 *
 * @param string $name 名前
 * @return void
 */
function greet(string $name): void
{
    echo "こんにちは、{$name}さん！" . PHP_EOL;
}

greet("太郎");
greet("花子");

echo PHP_EOL;

/**
 * 複数の引数を受け取る関数
 *
 * @param string $firstName 名
 * @param string $lastName 姓
 * @return void
 */
function greetFull(string $firstName, string $lastName): void
{
    echo "こんにちは、{$lastName} {$firstName}さん！" . PHP_EOL;
}

greetFull("太郎", "山田");

echo PHP_EOL;

// ============================================================
// 3. 戻り値を返す関数
// ============================================================

echo "【3. 戻り値を返す関数】" . PHP_EOL;

/**
 * 2つの数値を加算する
 *
 * @param int $a 1つ目の数値
 * @param int $b 2つ目の数値
 * @return int 合計
 */
function add(int $a, int $b): int
{
    return $a + $b;
}

$result = add(10, 20);
echo "10 + 20 = {$result}" . PHP_EOL;

$sum = add(5, add(3, 7));  // 関数の結果を別の関数の引数に使える
echo "5 + (3 + 7) = {$sum}" . PHP_EOL;

echo PHP_EOL;

/**
 * 文字列を返す関数
 *
 * @param string $name 名前
 * @return string 挨拶メッセージ
 */
function createGreeting(string $name): string
{
    return "こんにちは、{$name}さん！";
}

$message = createGreeting("佐藤");
echo $message . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 4. 型宣言（Type Hints）
// ============================================================

echo "【4. 型宣言（Type Hints）】" . PHP_EOL;

/**
 * 価格に消費税を加算する
 *
 * @param int $price 価格
 * @param float $taxRate 税率
 * @return float 税込価格
 */
function addTax(int $price, float $taxRate): float
{
    return $price * (1 + $taxRate);
}

$priceWithTax = addTax(1000, 0.10);
echo "税抜¥1,000 → 税込¥" . number_format($priceWithTax) . PHP_EOL;

echo PHP_EOL;

// 複数の型を許容（Union型 - PHP 8.0+）
/**
 * 数値を文字列に変換
 *
 * @param int|float $number 数値
 * @return string 文字列
 */
function numberToString(int|float $number): string
{
    return (string)$number;
}

echo "整数: " . numberToString(123) . PHP_EOL;
echo "浮動小数: " . numberToString(123.45) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 5. Nullable型（null許容型）
// ============================================================

echo "【5. Nullable型】" . PHP_EOL;

/**
 * ユーザー名を取得（nullの可能性あり）
 *
 * @param int $userId ユーザーID
 * @return string|null ユーザー名、見つからない場合はnull
 */
function findUsername(int $userId): ?string
{
    $users = [
        1 => "山田太郎",
        2 => "佐藤花子",
        3 => "鈴木一郎",
    ];

    return $users[$userId] ?? null;
}

$username = findUsername(2);
echo "ユーザーID 2: " . ($username ?? "見つかりません") . PHP_EOL;

$username = findUsername(99);
echo "ユーザーID 99: " . ($username ?? "見つかりません") . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 6. 配列を引数・戻り値にする
// ============================================================

echo "【6. 配列を引数・戻り値にする】" . PHP_EOL;

/**
 * 配列の合計を計算
 *
 * @param array<int> $numbers 数値の配列
 * @return int 合計
 */
function sumArray(array $numbers): int
{
    $total = 0;
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

$numbers = [10, 20, 30, 40, 50];
$total = sumArray($numbers);
echo "配列の合計: {$total}" . PHP_EOL;

echo PHP_EOL;

/**
 * ユーザー情報を作成
 *
 * @param string $name 名前
 * @param int $age 年齢
 * @return array<string, mixed> ユーザー情報
 */
function createUser(string $name, int $age): array
{
    return [
        'name' => $name,
        'age' => $age,
        'is_adult' => $age >= 18,
        'created_at' => date('Y-m-d H:i:s'),
    ];
}

$user = createUser("田中美咲", 25);
echo "ユーザー: {$user['name']}, {$user['age']}歳, ";
echo $user['is_adult'] ? "成人" : "未成年";
echo PHP_EOL . PHP_EOL;

// ============================================================
// 7. 値渡しと参照渡し
// ============================================================

echo "【7. 値渡しと参照渡し】" . PHP_EOL;

// 値渡し（デフォルト）
/**
 * 値を2倍にする（値渡し）
 *
 * @param int $number 数値
 * @return int 2倍の値
 */
function doubleValue(int $number): int
{
    $number *= 2;
    return $number;
}

$value = 10;
$doubled = doubleValue($value);
echo "値渡し: 元の値 = {$value}, 2倍の値 = {$doubled}" . PHP_EOL;

echo PHP_EOL;

// 参照渡し（&を使用）
/**
 * 値を2倍にする（参照渡し）
 *
 * @param int $number 数値（参照）
 * @return void
 */
function doubleByReference(int &$number): void
{
    $number *= 2;
}

$value = 10;
echo "参照渡し前: {$value}" . PHP_EOL;
doubleByReference($value);
echo "参照渡し後: {$value}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 8. 早期リターン
// ============================================================

echo "【8. 早期リターン】" . PHP_EOL;

/**
 * 割り算を実行（早期リターンパターン）
 *
 * @param float $dividend 被除数
 * @param float $divisor 除数
 * @return float|string 商、またはエラーメッセージ
 */
function divide(float $dividend, float $divisor): float|string
{
    // ゼロ除算チェック
    if ($divisor === 0.0) {
        return "エラー: ゼロで割ることはできません";
    }

    return $dividend / $divisor;
}

echo "10 ÷ 2 = " . divide(10, 2) . PHP_EOL;
echo "10 ÷ 0 = " . divide(10, 0) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 9. 関数内の変数スコープ
// ============================================================

echo "【9. 関数内の変数スコープ】" . PHP_EOL;

$globalVariable = "グローバル変数";

/**
 * ローカル変数を使用
 *
 * @return void
 */
function demonstrateScope(): void
{
    $localVariable = "ローカル変数";
    echo "関数内: {$localVariable}" . PHP_EOL;

    // グローバル変数にはアクセスできない（推奨しない）
    // echo $globalVariable;  // エラー
}

demonstrateScope();
echo "関数外: {$globalVariable}" . PHP_EOL;
// echo $localVariable;  // エラー: 関数外からはアクセスできない

echo PHP_EOL;

// ============================================================
// 10. 実用例：バリデーション関数
// ============================================================

echo "【10. 実用例：バリデーション関数】" . PHP_EOL;

/**
 * メールアドレスの形式をチェック
 *
 * @param string $email メールアドレス
 * @return bool 有効ならtrue
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * パスワードの強度をチェック
 *
 * @param string $password パスワード
 * @return bool 強力ならtrue（8文字以上、英数字含む）
 */
function isStrongPassword(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }

    // 英字と数字の両方を含むかチェック
    $hasLetter = preg_match('/[a-zA-Z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);

    return $hasLetter && $hasNumber;
}

$testEmails = ['test@example.com', 'invalid-email', 'user@domain'];
foreach ($testEmails as $email) {
    $valid = isValidEmail($email) ? '✅ 有効' : '❌ 無効';
    echo "メール「{$email}」: {$valid}" . PHP_EOL;
}

echo PHP_EOL;

$testPasswords = ['abc123', 'password', 'StrongPass123', '12345678'];
foreach ($testPasswords as $password) {
    $strong = isStrongPassword($password) ? '✅ 強力' : '❌ 弱い';
    echo "パスワード「{$password}」: {$strong}" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 11. 実用例：フォーマット関数
// ============================================================

echo "【11. 実用例：フォーマット関数】" . PHP_EOL;

/**
 * 金額をフォーマット
 *
 * @param int $amount 金額
 * @return string フォーマット済み金額
 */
function formatPrice(int $amount): string
{
    return "¥" . number_format($amount);
}

/**
 * 日付をフォーマット
 *
 * @param string $date 日付文字列
 * @return string フォーマット済み日付
 */
function formatDate(string $date): string
{
    $timestamp = strtotime($date);
    return date('Y年n月j日', $timestamp);
}

echo formatPrice(1234567) . PHP_EOL;
echo formatDate('2025-12-25') . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 12. 実用例：計算関数
// ============================================================

echo "【12. 実用例：計算関数】" . PHP_EOL;

/**
 * BMIを計算
 *
 * @param float $weight 体重（kg）
 * @param float $height 身長（cm）
 * @return float BMI値
 */
function calculateBMI(float $weight, float $height): float
{
    $heightInMeters = $height / 100;
    return $weight / ($heightInMeters ** 2);
}

/**
 * BMI判定を取得
 *
 * @param float $bmi BMI値
 * @return string 判定結果
 */
function getBMICategory(float $bmi): string
{
    return match (true) {
        $bmi < 18.5 => "低体重",
        $bmi < 25.0 => "普通体重",
        $bmi < 30.0 => "肥満（1度）",
        $bmi < 35.0 => "肥満（2度）",
        default => "肥満（3度以上）",
    };
}

$weight = 70;
$height = 175;
$bmi = calculateBMI($weight, $height);
$category = getBMICategory($bmi);

echo "体重: {$weight}kg, 身長: {$height}cm" . PHP_EOL;
echo "BMI: " . round($bmi, 2) . " ({$category})" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 13. 実用例：データ変換関数
// ============================================================

echo "【13. 実用例：データ変換関数】" . PHP_EOL;

/**
 * 配列をカンマ区切り文字列に変換
 *
 * @param array<string> $items 項目の配列
 * @return string カンマ区切り文字列
 */
function arrayToCommaSeparated(array $items): string
{
    return implode(', ', $items);
}

/**
 * カンマ区切り文字列を配列に変換
 *
 * @param string $text カンマ区切り文字列
 * @return array<string> 項目の配列
 */
function commaSeparatedToArray(string $text): array
{
    return array_map('trim', explode(',', $text));
}

$fruits = ['りんご', 'バナナ', 'オレンジ'];
$text = arrayToCommaSeparated($fruits);
echo "配列 → 文字列: {$text}" . PHP_EOL;

$backToArray = commaSeparatedToArray($text);
echo "文字列 → 配列: " . print_r($backToArray, true);

echo PHP_EOL;

// ============================================================
// 14. 関数の組み合わせ
// ============================================================

echo "【14. 関数の組み合わせ】" . PHP_EOL;

/**
 * 割引後の価格を計算
 *
 * @param int $price 元の価格
 * @param int $discountPercent 割引率（%）
 * @return int 割引後の価格
 */
function applyDiscount(int $price, int $discountPercent): int
{
    $discountAmount = ($price * $discountPercent) / 100;
    return $price - (int)$discountAmount;
}

/**
 * 消費税を加算
 *
 * @param int $price 価格
 * @param float $taxRate 税率
 * @return int 税込価格
 */
function addSalesTax(int $price, float $taxRate): int
{
    return (int)($price * (1 + $taxRate));
}

$originalPrice = 10000;
$afterDiscount = applyDiscount($originalPrice, 20);  // 20%割引
$finalPrice = addSalesTax($afterDiscount, 0.10);     // 10%の消費税

echo "元の価格: " . formatPrice($originalPrice) . PHP_EOL;
echo "20%割引後: " . formatPrice($afterDiscount) . PHP_EOL;
echo "税込価格: " . formatPrice($finalPrice) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 学習のポイント
// ============================================================

echo "【学習のポイント】" . PHP_EOL;
echo "1. 関数は再利用可能なコードの塊" . PHP_EOL;
echo "2. 型宣言を必ず使用する（厳格な型により安全性向上）" . PHP_EOL;
echo "3. 関数は1つの責任を持つべき（単一責任の原則）" . PHP_EOL;
echo "4. 早期リターンでネストを避ける" . PHP_EOL;
echo "5. PHPDocコメントで関数の目的を明確にする" . PHP_EOL;
echo "6. 参照渡しは必要な場合のみ使用（通常は値を返す）" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.4: 関数の基本 完了 ===" . PHP_EOL;
