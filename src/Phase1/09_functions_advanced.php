<?php

declare(strict_types=1);

/**
 * Phase 1.4: 関数の高度な機能
 *
 * このファイルでは、PHPの高度な関数機能について学習します：
 * - デフォルト引数
 * - 可変長引数
 * - 名前付き引数（PHP 8.0+）
 * - アロー関数（PHP 7.4+）
 * - 無名関数（クロージャ）
 */

echo "=== Phase 1.4: 関数の高度な機能 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 1. デフォルト引数
// ============================================================

echo "【1. デフォルト引数】" . PHP_EOL;

/**
 * 挨拶メッセージを作成（デフォルト引数）
 *
 * @param string $name 名前
 * @param string $greeting 挨拶（デフォルト: "こんにちは"）
 * @return string 挨拶メッセージ
 */
function createGreeting(string $name, string $greeting = "こんにちは"): string
{
    return "{$greeting}、{$name}さん！";
}

echo createGreeting("太郎") . PHP_EOL;  // デフォルト値を使用
echo createGreeting("花子", "おはよう") . PHP_EOL;  // カスタム値を使用

echo PHP_EOL;

/**
 * ユーザー情報を作成
 *
 * @param string $name 名前
 * @param int $age 年齢（デフォルト: 18）
 * @param string $country 国（デフォルト: "日本"）
 * @return array<string, mixed> ユーザー情報
 */
function buildUser(string $name, int $age = 18, string $country = "日本"): array
{
    return [
        'name' => $name,
        'age' => $age,
        'country' => $country,
    ];
}

$user1 = buildUser("山田太郎");
$user2 = buildUser("佐藤花子", 25);
$user3 = buildUser("田中健", 30, "アメリカ");

echo "ユーザー1: {$user1['name']}, {$user1['age']}歳, {$user1['country']}" . PHP_EOL;
echo "ユーザー2: {$user2['name']}, {$user2['age']}歳, {$user2['country']}" . PHP_EOL;
echo "ユーザー3: {$user3['name']}, {$user3['age']}歳, {$user3['country']}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 2. 可変長引数（...演算子）
// ============================================================

echo "【2. 可変長引数】" . PHP_EOL;

/**
 * 複数の数値の合計を計算
 *
 * @param int ...$numbers 数値（可変長）
 * @return int 合計
 */
function sum(int ...$numbers): int
{
    $total = 0;
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

echo "sum(1, 2, 3) = " . sum(1, 2, 3) . PHP_EOL;
echo "sum(10, 20, 30, 40, 50) = " . sum(10, 20, 30, 40, 50) . PHP_EOL;
echo "sum(5) = " . sum(5) . PHP_EOL;

echo PHP_EOL;

/**
 * 文字列を結合
 *
 * @param string $separator 区切り文字
 * @param string ...$parts 結合する文字列（可変長）
 * @return string 結合された文字列
 */
function joinStrings(string $separator, string ...$parts): string
{
    return implode($separator, $parts);
}

echo joinStrings(" ", "Hello", "World", "!") . PHP_EOL;
echo joinStrings(", ", "りんご", "バナナ", "オレンジ") . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 3. 名前付き引数（PHP 8.0+）
// ============================================================

echo "【3. 名前付き引数（PHP 8.0+）】" . PHP_EOL;

/**
 * ユーザープロフィールを作成
 *
 * @param string $name 名前
 * @param int $age 年齢
 * @param string $email メールアドレス
 * @param string $city 都市
 * @return array<string, mixed> プロフィール
 */
function createProfile(string $name, int $age, string $email, string $city): array
{
    return [
        'name' => $name,
        'age' => $age,
        'email' => $email,
        'city' => $city,
    ];
}

// 通常の呼び出し（位置引数）
$profile1 = createProfile("山田太郎", 25, "yamada@example.com", "東京");

// 名前付き引数（順序を変えられる）
$profile2 = createProfile(
    name: "佐藤花子",
    email: "sato@example.com",
    city: "大阪",
    age: 30
);

// 一部だけ名前付き引数
$profile3 = createProfile(
    "鈴木一郎",
    age: 28,
    email: "suzuki@example.com",
    city: "名古屋"
);

echo "プロフィール1: {$profile1['name']}, {$profile1['city']}" . PHP_EOL;
echo "プロフィール2: {$profile2['name']}, {$profile2['city']}" . PHP_EOL;
echo "プロフィール3: {$profile3['name']}, {$profile3['city']}" . PHP_EOL;

echo PHP_EOL;

// デフォルト引数と名前付き引数の組み合わせ
/**
 * ページネーション情報を作成
 *
 * @param int $page ページ番号（デフォルト: 1）
 * @param int $perPage 1ページあたりの項目数（デフォルト: 20）
 * @param string $sortBy ソート項目（デフォルト: "created_at"）
 * @param string $order 並び順（デフォルト: "desc"）
 * @return array<string, mixed> ページネーション情報
 */
function createPagination(
    int $page = 1,
    int $perPage = 20,
    string $sortBy = "created_at",
    string $order = "desc"
): array {
    return [
        'page' => $page,
        'per_page' => $perPage,
        'sort_by' => $sortBy,
        'order' => $order,
        'offset' => ($page - 1) * $perPage,
    ];
}

// 名前付き引数で必要な部分のみ指定
$pagination = createPagination(page: 3, sortBy: "name", order: "asc");
echo "ページ: {$pagination['page']}, ";
echo "ソート: {$pagination['sort_by']} ({$pagination['order']})" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 4. アロー関数（PHP 7.4+）
// ============================================================

echo "【4. アロー関数】" . PHP_EOL;

// 通常の無名関数
$multiply = function (int $a, int $b): int {
    return $a * $b;
};

// アロー関数（短縮記法）
$multiplyArrow = fn(int $a, int $b): int => $a * $b;

echo "通常の無名関数: " . $multiply(5, 3) . PHP_EOL;
echo "アロー関数: " . $multiplyArrow(5, 3) . PHP_EOL;

echo PHP_EOL;

// 配列操作でのアロー関数
$numbers = [1, 2, 3, 4, 5];

$doubled = array_map(fn($n) => $n * 2, $numbers);
echo "2倍: " . implode(', ', $doubled) . PHP_EOL;

$squared = array_map(fn($n) => $n ** 2, $numbers);
echo "2乗: " . implode(', ', $squared) . PHP_EOL;

$even = array_filter($numbers, fn($n) => $n % 2 === 0);
echo "偶数: " . implode(', ', $even) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 5. 無名関数（クロージャ）
// ============================================================

echo "【5. 無名関数（クロージャ）】" . PHP_EOL;

/**
 * コールバック関数を使った処理
 *
 * @param array<int> $numbers 数値の配列
 * @param callable $callback コールバック関数
 * @return array<int> 処理後の配列
 */
function processNumbers(array $numbers, callable $callback): array
{
    $result = [];
    foreach ($numbers as $number) {
        $result[] = $callback($number);
    }
    return $result;
}

$numbers = [1, 2, 3, 4, 5];

// 無名関数を引数として渡す
$tripled = processNumbers($numbers, function (int $n): int {
    return $n * 3;
});
echo "3倍: " . implode(', ', $tripled) . PHP_EOL;

// アロー関数を引数として渡す
$plusTen = processNumbers($numbers, fn($n) => $n + 10);
echo "+10: " . implode(', ', $plusTen) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 6. use キーワード（外部変数の取り込み）
// ============================================================

echo "【6. useキーワード】" . PHP_EOL;

$multiplier = 10;

$multiplyBy10 = function (int $number) use ($multiplier): int {
    return $number * $multiplier;
};

echo "5 × 10 = " . $multiplyBy10(5) . PHP_EOL;

// アロー関数は自動的に外部変数を取り込む
$multiplyBy10Arrow = fn(int $number): int => $number * $multiplier;
echo "7 × 10 = " . $multiplyBy10Arrow(7) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 7. 実用例：バリデーション関数のチェーン
// ============================================================

echo "【7. 実用例：バリデーション関数のチェーン】" . PHP_EOL;

/**
 * バリデーションルールを実行
 *
 * @param mixed $value 検証する値
 * @param array<callable> $rules ルールの配列
 * @return array{valid: bool, errors: array<string>} 検証結果
 */
function validate(mixed $value, array $rules): array
{
    $errors = [];

    foreach ($rules as $rule) {
        $result = $rule($value);
        if ($result !== true) {
            $errors[] = $result;
        }
    }

    return [
        'valid' => count($errors) === 0,
        'errors' => $errors,
    ];
}

// バリデーションルールを定義
$minLength = fn(int $min) => fn(string $value): bool|string =>
    strlen($value) >= $min ?: "最低{$min}文字必要です";

$maxLength = fn(int $max) => fn(string $value): bool|string =>
    strlen($value) <= $max ?: "最大{$max}文字までです";

$hasNumber = fn(string $value): bool|string =>
    preg_match('/[0-9]/', $value) ? true : "数字を含む必要があります";

// パスワードのバリデーション
$password = "abc123";
$result = validate($password, [
    $minLength(8),
    $maxLength(20),
    $hasNumber,
]);

echo "パスワード「{$password}」の検証:" . PHP_EOL;
if ($result['valid']) {
    echo "✅ 有効" . PHP_EOL;
} else {
    foreach ($result['errors'] as $error) {
        echo "❌ {$error}" . PHP_EOL;
    }
}

echo PHP_EOL;

// ============================================================
// 8. 実用例：カスタムソート
// ============================================================

echo "【8. 実用例：カスタムソート】" . PHP_EOL;

$products = [
    ['name' => 'ノートPC', 'price' => 98000, 'stock' => 5],
    ['name' => 'マウス', 'price' => 1500, 'stock' => 20],
    ['name' => 'キーボード', 'price' => 8000, 'stock' => 10],
    ['name' => 'モニター', 'price' => 35000, 'stock' => 3],
];

// 価格で昇順ソート
$byPrice = $products;
usort($byPrice, fn($a, $b) => $a['price'] <=> $b['price']);

echo "価格順（安い順）:" . PHP_EOL;
foreach ($byPrice as $product) {
    echo "  {$product['name']}: ¥" . number_format($product['price']) . PHP_EOL;
}

echo PHP_EOL;

// 在庫数で降順ソート
$byStock = $products;
usort($byStock, fn($a, $b) => $b['stock'] <=> $a['stock']);

echo "在庫順（多い順）:" . PHP_EOL;
foreach ($byStock as $product) {
    echo "  {$product['name']}: {$product['stock']}個" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 9. 実用例：関数ファクトリー
// ============================================================

echo "【9. 実用例：関数ファクトリー】" . PHP_EOL;

/**
 * 税率を適用する関数を生成
 *
 * @param float $taxRate 税率
 * @return callable 税込価格を計算する関数
 */
function createTaxCalculator(float $taxRate): callable
{
    return fn(int $price): int => (int)($price * (1 + $taxRate));
}

/**
 * 割引を適用する関数を生成
 *
 * @param int $discountPercent 割引率（%）
 * @return callable 割引後の価格を計算する関数
 */
function createDiscountCalculator(int $discountPercent): callable
{
    return function (int $price) use ($discountPercent): int {
        $discount = ($price * $discountPercent) / 100;
        return $price - (int)$discount;
    };
}

// 日本の消費税計算関数を生成
$addJapanTax = createTaxCalculator(0.10);

// 20%割引計算関数を生成
$apply20Discount = createDiscountCalculator(20);

$originalPrice = 10000;
$discounted = $apply20Discount($originalPrice);
$finalPrice = $addJapanTax($discounted);

echo "元の価格: ¥" . number_format($originalPrice) . PHP_EOL;
echo "20%割引後: ¥" . number_format($discounted) . PHP_EOL;
echo "税込価格: ¥" . number_format($finalPrice) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 10. 実用例：配列操作の高度な使い方
// ============================================================

echo "【10. 実用例：配列操作の高度な使い方】" . PHP_EOL;

$students = [
    ['name' => '山田太郎', 'score' => 85, 'subject' => '数学'],
    ['name' => '佐藤花子', 'score' => 92, 'subject' => '英語'],
    ['name' => '鈴木一郎', 'score' => 78, 'subject' => '数学'],
    ['name' => '田中美咲', 'score' => 88, 'subject' => '英語'],
];

// 80点以上の学生のみフィルタ
$highScorers = array_filter($students, fn($s) => $s['score'] >= 80);

echo "80点以上の学生:" . PHP_EOL;
foreach ($highScorers as $student) {
    echo "  {$student['name']}: {$student['score']}点（{$student['subject']}）" . PHP_EOL;
}

echo PHP_EOL;

// 名前のみを抽出
$names = array_map(fn($s) => $s['name'], $students);
echo "学生名: " . implode(', ', $names) . PHP_EOL;

// 平均点を計算
$totalScore = array_reduce($students, fn($carry, $s) => $carry + $s['score'], 0);
$average = $totalScore / count($students);
echo "平均点: " . round($average, 2) . "点" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 11. 型宣言の組み合わせ
// ============================================================

echo "【11. 型宣言の組み合わせ】" . PHP_EOL;

/**
 * 値を処理する（複数の型を受け入れる）
 *
 * @param int|float|string $value 処理する値
 * @param bool $format フォーマットするか
 * @return string 処理結果
 */
function processValue(int|float|string $value, bool $format = false): string
{
    $result = match (true) {
        is_int($value) => "整数: {$value}",
        is_float($value) => "浮動小数: {$value}",
        is_string($value) => "文字列: {$value}",
        default => "不明な型",
    };

    if ($format && (is_int($value) || is_float($value))) {
        $result .= " (フォーマット: " . number_format((float)$value, 2) . ")";
    }

    return $result;
}

echo processValue(123) . PHP_EOL;
echo processValue(123.456, true) . PHP_EOL;
echo processValue("Hello") . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 学習のポイント
// ============================================================

echo "【学習のポイント】" . PHP_EOL;
echo "1. デフォルト引数で柔軟な関数を作成" . PHP_EOL;
echo "2. 可変長引数（...）で任意の数の引数を受け取る" . PHP_EOL;
echo "3. 名前付き引数（PHP 8）で可読性向上" . PHP_EOL;
echo "4. アロー関数（fn）で簡潔な記述" . PHP_EOL;
echo "5. 無名関数（クロージャ）でコールバック処理" . PHP_EOL;
echo "6. 高階関数で関数を返す関数を作成" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.4: 関数の高度な機能 完了 ===" . PHP_EOL;
