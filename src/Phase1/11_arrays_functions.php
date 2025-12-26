<?php

declare(strict_types=1);

/**
 * Phase 1.5: 配列関数の応用
 *
 * このファイルでは、PHPの配列関数（map, filter, reduce など）を学習します。
 *
 * 学習内容:
 * 1. array_map() - 配列の各要素に関数を適用
 * 2. array_filter() - 条件に一致する要素を抽出
 * 3. array_reduce() - 配列を単一の値に集約
 * 4. array_walk() - 配列の各要素に関数を適用（参照渡し）
 * 5. その他の便利な配列関数
 */

echo "=== 1. array_map() - 配列の各要素に関数を適用 ===" . PHP_EOL;

// 数値を2倍にする
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($n) => $n * 2, $numbers);
echo "元の配列: " . print_r($numbers, true);
echo "2倍にした配列: " . print_r($doubled, true) . PHP_EOL;

// 文字列を大文字に変換
$words = ["hello", "world", "php"];
$uppercase = array_map(fn($word) => strtoupper($word), $words);
echo "大文字変換: " . print_r($uppercase, true) . PHP_EOL;

// 複数の配列を組み合わせる
$firstNames = ["太郎", "花子", "一郎"];
$lastNames = ["山田", "佐藤", "鈴木"];
$fullNames = array_map(
    fn($first, $last) => "{$last} {$first}",
    $firstNames,
    $lastNames
);
echo "フルネーム: " . print_r($fullNames, true) . PHP_EOL;

// 配列の各要素をオブジェクト化
$users = [
    ["name" => "山田太郎", "age" => 28],
    ["name" => "佐藤花子", "age" => 25],
    ["name" => "鈴木一郎", "age" => 32],
];

$userSummaries = array_map(
    fn($user) => "{$user['name']} ({$user['age']}歳)",
    $users
);
echo "ユーザーサマリー: " . print_r($userSummaries, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 2. array_filter() - 条件に一致する要素を抽出 ===" . PHP_EOL;

// 偶数のみを抽出
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$evenNumbers = array_filter($numbers, fn($n) => $n % 2 === 0);
echo "偶数のみ: " . print_r($evenNumbers, true) . PHP_EOL;

// 空でない文字列のみを抽出
$words = ["hello", "", "world", "", "php"];
$nonEmpty = array_filter($words);  // コールバックなしの場合、falsy値を除外
echo "空でない文字列: " . print_r($nonEmpty, true) . PHP_EOL;

// 明示的にコールバックを使用
$nonEmpty2 = array_filter($words, fn($word) => $word !== "");
echo "空でない文字列（明示的）: " . print_r($nonEmpty2, true) . PHP_EOL;

// 年齢が30歳未満のユーザーを抽出
$users = [
    ["name" => "山田太郎", "age" => 28],
    ["name" => "佐藤花子", "age" => 25],
    ["name" => "鈴木一郎", "age" => 32],
    ["name" => "田中次郎", "age" => 29],
];

$youngUsers = array_filter($users, fn($user) => $user['age'] < 30);
echo "30歳未満のユーザー: " . print_r($youngUsers, true) . PHP_EOL;

// キーと値の両方を使用してフィルタリング
$scores = ["太郎" => 85, "花子" => 92, "一郎" => 78, "次郎" => 88];
$highScorers = array_filter(
    $scores,
    fn($score, $name) => $score >= 85,
    ARRAY_FILTER_USE_BOTH
);
echo "85点以上: " . print_r($highScorers, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 3. array_reduce() - 配列を単一の値に集約 ===" . PHP_EOL;

// 合計を計算
$numbers = [1, 2, 3, 4, 5];
$sum = array_reduce($numbers, fn($carry, $item) => $carry + $item, 0);
echo "合計: {$sum}" . PHP_EOL;

// 積を計算
$product = array_reduce($numbers, fn($carry, $item) => $carry * $item, 1);
echo "積: {$product}" . PHP_EOL;

// 最大値を見つける
$max = array_reduce($numbers, fn($carry, $item) => max($carry, $item), PHP_INT_MIN);
echo "最大値: {$max}" . PHP_EOL;

// 文字列を結合
$words = ["Hello", "World", "from", "PHP"];
$sentence = array_reduce($words, fn($carry, $word) => $carry . " " . $word, "");
echo "結合された文字列:{$sentence}" . PHP_EOL;

// 連想配列を作成
$fruits = ["apple", "banana", "orange"];
$fruitMap = array_reduce(
    $fruits,
    function ($carry, $fruit) {
        $carry[$fruit] = strlen($fruit);
        return $carry;
    },
    []
);
echo "果物と文字数: " . print_r($fruitMap, true) . PHP_EOL;

// グループ化
$users = [
    ["name" => "山田", "age" => 28, "gender" => "male"],
    ["name" => "佐藤", "age" => 25, "gender" => "female"],
    ["name" => "鈴木", "age" => 32, "gender" => "male"],
    ["name" => "田中", "age" => 29, "gender" => "female"],
];

$groupedByGender = array_reduce(
    $users,
    function ($carry, $user) {
        $carry[$user['gender']][] = $user['name'];
        return $carry;
    },
    []
);
echo "性別でグループ化: " . print_r($groupedByGender, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 4. array_walk() - 配列の各要素に関数を適用（参照渡し） ===" . PHP_EOL;

// 配列の各要素を2倍にする（元の配列を変更）
$numbers = [1, 2, 3, 4, 5];
array_walk($numbers, function (&$value) {
    $value *= 2;
});
echo "2倍にした配列: " . print_r($numbers, true) . PHP_EOL;

// 連想配列の値を変更
$prices = ["apple" => 100, "banana" => 80, "orange" => 120];
array_walk($prices, function (&$value, $key) {
    $value = (int)($value * 1.1);  // 10%値上げ
    echo "{$key}の新価格: {$value}円" . PHP_EOL;
});
echo "値上げ後: " . print_r($prices, true) . PHP_EOL;

// 外部変数を使用
$tax = 1.1;
$pricesWithTax = ["apple" => 100, "banana" => 80, "orange" => 120];
array_walk($pricesWithTax, function (&$value) use ($tax) {
    $value = (int)($value * $tax);
});
echo "税込価格: " . print_r($pricesWithTax, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 5. array_column() - 多次元配列から特定のカラムを抽出 ===" . PHP_EOL;

$users = [
    ["name" => "山田太郎", "email" => "yamada@example.com", "age" => 28],
    ["name" => "佐藤花子", "email" => "sato@example.com", "age" => 25],
    ["name" => "鈴木一郎", "email" => "suzuki@example.com", "age" => 32],
];

// 名前のみを抽出
$names = array_column($users, 'name');
echo "名前一覧: " . print_r($names, true) . PHP_EOL;

// メールアドレスのみを抽出
$emails = array_column($users, 'email');
echo "メールアドレス一覧: " . print_r($emails, true) . PHP_EOL;

// メールアドレスをキー、名前を値とした連想配列を作成
$emailToName = array_column($users, 'name', 'email');
echo "メールアドレスをキーとした連想配列: " . print_r($emailToName, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 6. array_unique() - 重複を削除 ===" . PHP_EOL;

$numbers = [1, 2, 2, 3, 3, 3, 4, 4, 5];
$unique = array_unique($numbers);
echo "重複削除: " . print_r($unique, true) . PHP_EOL;

// 文字列の重複削除
$words = ["apple", "banana", "apple", "orange", "banana"];
$uniqueWords = array_unique($words);
echo "重複削除（文字列）: " . print_r($uniqueWords, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 7. array_reverse() - 配列を逆順にする ===" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5];
$reversed = array_reverse($numbers);
echo "逆順: " . print_r($reversed, true) . PHP_EOL;

// キーを保持
$associative = ["a" => 1, "b" => 2, "c" => 3];
$reversedWithKeys = array_reverse($associative, true);
echo "逆順（キー保持）: " . print_r($reversedWithKeys, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 8. array_flip() - キーと値を入れ替える ===" . PHP_EOL;

$fruits = ["a" => "apple", "b" => "banana", "c" => "orange"];
$flipped = array_flip($fruits);
echo "キーと値を入れ替え: " . print_r($flipped, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 9. array_intersect() / array_diff() - 配列の差集合と共通部分 ===" . PHP_EOL;

$array1 = [1, 2, 3, 4, 5];
$array2 = [3, 4, 5, 6, 7];

// 共通部分（値のみ比較）
$intersection = array_intersect($array1, $array2);
echo "共通部分: " . print_r($intersection, true) . PHP_EOL;

// 差集合（$array1にあって$array2にない要素）
$difference = array_diff($array1, $array2);
echo "差集合: " . print_r($difference, true) . PHP_EOL;

// 連想配列の共通部分（キーと値の両方を比較）
$user1 = ["name" => "山田", "age" => 28, "city" => "東京"];
$user2 = ["name" => "山田", "age" => 30, "email" => "yamada@example.com"];

$commonData = array_intersect($user1, $user2);
echo "連想配列の共通部分: " . print_r($commonData, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 10. array_search() / in_array() - 値の検索 ===" . PHP_EOL;

$fruits = ["apple", "banana", "orange", "grape"];

// in_array() - 値が存在するかチェック
echo "bananaが存在する: " . var_export(in_array("banana", $fruits), true) . PHP_EOL;
echo "melonが存在する: " . var_export(in_array("melon", $fruits), true) . PHP_EOL;

// array_search() - 値が存在する場合、そのキーを返す
$key = array_search("orange", $fruits);
echo "orangeのキー: " . var_export($key, true) . PHP_EOL;

$key = array_search("melon", $fruits);
echo "melonのキー: " . var_export($key, true) . " (存在しない場合はfalse)" . PHP_EOL;

// 厳密な比較
$numbers = [1, 2, 3, 4, 5];
echo "文字列'3'が存在する（非厳密）: " . var_export(in_array("3", $numbers), true) . PHP_EOL;
echo "文字列'3'が存在する（厳密）: " . var_export(in_array("3", $numbers, true), true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 11. array_pad() - 配列を指定サイズまで埋める ===" . PHP_EOL;

$numbers = [1, 2, 3];
$padded = array_pad($numbers, 7, 0);
echo "7要素まで0で埋める: " . print_r($padded, true) . PHP_EOL;

// 負の値で先頭に追加
$paddedFront = array_pad($numbers, -7, 0);
echo "先頭に0を追加: " . print_r($paddedFront, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 12. array_fill() / array_fill_keys() - 配列を初期化 ===" . PHP_EOL;

// array_fill() - 同じ値で配列を埋める
$filled = array_fill(0, 5, "value");
echo "array_fill(0, 5, 'value'): " . print_r($filled, true) . PHP_EOL;

// array_fill_keys() - 指定したキーで配列を初期化
$keys = ["apple", "banana", "orange"];
$filledKeys = array_fill_keys($keys, 0);
echo "array_fill_keys(): " . print_r($filledKeys, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 13. array_combine() - 2つの配列からキーと値のペアを作成 ===" . PHP_EOL;

$keys = ["name", "email", "age"];
$values = ["山田太郎", "yamada@example.com", 28];
$combined = array_combine($keys, $values);
echo "array_combine(): " . print_r($combined, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 14. array_count_values() - 値の出現回数を数える ===" . PHP_EOL;

$fruits = ["apple", "banana", "apple", "orange", "banana", "apple"];
$counts = array_count_values($fruits);
echo "出現回数: " . print_r($counts, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 15. array_rand() - ランダムな要素を取得 ===" . PHP_EOL;

$fruits = ["apple", "banana", "orange", "grape", "melon"];

// 1つのランダムなキーを取得
$randomKey = array_rand($fruits);
echo "ランダムな果物: {$fruits[$randomKey]}" . PHP_EOL;

// 複数のランダムなキーを取得
$randomKeys = array_rand($fruits, 3);
echo "ランダムな3つの果物: ";
foreach ($randomKeys as $key) {
    echo "{$fruits[$key]} ";
}
echo PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 16. 実践例: メソッドチェーン風の処理 ===" . PHP_EOL;

/**
 * ユーザーデータを処理するクラス
 */
class UserCollection
{
    /**
     * @param array<int, array<string, mixed>> $users ユーザーデータの配列
     */
    public function __construct(private array $users)
    {
    }

    /**
     * 年齢でフィルタリング
     *
     * @param int $minAge 最小年齢
     * @return self
     */
    public function filterByAge(int $minAge): self
    {
        $this->users = array_filter($this->users, fn($user) => $user['age'] >= $minAge);
        return $this;
    }

    /**
     * 名前でソート
     *
     * @return self
     */
    public function sortByName(): self
    {
        usort($this->users, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $this;
    }

    /**
     * 名前のみを抽出
     *
     * @return array<int, string>
     */
    public function pluckNames(): array
    {
        return array_column($this->users, 'name');
    }

    /**
     * すべてのユーザーデータを取得
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->users;
    }
}

$users = [
    ["name" => "山田太郎", "age" => 28],
    ["name" => "佐藤花子", "age" => 25],
    ["name" => "鈴木一郎", "age" => 32],
    ["name" => "田中次郎", "age" => 22],
];

$collection = new UserCollection($users);
$names = $collection
    ->filterByAge(25)
    ->sortByName()
    ->pluckNames();

echo "25歳以上のユーザー（名前順）: " . print_r($names, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "配列関数の応用を学習しました！" . PHP_EOL;
echo "次は exercises/05_array_practice.php で配列操作の実践練習をします。" . PHP_EOL;
