<?php

declare(strict_types=1);

/**
 * Phase 1.5: 配列の基礎
 *
 * このファイルでは、PHPの配列の基本操作を学習します。
 *
 * 学習内容:
 * 1. インデックス配列の作成と操作
 * 2. 連想配列の作成と操作
 * 3. 多次元配列
 * 4. 配列の基本操作（追加、削除、更新）
 * 5. 配列の走査方法
 */

echo "=== 1. インデックス配列 ===" . PHP_EOL;

// インデックス配列の作成
$fruits = ["りんご", "バナナ", "オレンジ"];
echo "配列: " . print_r($fruits, true) . PHP_EOL;

// 配列の要素にアクセス
echo "最初の果物: {$fruits[0]}" . PHP_EOL;
echo "2番目の果物: {$fruits[1]}" . PHP_EOL;

// 配列の要素数
echo "配列の要素数: " . count($fruits) . PHP_EOL;

// 配列の最後に要素を追加
$fruits[] = "ぶどう";
echo "追加後: " . print_r($fruits, true) . PHP_EOL;

// 配列の要素を更新
$fruits[0] = "青りんご";
echo "更新後: " . print_r($fruits, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 2. 連想配列 ===" . PHP_EOL;

// 連想配列の作成
$user = [
    "name" => "山田太郎",
    "email" => "yamada@example.com",
    "age" => 28,
    "active" => true,
];

echo "ユーザー情報: " . print_r($user, true) . PHP_EOL;

// 連想配列の要素にアクセス
echo "名前: {$user['name']}" . PHP_EOL;
echo "メールアドレス: {$user['email']}" . PHP_EOL;
echo "年齢: {$user['age']}" . PHP_EOL;

// 新しいキーと値のペアを追加
$user['city'] = "東京";
echo "追加後: " . print_r($user, true) . PHP_EOL;

// 連想配列の更新
$user['age'] = 29;
echo "年齢を更新: {$user['age']}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 3. 多次元配列 ===" . PHP_EOL;

// 多次元配列の作成
$users = [
    [
        "name" => "山田太郎",
        "email" => "yamada@example.com",
        "age" => 28,
    ],
    [
        "name" => "佐藤花子",
        "email" => "sato@example.com",
        "age" => 25,
    ],
    [
        "name" => "鈴木一郎",
        "email" => "suzuki@example.com",
        "age" => 32,
    ],
];

echo "ユーザーリスト: " . print_r($users, true) . PHP_EOL;

// 多次元配列の要素にアクセス
echo "最初のユーザー: {$users[0]['name']}" . PHP_EOL;
echo "2番目のユーザーのメール: {$users[1]['email']}" . PHP_EOL;

// 多次元配列の走査
echo PHP_EOL . "すべてのユーザー:" . PHP_EOL;
foreach ($users as $index => $user) {
    $number = $index + 1;
    echo "{$number}. {$user['name']} ({$user['email']}) - {$user['age']}歳" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================

echo "=== 4. 配列の基本操作 ===" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5];
echo "元の配列: " . print_r($numbers, true) . PHP_EOL;

// array_push() - 配列の末尾に要素を追加
array_push($numbers, 6, 7);
echo "array_push()後: " . print_r($numbers, true) . PHP_EOL;

// array_pop() - 配列の末尾から要素を削除して返す
$lastElement = array_pop($numbers);
echo "削除された要素: {$lastElement}" . PHP_EOL;
echo "array_pop()後: " . print_r($numbers, true) . PHP_EOL;

// array_unshift() - 配列の先頭に要素を追加
array_unshift($numbers, 0, -1);
echo "array_unshift()後: " . print_r($numbers, true) . PHP_EOL;

// array_shift() - 配列の先頭から要素を削除して返す
$firstElement = array_shift($numbers);
echo "削除された要素: {$firstElement}" . PHP_EOL;
echo "array_shift()後: " . print_r($numbers, true) . PHP_EOL;

// unset() - 指定したインデックスの要素を削除
unset($numbers[2]);
echo "unset(\$numbers[2])後: " . print_r($numbers, true) . PHP_EOL;

// array_values() - インデックスを再作成
$numbers = array_values($numbers);
echo "array_values()後: " . print_r($numbers, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 5. 配列の走査 ===" . PHP_EOL;

$colors = ["red" => "赤", "green" => "緑", "blue" => "青"];

// foreach でキーと値を取得
echo "色の一覧:" . PHP_EOL;
foreach ($colors as $key => $value) {
    echo "{$key} => {$value}" . PHP_EOL;
}

echo PHP_EOL;

// 値のみを取得
echo "値のみ:" . PHP_EOL;
foreach ($colors as $value) {
    echo "- {$value}" . PHP_EOL;
}

echo PHP_EOL;

// インデックス配列の走査
$numbers = [10, 20, 30, 40, 50];

echo "インデックスと値:" . PHP_EOL;
foreach ($numbers as $index => $value) {
    echo "[{$index}] = {$value}" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================

echo "=== 6. 配列の存在チェック ===" . PHP_EOL;

$person = [
    "name" => "山田太郎",
    "email" => "yamada@example.com",
    "age" => 28,
];

// isset() - キーの存在とnullでないかをチェック
echo "nameが存在する: " . var_export(isset($person['name']), true) . PHP_EOL;
echo "phoneが存在する: " . var_export(isset($person['phone']), true) . PHP_EOL;

// array_key_exists() - キーの存在のみをチェック（nullでも存在とみなす）
echo "nameキーが存在する: " . var_export(array_key_exists('name', $person), true) . PHP_EOL;
echo "phoneキーが存在する: " . var_export(array_key_exists('phone', $person), true) . PHP_EOL;

// in_array() - 値の存在をチェック
$fruits = ["りんご", "バナナ", "オレンジ"];
echo "りんごが存在する: " . var_export(in_array("りんご", $fruits), true) . PHP_EOL;
echo "ぶどうが存在する: " . var_export(in_array("ぶどう", $fruits), true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 7. 配列の結合と分割 ===" . PHP_EOL;

// array_merge() - 配列を結合
$array1 = [1, 2, 3];
$array2 = [4, 5, 6];
$merged = array_merge($array1, $array2);
echo "array_merge: " . print_r($merged, true) . PHP_EOL;

// スプレッド演算子（...）による結合（PHP 7.4+）
$merged2 = [...$array1, ...$array2];
echo "スプレッド演算子: " . print_r($merged2, true) . PHP_EOL;

// 連想配列の結合
$user1 = ["name" => "山田", "age" => 28];
$user2 = ["email" => "yamada@example.com", "age" => 30];
$mergedUser = array_merge($user1, $user2);
echo "連想配列の結合（同じキーは上書き）: " . print_r($mergedUser, true) . PHP_EOL;

// array_slice() - 配列の一部を取得
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$sliced = array_slice($numbers, 2, 4);
echo "array_slice(\$numbers, 2, 4): " . print_r($sliced, true) . PHP_EOL;

// array_chunk() - 配列を指定サイズのチャンクに分割
$chunked = array_chunk($numbers, 3);
echo "array_chunk(\$numbers, 3): " . print_r($chunked, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 8. 配列のソート ===" . PHP_EOL;

// sort() - インデックス配列を昇順にソート（キーは再割り当てされる）
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
sort($numbers);
echo "sort(): " . print_r($numbers, true) . PHP_EOL;

// rsort() - インデックス配列を降順にソート
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
rsort($numbers);
echo "rsort(): " . print_r($numbers, true) . PHP_EOL;

// asort() - 連想配列を値で昇順にソート（キーを保持）
$scores = ["太郎" => 85, "花子" => 92, "一郎" => 78];
asort($scores);
echo "asort(): " . print_r($scores, true) . PHP_EOL;

// arsort() - 連想配列を値で降順にソート（キーを保持）
$scores = ["太郎" => 85, "花子" => 92, "一郎" => 78];
arsort($scores);
echo "arsort(): " . print_r($scores, true) . PHP_EOL;

// ksort() - 連想配列をキーで昇順にソート
$scores = ["太郎" => 85, "花子" => 92, "一郎" => 78];
ksort($scores);
echo "ksort(): " . print_r($scores, true) . PHP_EOL;

// krsort() - 連想配列をキーで降順にソート
$scores = ["太郎" => 85, "花子" => 92, "一郎" => 78];
krsort($scores);
echo "krsort(): " . print_r($scores, true) . PHP_EOL;

// usort() - カスタム比較関数を使ってソート
$users = [
    ["name" => "山田", "age" => 28],
    ["name" => "佐藤", "age" => 25],
    ["name" => "鈴木", "age" => 32],
];

usort($users, fn($a, $b) => $a['age'] <=> $b['age']);
echo "usort() - 年齢で昇順: " . print_r($users, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 9. 配列の情報取得 ===" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5];

// count() / sizeof() - 配列の要素数を取得
echo "要素数: " . count($numbers) . PHP_EOL;

// array_sum() - 配列の合計値を計算
echo "合計: " . array_sum($numbers) . PHP_EOL;

// array_product() - 配列の積を計算
echo "積: " . array_product($numbers) . PHP_EOL;

// max() / min() - 最大値/最小値を取得
echo "最大値: " . max($numbers) . PHP_EOL;
echo "最小値: " . min($numbers) . PHP_EOL;

// array_keys() - 配列のすべてのキーを取得
$person = ["name" => "山田", "age" => 28, "email" => "yamada@example.com"];
$keys = array_keys($person);
echo "キー一覧: " . print_r($keys, true) . PHP_EOL;

// array_values() - 配列のすべての値を取得
$values = array_values($person);
echo "値一覧: " . print_r($values, true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 10. 配列の型判定 ===" . PHP_EOL;

$indexedArray = [1, 2, 3];
$associativeArray = ["name" => "山田", "age" => 28];

/**
 * 配列がインデックス配列かどうかを判定する
 *
 * @param array<mixed> $array 判定する配列
 * @return bool インデックス配列の場合true
 */
function isIndexedArray(array $array): bool
{
    if (count($array) === 0) {
        return true;
    }
    return array_keys($array) === range(0, count($array) - 1);
}

/**
 * 配列が連想配列かどうかを判定する
 *
 * @param array<mixed> $array 判定する配列
 * @return bool 連想配列の場合true
 */
function isAssociativeArray(array $array): bool
{
    if (count($array) === 0) {
        return false;
    }
    return array_keys($array) !== range(0, count($array) - 1);
}

echo "インデックス配列?" . PHP_EOL;
echo "[1, 2, 3]: " . var_export(isIndexedArray($indexedArray), true) . PHP_EOL;
echo "連想配列: " . var_export(isIndexedArray($associativeArray), true) . PHP_EOL;

echo PHP_EOL . "連想配列?" . PHP_EOL;
echo "[1, 2, 3]: " . var_export(isAssociativeArray($indexedArray), true) . PHP_EOL;
echo "連想配列: " . var_export(isAssociativeArray($associativeArray), true) . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 11. compact() と extract() ===" . PHP_EOL;

// compact() - 変数名から連想配列を作成
$name = "山田太郎";
$age = 28;
$email = "yamada@example.com";

$user = compact('name', 'age', 'email');
echo "compact()で作成した配列: " . print_r($user, true) . PHP_EOL;

// extract() - 連想配列から変数を作成（注意: 使用は慎重に）
$data = ["city" => "東京", "country" => "日本"];
extract($data);

/** @var string $city */
/** @var string $country */
echo "extract()で作成された変数: city = {$city}, country = {$country}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 12. list() と短縮構文 ===" . PHP_EOL;

// list() - 配列を変数に展開
$fruits = ["りんご", "バナナ", "オレンジ"];
list($first, $second, $third) = $fruits;
echo "list()で展開: {$first}, {$second}, {$third}" . PHP_EOL;

// 短縮構文（[]）
[$first, $second, $third] = $fruits;
echo "[]で展開: {$first}, {$second}, {$third}" . PHP_EOL;

// 一部の要素のみ取得
[$first, , $third] = $fruits;
echo "一部のみ: {$first}, {$third}" . PHP_EOL;

// 連想配列の展開
$user = ["name" => "山田太郎", "email" => "yamada@example.com", "age" => 28];
["name" => $userName, "email" => $userEmail] = $user;
echo "連想配列の展開: {$userName}, {$userEmail}" . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "配列の基礎を学習しました！" . PHP_EOL;
echo "次は 11_arrays_functions.php で配列関数の応用を学習します。" . PHP_EOL;
