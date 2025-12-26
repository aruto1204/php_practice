<?php

declare(strict_types=1);

/**
 * Phase 1.2: 代入演算子とnull合体演算子
 *
 * このファイルでは、以下について学習します：
 * - 代入演算子とその短縮形
 * - null合体演算子（??）
 * - null合体代入演算子（??=）
 */

echo "=== Phase 1.2: 代入演算子とnull合体演算子 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 1. 基本的な代入演算子
// ============================================================

echo "【1. 基本的な代入演算子】" . PHP_EOL;

$x = 10;
echo "x = {$x}" . PHP_EOL;

// 代入は式として評価される
$y = ($x = 20);  // $xに20を代入し、その値を$yにも代入
echo "x = {$x}, y = {$y}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 2. 複合代入演算子（Compound Assignment Operators）
// ============================================================

echo "【2. 複合代入演算子】" . PHP_EOL;

$number = 10;
echo "初期値: number = {$number}" . PHP_EOL;

// 加算代入
$number += 5;  // $number = $number + 5;
echo "number += 5  → {$number}" . PHP_EOL;

// 減算代入
$number -= 3;  // $number = $number - 3;
echo "number -= 3  → {$number}" . PHP_EOL;

// 乗算代入
$number *= 2;  // $number = $number * 2;
echo "number *= 2  → {$number}" . PHP_EOL;

// 除算代入
$number /= 4;  // $number = $number / 4;
echo "number /= 4  → {$number}" . PHP_EOL;

// 剰余代入
$number = 17;
$number %= 5;  // $number = $number % 5;
echo "17 %= 5     → {$number}" . PHP_EOL;

echo PHP_EOL;

// 文字列連結代入
$message = "Hello";
echo "初期値: message = \"{$message}\"" . PHP_EOL;

$message .= ", World!";  // $message = $message . ", World!";
echo "message .= \", World!\" → \"{$message}\"" . PHP_EOL;

echo PHP_EOL;

// 実用例: カウンターのインクリメント
$pageViews = 100;
echo "【実用例: ページビューカウンター】" . PHP_EOL;
echo "現在のページビュー: {$pageViews}" . PHP_EOL;

$pageViews += 1;  // 1回閲覧された
echo "閲覧後: {$pageViews}" . PHP_EOL;

echo PHP_EOL;

// 実用例: ポイントの加算
$userPoints = 500;
$earnedPoints = 150;

echo "【実用例: ユーザーポイント】" . PHP_EOL;
echo "現在のポイント: {$userPoints}" . PHP_EOL;
echo "獲得ポイント: {$earnedPoints}" . PHP_EOL;

$userPoints += $earnedPoints;
echo "合計ポイント: {$userPoints}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 3. null合体演算子（Null Coalescing Operator - ??）
// ============================================================

echo "【3. null合体演算子（??）】" . PHP_EOL;

// 基本的な使い方
$username = null;
$defaultUsername = "ゲスト";

$displayName = $username ?? $defaultUsername;
echo "username = null の場合: {$displayName}" . PHP_EOL;  // "ゲスト"

$username = "山田太郎";
$displayName = $username ?? $defaultUsername;
echo "username = \"山田太郎\" の場合: {$displayName}" . PHP_EOL;  // "山田太郎"

echo PHP_EOL;

// 従来の書き方との比較
echo "【従来の書き方との比較】" . PHP_EOL;

$value = null;

// 従来の書き方（冗長）
$result1 = isset($value) ? $value : "デフォルト";
echo "isset() + 三項演算子: {$result1}" . PHP_EOL;

// null合体演算子（簡潔）
$result2 = $value ?? "デフォルト";
echo "null合体演算子: {$result2}" . PHP_EOL;

echo PHP_EOL;

// チェーン（連鎖）
echo "【null合体演算子のチェーン】" . PHP_EOL;

$primary = null;
$secondary = null;
$tertiary = "最終的なデフォルト値";

$result = $primary ?? $secondary ?? $tertiary ?? "緊急デフォルト";
echo "結果: {$result}" . PHP_EOL;

echo PHP_EOL;

// 実用例1: フォームデータの取得
echo "【実用例1: フォームデータのデフォルト値】" . PHP_EOL;

// $_POSTをシミュレート
$_POST = [
    'name' => '山田太郎',
    // 'email' は未設定
];

$name = $_POST['name'] ?? '名無し';
$email = $_POST['email'] ?? 'default@example.com';
$age = $_POST['age'] ?? 18;

echo "名前: {$name}" . PHP_EOL;
echo "メール: {$email}" . PHP_EOL;
echo "年齢: {$age}" . PHP_EOL;

echo PHP_EOL;

// 実用例2: 設定値の取得
echo "【実用例2: 設定値のデフォルト】" . PHP_EOL;

/**
 * 設定値を取得する
 *
 * @param array<string, mixed> $config 設定配列
 * @return array<string, mixed> マージされた設定
 */
function getConfig(array $config): array
{
    return [
        'theme' => $config['theme'] ?? 'light',
        'language' => $config['language'] ?? 'ja',
        'itemsPerPage' => $config['itemsPerPage'] ?? 10,
        'enableNotifications' => $config['enableNotifications'] ?? true,
    ];
}

$userConfig = [
    'theme' => 'dark',
    // その他の設定は未指定
];

$finalConfig = getConfig($userConfig);
echo "テーマ: {$finalConfig['theme']}" . PHP_EOL;
echo "言語: {$finalConfig['language']}" . PHP_EOL;
echo "ページあたりアイテム数: {$finalConfig['itemsPerPage']}" . PHP_EOL;
echo "通知: " . ($finalConfig['enableNotifications'] ? '有効' : '無効') . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 4. null合体代入演算子（??=）（PHP 7.4+）
// ============================================================

echo "【4. null合体代入演算子（??=）】" . PHP_EOL;

$count = null;
echo "初期値: count = " . var_export($count, true) . PHP_EOL;

// $countがnullの場合のみ、0を代入
$count ??= 0;
echo "$count ??= 0 → count = {$count}" . PHP_EOL;

// すでに値がある場合は代入されない
$count ??= 100;
echo "$count ??= 100 → count = {$count}" . PHP_EOL;  // 0のまま

echo PHP_EOL;

// 従来の書き方との比較
echo "【従来の書き方との比較】" . PHP_EOL;

$value1 = null;

// 従来の書き方
if (!isset($value1)) {
    $value1 = "デフォルト";
}
echo "isset() + if文: {$value1}" . PHP_EOL;

// null合体代入演算子
$value2 = null;
$value2 ??= "デフォルト";
echo "??= 演算子: {$value2}" . PHP_EOL;

echo PHP_EOL;

// 実用例: キャッシュの初期化
echo "【実用例: キャッシュの初期化】" . PHP_EOL;

/**
 * ユーザーキャッシュを管理するクラス
 */
class UserCache
{
    /** @var array<int, array<string, mixed>> */
    private array $cache = [];

    /**
     * ユーザー情報を取得（キャッシュがあればキャッシュから）
     *
     * @param int $userId ユーザーID
     * @return array<string, mixed> ユーザー情報
     */
    public function getUser(int $userId): array
    {
        // キャッシュがなければデータベースから取得（ここではシミュレート）
        $this->cache[$userId] ??= $this->fetchUserFromDatabase($userId);

        return $this->cache[$userId];
    }

    /**
     * データベースからユーザー情報を取得（シミュレート）
     *
     * @param int $userId ユーザーID
     * @return array<string, mixed> ユーザー情報
     */
    private function fetchUserFromDatabase(int $userId): array
    {
        echo "  → データベースからユーザーID {$userId} を取得" . PHP_EOL;
        return [
            'id' => $userId,
            'name' => "ユーザー{$userId}",
            'email' => "user{$userId}@example.com",
        ];
    }
}

$cache = new UserCache();

echo "1回目の取得（DBから取得）:" . PHP_EOL;
$user1 = $cache->getUser(1);
echo "  ユーザー名: {$user1['name']}" . PHP_EOL;

echo "2回目の取得（キャッシュから取得）:" . PHP_EOL;
$user2 = $cache->getUser(1);
echo "  ユーザー名: {$user2['name']}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 5. null合体演算子 vs 論理OR
// ============================================================

echo "【5. null合体演算子 vs 論理OR】" . PHP_EOL;

$value = 0;

// 論理OR（||）は0をfalseとして扱う
$result1 = $value || "デフォルト";
echo "0 || \"デフォルト\": " . var_export($result1, true) . PHP_EOL;  // "デフォルト"

// null合体演算子（??）はnullの場合のみ
$result2 = $value ?? "デフォルト";
echo "0 ?? \"デフォルト\": " . var_export($result2, true) . PHP_EOL;  // 0

echo PHP_EOL;

$emptyString = "";

$result3 = $emptyString || "デフォルト";
echo "\"\" || \"デフォルト\": " . var_export($result3, true) . PHP_EOL;  // "デフォルト"

$result4 = $emptyString ?? "デフォルト";
echo "\"\" ?? \"デフォルト\": " . var_export($result4, true) . PHP_EOL;  // ""

echo PHP_EOL;
echo "💡 重要: null合体演算子は null のみをチェック、論理ORは falsy な値すべてをチェック" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 6. 実用的な組み合わせ例
// ============================================================

echo "【6. 実用的な組み合わせ例】" . PHP_EOL . PHP_EOL;

/**
 * ユーザープロフィールを取得
 *
 * @param array<string, mixed> $data ユーザーデータ
 * @return array<string, mixed> 完全なプロフィール
 */
function getUserProfile(array $data): array
{
    return [
        'name' => $data['name'] ?? '名無し',
        'email' => $data['email'] ?? 'unknown@example.com',
        'age' => $data['age'] ?? null,
        'bio' => $data['bio'] ?? '自己紹介なし',
        'avatar' => $data['avatar'] ?? '/images/default-avatar.png',
        'joinedAt' => $data['joinedAt'] ?? date('Y-m-d'),
    ];
}

$userData = [
    'name' => '田中花子',
    'email' => 'hanako@example.com',
    // その他のフィールドは未設定
];

$profile = getUserProfile($userData);

echo "プロフィール情報:" . PHP_EOL;
foreach ($profile as $key => $value) {
    $displayValue = $value ?? '(未設定)';
    echo "  {$key}: {$displayValue}" . PHP_EOL;
}

echo PHP_EOL;

/**
 * ページネーション設定を取得
 *
 * @param array<string, mixed> $params リクエストパラメータ
 * @return array<string, int> ページネーション設定
 */
function getPaginationSettings(array $params): array
{
    $page = $params['page'] ?? 1;
    $perPage = $params['per_page'] ?? 20;

    // 負の値やゼロを防ぐ
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));  // 最大100件まで

    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];
}

$params = ['page' => 3];  // per_pageは未指定
$pagination = getPaginationSettings($params);

echo "ページネーション設定:" . PHP_EOL;
echo "  ページ: {$pagination['page']}" . PHP_EOL;
echo "  1ページあたり: {$pagination['per_page']}件" . PHP_EOL;
echo "  オフセット: {$pagination['offset']}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 学習のポイント
// ============================================================

echo "【学習のポイント】" . PHP_EOL;
echo "1. 複合代入演算子（+=, -=, *=, /=, .=）で簡潔なコードを書く" . PHP_EOL;
echo "2. null合体演算子（??）でnullチェックとデフォルト値設定を簡潔に" . PHP_EOL;
echo "3. null合体代入演算子（??=）で初期化処理を簡潔に" . PHP_EOL;
echo "4. ?? と || の違いを理解する（nullのみ vs falsyな値すべて）" . PHP_EOL;
echo "5. チェーンで複数のフォールバック値を設定できる" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.2: 代入演算子とnull合体演算子 完了 ===" . PHP_EOL;
