<?php

declare(strict_types=1);

/**
 * Phase 1.2: 演算子の演習課題
 *
 * このファイルでは、演算子を使った実践的な演習を行います：
 * - 演習1: 四則演算計算機
 * - 演習2: 年齢判定システム
 * - 演習3: 割引計算システム
 * - 演習4: グレード判定システム
 * - 演習5: アクセス権限チェック
 */

echo "=== Phase 1.2: 演算子の演習課題 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 演習1: 四則演算計算機
// ============================================================

echo "【演習1: 四則演算計算機】" . PHP_EOL;

/**
 * 四則演算を実行する
 *
 * @param float $a 1つ目の数値
 * @param float $b 2つ目の数値
 * @param string $operator 演算子 (+, -, *, /)
 * @return float|string 計算結果、エラーの場合はエラーメッセージ
 */
function calculate(float $a, float $b, string $operator): float|string
{
    return match ($operator) {
        '+' => $a + $b,
        '-' => $a - $b,
        '*' => $a * $b,
        '/' => $b !== 0.0 ? $a / $b : "エラー: ゼロ除算はできません",
        default => "エラー: 無効な演算子です（+, -, *, / のいずれかを指定してください）",
    };
}

// テストケース
$testCases = [
    [10, 5, '+'],
    [10, 5, '-'],
    [10, 5, '*'],
    [10, 5, '/'],
    [10, 0, '/'],  // ゼロ除算
    [10, 5, '%'],  // 無効な演算子
];

foreach ($testCases as [$num1, $num2, $op]) {
    $result = calculate($num1, $num2, $op);
    echo "{$num1} {$op} {$num2} = {$result}" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 演習2: 年齢判定システム
// ============================================================

echo "【演習2: 年齢判定システム】" . PHP_EOL;

/**
 * 年齢から様々な判定を行う
 *
 * @param int $age 年齢
 * @return array<string, mixed> 判定結果
 */
function analyzeAge(int $age): array
{
    return [
        'age' => $age,
        'is_adult' => $age >= 18,
        'is_senior' => $age >= 65,
        'can_vote' => $age >= 18,
        'can_drink' => $age >= 20,
        'can_drive' => $age >= 18,
        'discount_rate' => match (true) {
            $age < 12 => 50,          // 子供割引 50%
            $age >= 65 => 30,         // シニア割引 30%
            $age >= 18 => 0,          // 一般 割引なし
            default => 20,            // 学生割引 20%
        },
        'category' => match (true) {
            $age < 0 => '無効な年齢',
            $age < 6 => '幼児',
            $age < 12 => '子供',
            $age < 18 => '青少年',
            $age < 65 => '成人',
            default => 'シニア',
        },
    ];
}

// テストケース
$ages = [5, 10, 15, 20, 30, 65, 70];

foreach ($ages as $age) {
    $result = analyzeAge($age);
    echo "{$age}歳: {$result['category']}、";
    echo "割引率{$result['discount_rate']}%、";
    echo $result['is_adult'] ? "成人" : "未成年";
    echo PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 演習3: 割引計算システム
// ============================================================

echo "【演習3: 割引計算システム】" . PHP_EOL;

/**
 * 購入金額に応じた割引後の価格を計算
 *
 * @param int $totalAmount 合計金額
 * @param bool $isMember 会員かどうか
 * @param string|null $couponCode クーポンコード
 * @return array<string, mixed> 計算結果
 */
function calculateDiscount(int $totalAmount, bool $isMember, ?string $couponCode = null): array
{
    // 基本割引（購入金額に応じて）
    $volumeDiscountRate = match (true) {
        $totalAmount >= 50000 => 15,
        $totalAmount >= 30000 => 10,
        $totalAmount >= 10000 => 5,
        default => 0,
    };

    // 会員割引
    $memberDiscountRate = $isMember ? 5 : 0;

    // クーポン割引
    $couponDiscountRate = match ($couponCode) {
        'WELCOME10' => 10,
        'SAVE20' => 20,
        'SPECIAL30' => 30,
        default => 0,
    };

    // 最大割引率を適用（重複適用はしない）
    $maxDiscountRate = max($volumeDiscountRate, $memberDiscountRate, $couponDiscountRate);

    // 割引額を計算
    $discountAmount = (int)(($totalAmount * $maxDiscountRate) / 100);

    // 最終金額
    $finalAmount = $totalAmount - $discountAmount;

    return [
        'original_amount' => $totalAmount,
        'discount_rate' => $maxDiscountRate,
        'discount_amount' => $discountAmount,
        'final_amount' => $finalAmount,
        'applied_discount' => match ($maxDiscountRate) {
            $volumeDiscountRate => "大量購入割引 {$maxDiscountRate}%",
            $memberDiscountRate => "会員割引 {$maxDiscountRate}%",
            $couponDiscountRate => "クーポン割引 {$maxDiscountRate}%",
            default => "割引なし",
        },
    ];
}

// テストケース
$orders = [
    [5000, false, null],           // 割引なし
    [15000, true, null],           // 会員割引 or 大量購入割引
    [35000, false, 'WELCOME10'],   // 大量購入割引 or クーポン
    [8000, false, 'SPECIAL30'],    // クーポン割引
];

foreach ($orders as [$amount, $member, $coupon]) {
    $result = calculateDiscount($amount, $member, $coupon);
    echo "購入金額: ¥" . number_format($result['original_amount']);
    echo ", {$result['applied_discount']}";
    echo " → 最終金額: ¥" . number_format($result['final_amount']);
    echo " (¥" . number_format($result['discount_amount']) . "引き)";
    echo PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 演習4: グレード判定システム
// ============================================================

echo "【演習4: グレード判定システム】" . PHP_EOL;

/**
 * 学生の成績を評価する
 *
 * @param string $name 学生名
 * @param int $math 数学の点数
 * @param int $english 英語の点数
 * @param int $science 理科の点数
 * @param int|null $attendance 出席率（%）
 * @return array<string, mixed> 成績評価
 */
function evaluateStudent(string $name, int $math, int $english, int $science, ?int $attendance = null): array
{
    $attendance ??= 100;  // デフォルトは100%

    $total = $math + $english + $science;
    $average = $total / 3;

    // グレード判定
    $grade = match (true) {
        $average >= 90 => 'S',
        $average >= 80 => 'A',
        $average >= 70 => 'B',
        $average >= 60 => 'C',
        default => 'F',
    };

    // 合格判定（平均60点以上 かつ 出席率80%以上）
    $isPassed = $average >= 60 && $attendance >= 80;

    // 優秀学生判定（平均85点以上 かつ 出席率95%以上）
    $isHonor = $average >= 85 && $attendance >= 95;

    // 警告判定（平均50点未満 または 出席率70%未満）
    $needsWarning = $average < 50 || $attendance < 70;

    return [
        'name' => $name,
        'math' => $math,
        'english' => $english,
        'science' => $science,
        'total' => $total,
        'average' => round($average, 2),
        'attendance' => $attendance,
        'grade' => $grade,
        'status' => match (true) {
            $isHonor => '優秀',
            $isPassed => '合格',
            $needsWarning => '要注意',
            default => '不合格',
        },
        'passed' => $isPassed,
    ];
}

// テストケース
$students = [
    ['山田太郎', 95, 90, 92, 98],
    ['佐藤花子', 75, 80, 70, 85],
    ['鈴木一郎', 55, 60, 58, 75],
    ['田中美咲', 45, 50, 48, 65],
];

echo "成績評価一覧:" . PHP_EOL;
echo str_repeat('-', 80) . PHP_EOL;

foreach ($students as $studentData) {
    $result = evaluateStudent(...$studentData);
    echo "{$result['name']}: ";
    echo "平均{$result['average']}点、";
    echo "グレード{$result['grade']}、";
    echo "出席率{$result['attendance']}%、";
    echo "判定「{$result['status']}」";
    echo PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 演習5: アクセス権限チェックシステム
// ============================================================

echo "【演習5: アクセス権限チェックシステム】" . PHP_EOL;

/**
 * ユーザーのアクセス権限をチェックする
 *
 * @param array<string, mixed> $user ユーザー情報
 * @param string $resource リソース名
 * @param string $action アクション（read, write, delete）
 * @return array<string, mixed> アクセス権限チェック結果
 */
function checkAccess(array $user, string $resource, string $action): array
{
    $isLoggedIn = $user['is_logged_in'] ?? false;
    $role = $user['role'] ?? 'guest';
    $isActive = $user['is_active'] ?? false;
    $isVerified = $user['is_verified'] ?? false;

    // ログインチェック
    if (!$isLoggedIn) {
        return [
            'allowed' => false,
            'reason' => 'ログインが必要です',
        ];
    }

    // アカウント状態チェック
    if (!$isActive) {
        return [
            'allowed' => false,
            'reason' => 'アカウントが無効化されています',
        ];
    }

    // メール認証チェック（writeとdelete操作の場合）
    if (($action === 'write' || $action === 'delete') && !$isVerified) {
        return [
            'allowed' => false,
            'reason' => 'メール認証が必要です',
        ];
    }

    // ロールベースのアクセス制御
    $allowed = match (true) {
        // 管理者はすべてのリソースにフルアクセス
        $role === 'admin' => true,

        // モデレーターはread/writeアクセス可能
        $role === 'moderator' && in_array($action, ['read', 'write']) => true,

        // ユーザーは自分のリソースにのみアクセス可能
        $role === 'user' && $resource === 'own_profile' => true,
        $role === 'user' && $action === 'read' => true,

        // ゲストはreadのみ
        $role === 'guest' && $action === 'read' => true,

        default => false,
    };

    return [
        'allowed' => $allowed,
        'reason' => $allowed ? 'アクセス許可' : 'アクセス権限がありません',
    ];
}

// テストケース
$users = [
    [
        'user' => ['is_logged_in' => true, 'role' => 'admin', 'is_active' => true, 'is_verified' => true],
        'resource' => 'users',
        'action' => 'delete',
        'name' => '管理者',
    ],
    [
        'user' => ['is_logged_in' => true, 'role' => 'moderator', 'is_active' => true, 'is_verified' => true],
        'resource' => 'posts',
        'action' => 'write',
        'name' => 'モデレーター',
    ],
    [
        'user' => ['is_logged_in' => true, 'role' => 'user', 'is_active' => true, 'is_verified' => false],
        'resource' => 'posts',
        'action' => 'write',
        'name' => '未認証ユーザー',
    ],
    [
        'user' => ['is_logged_in' => true, 'role' => 'user', 'is_active' => true, 'is_verified' => true],
        'resource' => 'posts',
        'action' => 'read',
        'name' => '一般ユーザー',
    ],
    [
        'user' => ['is_logged_in' => false, 'role' => 'guest', 'is_active' => false, 'is_verified' => false],
        'resource' => 'posts',
        'action' => 'read',
        'name' => 'ゲスト',
    ],
];

echo "アクセス権限チェック結果:" . PHP_EOL;
echo str_repeat('-', 80) . PHP_EOL;

foreach ($users as $test) {
    $result = checkAccess($test['user'], $test['resource'], $test['action']);
    $status = $result['allowed'] ? '✅ 許可' : '❌ 拒否';
    echo "{$test['name']} が {$test['resource']} に {$test['action']}: ";
    echo "{$status} - {$result['reason']}";
    echo PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// ボーナス演習: 複雑な条件判定
// ============================================================

echo "【ボーナス演習: 配送料計算システム】" . PHP_EOL;

/**
 * 配送料を計算する
 *
 * @param int $totalAmount 購入金額
 * @param string $region 地域（local, remote, overseas）
 * @param bool $isPrime プレミアム会員かどうか
 * @param int $weight 重量（kg）
 * @return array<string, mixed> 配送料計算結果
 */
function calculateShipping(int $totalAmount, string $region, bool $isPrime, int $weight): array
{
    // プレミアム会員は基本送料無料
    if ($isPrime && $totalAmount >= 2000) {
        return [
            'shipping_fee' => 0,
            'reason' => 'プレミアム会員特典（2,000円以上購入）',
        ];
    }

    // 一般会員も一定金額以上は送料無料
    if ($totalAmount >= 5000 && $region === 'local') {
        return [
            'shipping_fee' => 0,
            'reason' => '5,000円以上購入（国内）',
        ];
    }

    // 地域別基本送料
    $baseFee = match ($region) {
        'local' => 500,
        'remote' => 1000,
        'overseas' => 2500,
        default => 500,
    };

    // 重量による追加料金（10kgを超える場合、1kgあたり100円）
    $weightSurcharge = $weight > 10 ? ($weight - 10) * 100 : 0;

    // 合計送料
    $totalFee = $baseFee + $weightSurcharge;

    return [
        'shipping_fee' => $totalFee,
        'reason' => "基本送料¥{$baseFee}" . ($weightSurcharge > 0 ? " + 重量超過料金¥{$weightSurcharge}" : ""),
    ];
}

// テストケース
$orders = [
    [6000, 'local', false, 5],      // 送料無料
    [3000, 'local', true, 3],       // プレミアム会員
    [2000, 'remote', false, 15],    // 遠隔地 + 重量超過
    [1000, 'overseas', false, 2],   // 海外配送
];

echo "配送料計算結果:" . PHP_EOL;
echo str_repeat('-', 80) . PHP_EOL;

foreach ($orders as [$amount, $region, $prime, $weight]) {
    $result = calculateShipping($amount, $region, $prime, $weight);
    $regionName = match ($region) {
        'local' => '国内',
        'remote' => '遠隔地',
        'overseas' => '海外',
        default => '不明',
    };
    echo "購入金額¥" . number_format($amount);
    echo ", {$regionName}";
    echo ", " . ($prime ? 'プレミアム会員' : '一般会員');
    echo ", {$weight}kg";
    echo " → 送料: ¥" . number_format($result['shipping_fee']);
    echo " ({$result['reason']})";
    echo PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 学習のまとめ
// ============================================================

echo "【学習のまとめ】" . PHP_EOL;
echo "演習1: 四則演算とmatch式によるパターンマッチング" . PHP_EOL;
echo "演習2: 複雑な条件分岐と論理演算子の組み合わせ" . PHP_EOL;
echo "演習3: 複数の割引条件の比較とmax関数の活用" . PHP_EOL;
echo "演習4: null合体演算子によるデフォルト値設定" . PHP_EOL;
echo "演習5: 論理演算子を使った段階的な権限チェック" . PHP_EOL;
echo "ボーナス: 複雑なビジネスロジックの実装" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.2: 演算子の演習課題 完了 ===" . PHP_EOL;
