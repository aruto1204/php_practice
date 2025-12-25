<?php

declare(strict_types=1);

/**
 * Phase 1.1 演習課題: 基本的な変数操作プログラム
 *
 * この演習では、以下の実践的な課題に取り組みます:
 * 1. ユーザー情報の管理
 * 2. 商品の価格計算
 * 3. 学生の成績処理
 * 4. データ型の変換と検証
 */

echo "==================================" . PHP_EOL;
echo "  Phase 1.1 演習課題" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 演習1: ユーザー情報の管理
// ============================================

echo "【演習1: ユーザー情報の管理】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * ユーザー情報を作成する
 *
 * @param string $name 名前
 * @param int $age 年齢
 * @param string $email メールアドレス
 * @param array<string> $hobbies 趣味の配列
 * @return array<string, mixed> ユーザー情報
 */
function createUser(string $name, int $age, string $email, array $hobbies): array
{
    return [
        'name' => $name,
        'age' => $age,
        'email' => $email,
        'hobbies' => $hobbies,
        'is_adult' => $age >= 18,
        'registered_at' => date('Y-m-d H:i:s'),
    ];
}

/**
 * ユーザー情報を表示する
 *
 * @param array<string, mixed> $user ユーザー情報
 */
function displayUser(array $user): void
{
    echo "【ユーザー情報】" . PHP_EOL;
    echo "  名前: {$user['name']}" . PHP_EOL;
    echo "  年齢: {$user['age']}歳 (" . ($user['is_adult'] ? "成人" : "未成年") . ")" . PHP_EOL;
    echo "  メール: {$user['email']}" . PHP_EOL;
    echo "  趣味: " . implode(", ", $user['hobbies']) . PHP_EOL;
    echo "  登録日時: {$user['registered_at']}" . PHP_EOL;
}

// ユーザーを作成して表示
$user1 = createUser(
    "山田太郎",
    25,
    "taro@example.com",
    ["プログラミング", "読書", "音楽"]
);

displayUser($user1);
echo PHP_EOL;

$user2 = createUser(
    "佐藤花子",
    17,
    "hanako@example.com",
    ["絵画", "ダンス"]
);

displayUser($user2);
echo PHP_EOL;

// ============================================
// 演習2: 商品の価格計算
// ============================================

echo "【演習2: 商品の価格計算】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 商品情報を作成する
 *
 * @param string $name 商品名
 * @param int $price 価格（税抜）
 * @param int $quantity 数量
 * @return array<string, mixed> 商品情報
 */
function createProduct(string $name, int $price, int $quantity): array
{
    const TAX_RATE = 0.1;

    $subtotal = $price * $quantity;
    $tax = (int) ($subtotal * TAX_RATE);
    $total = $subtotal + $tax;

    return [
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total,
    ];
}

/**
 * 商品情報を表示する
 *
 * @param array<string, mixed> $product 商品情報
 */
function displayProduct(array $product): void
{
    echo "商品名: {$product['name']}" . PHP_EOL;
    echo "  単価: ¥" . number_format($product['price']) . PHP_EOL;
    echo "  数量: {$product['quantity']}" . PHP_EOL;
    echo "  小計: ¥" . number_format($product['subtotal']) . PHP_EOL;
    echo "  税額: ¥" . number_format($product['tax']) . PHP_EOL;
    echo "  合計: ¥" . number_format($product['total']) . PHP_EOL;
}

// 商品を作成して表示
$products = [
    createProduct("ノートPC", 150000, 1),
    createProduct("マウス", 3000, 2),
    createProduct("USBメモリ", 1500, 3),
];

foreach ($products as $product) {
    displayProduct($product);
    echo PHP_EOL;
}

// カート合計を計算
$cartTotal = array_reduce(
    $products,
    fn($carry, $product) => $carry + $product['total'],
    0
);

echo "カート合計: ¥" . number_format($cartTotal) . PHP_EOL;
echo PHP_EOL;

// ============================================
// 演習3: 学生の成績処理
// ============================================

echo "【演習3: 学生の成績処理】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 学生の成績を作成する
 *
 * @param string $name 名前
 * @param int $math 数学の点数
 * @param int $english 英語の点数
 * @param int $science 理科の点数
 * @return array<string, mixed> 成績情報
 */
function createGrade(string $name, int $math, int $english, int $science): array
{
    $total = $math + $english + $science;
    $average = $total / 3;

    // 評価を判定
    $grade = match (true) {
        $average >= 90 => 'A',
        $average >= 80 => 'B',
        $average >= 70 => 'C',
        $average >= 60 => 'D',
        default => 'F',
    };

    return [
        'name' => $name,
        'math' => $math,
        'english' => $english,
        'science' => $science,
        'total' => $total,
        'average' => $average,
        'grade' => $grade,
        'passed' => $average >= 60,
    ];
}

/**
 * 成績を表示する
 *
 * @param array<string, mixed> $grade 成績情報
 */
function displayGrade(array $grade): void
{
    echo "【{$grade['name']}の成績】" . PHP_EOL;
    echo "  数学: {$grade['math']}点" . PHP_EOL;
    echo "  英語: {$grade['english']}点" . PHP_EOL;
    echo "  理科: {$grade['science']}点" . PHP_EOL;
    echo "  合計: {$grade['total']}点" . PHP_EOL;
    echo "  平均: " . number_format($grade['average'], 1) . "点" . PHP_EOL;
    echo "  評価: {$grade['grade']}" . PHP_EOL;
    echo "  判定: " . ($grade['passed'] ? "合格 ✓" : "不合格 ✗") . PHP_EOL;
}

// 学生の成績を作成して表示
$students = [
    createGrade("山田太郎", 85, 90, 88),
    createGrade("佐藤花子", 95, 92, 98),
    createGrade("鈴木一郎", 65, 70, 68),
    createGrade("田中次郎", 45, 55, 50),
];

foreach ($students as $student) {
    displayGrade($student);
    echo PHP_EOL;
}

// クラス全体の統計
$classAverage = array_reduce(
    $students,
    fn($carry, $student) => $carry + $student['average'],
    0
) / count($students);

$passedCount = count(array_filter($students, fn($student) => $student['passed']));

echo "【クラス全体の統計】" . PHP_EOL;
echo "  生徒数: " . count($students) . "名" . PHP_EOL;
echo "  平均点: " . number_format($classAverage, 1) . "点" . PHP_EOL;
echo "  合格者: {$passedCount}名" . PHP_EOL;
echo "  合格率: " . number_format(($passedCount / count($students)) * 100, 1) . "%" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 演習4: データ型の変換と検証
// ============================================

echo "【演習4: データ型の変換と検証】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 文字列を整数に安全に変換する
 *
 * @param string $value 変換する文字列
 * @param int $default デフォルト値
 * @return int 変換された整数
 */
function safeStringToInt(string $value, int $default = 0): int
{
    if (!is_numeric($value)) {
        return $default;
    }

    return (int) $value;
}

/**
 * 値の型と内容を検証する
 *
 * @param mixed $value 検証する値
 * @return array<string, mixed> 検証結果
 */
function validateValue(mixed $value): array
{
    return [
        'value' => $value,
        'type' => gettype($value),
        'is_string' => is_string($value),
        'is_int' => is_int($value),
        'is_float' => is_float($value),
        'is_bool' => is_bool($value),
        'is_array' => is_array($value),
        'is_null' => is_null($value),
        'is_numeric' => is_numeric($value),
        'is_empty' => empty($value),
    ];
}

/**
 * 検証結果を表示する
 *
 * @param array<string, mixed> $result 検証結果
 */
function displayValidation(array $result): void
{
    $valueDisplay = is_array($result['value'])
        ? '[配列]'
        : (is_null($result['value']) ? 'null' : (string) $result['value']);

    echo "値: {$valueDisplay}" . PHP_EOL;
    echo "  型: {$result['type']}" . PHP_EOL;
    echo "  is_numeric: " . ($result['is_numeric'] ? "true" : "false") . PHP_EOL;
    echo "  is_empty: " . ($result['is_empty'] ? "true" : "false") . PHP_EOL;
}

// 様々な値を検証
$testValues = [
    "123",
    123,
    "abc",
    0,
    "",
    [],
    ["a", "b"],
    null,
    true,
    false,
];

echo "型の検証テスト:" . PHP_EOL;
echo PHP_EOL;

foreach ($testValues as $value) {
    $result = validateValue($value);
    displayValidation($result);
    echo PHP_EOL;
}

// 文字列から整数への安全な変換
echo "文字列から整数への変換テスト:" . PHP_EOL;

$testStrings = ["123", "456abc", "abc", ""];

foreach ($testStrings as $str) {
    $converted = safeStringToInt($str, -1);
    echo "  '{$str}' → {$converted}" . PHP_EOL;
}

echo PHP_EOL;

// ============================================
// まとめ
// ============================================

echo "==================================" . PHP_EOL;
echo "  演習課題完了！" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

echo "✅ 完了した課題:" . PHP_EOL;
echo "   1. ユーザー情報の管理 - 配列と関数の活用" . PHP_EOL;
echo "   2. 商品の価格計算 - 数値計算と配列操作" . PHP_EOL;
echo "   3. 学生の成績処理 - 統計計算とmatch式の使用" . PHP_EOL;
echo "   4. データ型の変換と検証 - 型安全性の確保" . PHP_EOL;
echo PHP_EOL;

echo "✅ 習得したスキル:" . PHP_EOL;
echo "   - 型宣言を使った関数の作成" . PHP_EOL;
echo "   - 配列の操作（作成、反復、集計）" . PHP_EOL;
echo "   - 型変換と検証" . PHP_EOL;
echo "   - match式による条件分岐" . PHP_EOL;
echo "   - array_reduce, array_filter などの配列関数" . PHP_EOL;
echo PHP_EOL;

echo "🎉 Phase 1.1（変数とデータ型）の学習が完了しました！" . PHP_EOL;
echo PHP_EOL;
