<?php

declare(strict_types=1);

/**
 * Phase 1.3: 条件分岐（if/else、switch、match）
 *
 * このファイルでは、PHPの条件分岐について学習します：
 * - if/else文
 * - elseif文
 * - switch文
 * - match式（PHP 8.0+）
 */

echo "=== Phase 1.3: 条件分岐 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 1. if文の基本
// ============================================================

echo "【1. if文の基本】" . PHP_EOL;

$score = 85;

if ($score >= 60) {
    echo "点数: {$score} → 合格" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 2. if-else文
// ============================================================

echo "【2. if-else文】" . PHP_EOL;

$age = 17;

if ($age >= 18) {
    echo "{$age}歳 → 成人です" . PHP_EOL;
} else {
    echo "{$age}歳 → 未成年です" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 3. if-elseif-else文
// ============================================================

echo "【3. if-elseif-else文】" . PHP_EOL;

$temperature = 28;

if ($temperature >= 30) {
    echo "気温{$temperature}℃ → 暑い" . PHP_EOL;
} elseif ($temperature >= 20) {
    echo "気温{$temperature}℃ → 快適" . PHP_EOL;
} elseif ($temperature >= 10) {
    echo "気温{$temperature}℃ → 涼しい" . PHP_EOL;
} else {
    echo "気温{$temperature}℃ → 寒い" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 4. ネストしたif文（早期リターンパターン）
// ============================================================

echo "【4. ネストしたif文と早期リターン】" . PHP_EOL;

/**
 * ユーザーがログインできるかチェック（悪い例：ネストが深い）
 *
 * @param array<string, mixed> $user ユーザー情報
 * @return string 結果メッセージ
 */
function canLoginBad(array $user): string
{
    if (isset($user['email'])) {
        if (isset($user['password'])) {
            if ($user['is_active']) {
                if ($user['is_verified']) {
                    return "ログイン成功";
                } else {
                    return "メール認証が必要です";
                }
            } else {
                return "アカウントが無効化されています";
            }
        } else {
            return "パスワードが必要です";
        }
    } else {
        return "メールアドレスが必要です";
    }
}

/**
 * ユーザーがログインできるかチェック（良い例：早期リターン）
 *
 * @param array<string, mixed> $user ユーザー情報
 * @return string 結果メッセージ
 */
function canLoginGood(array $user): string
{
    if (!isset($user['email'])) {
        return "メールアドレスが必要です";
    }

    if (!isset($user['password'])) {
        return "パスワードが必要です";
    }

    if (!$user['is_active']) {
        return "アカウントが無効化されています";
    }

    if (!$user['is_verified']) {
        return "メール認証が必要です";
    }

    return "ログイン成功";
}

$testUser = [
    'email' => 'test@example.com',
    'password' => 'hashed_password',
    'is_active' => true,
    'is_verified' => false,
];

echo "早期リターンパターン: " . canLoginGood($testUser) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 5. switch文
// ============================================================

echo "【5. switch文】" . PHP_EOL;

$dayOfWeek = 3;  // 0=日曜日, 1=月曜日, ...

switch ($dayOfWeek) {
    case 0:
        $dayName = "日曜日";
        break;
    case 1:
        $dayName = "月曜日";
        break;
    case 2:
        $dayName = "火曜日";
        break;
    case 3:
        $dayName = "水曜日";
        break;
    case 4:
        $dayName = "木曜日";
        break;
    case 5:
        $dayName = "金曜日";
        break;
    case 6:
        $dayName = "土曜日";
        break;
    default:
        $dayName = "不明";
        break;
}

echo "曜日番号{$dayOfWeek} → {$dayName}" . PHP_EOL;

echo PHP_EOL;

// switch文での複数条件（フォールスルー）
$month = 8;

switch ($month) {
    case 12:
    case 1:
    case 2:
        $season = "冬";
        break;
    case 3:
    case 4:
    case 5:
        $season = "春";
        break;
    case 6:
    case 7:
    case 8:
        $season = "夏";
        break;
    case 9:
    case 10:
    case 11:
        $season = "秋";
        break;
    default:
        $season = "不明";
        break;
}

echo "{$month}月 → {$season}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 6. match式（PHP 8.0+）
// ============================================================

echo "【6. match式（PHP 8.0+）】" . PHP_EOL;

// 基本的なmatch式
$statusCode = 200;

$message = match ($statusCode) {
    200 => "OK",
    201 => "Created",
    400 => "Bad Request",
    401 => "Unauthorized",
    403 => "Forbidden",
    404 => "Not Found",
    500 => "Internal Server Error",
    default => "Unknown Status",
};

echo "HTTP {$statusCode} → {$message}" . PHP_EOL;

echo PHP_EOL;

// match式での複数条件
$dayOfWeek = 6;

$dayType = match ($dayOfWeek) {
    0, 6 => "週末",
    1, 2, 3, 4, 5 => "平日",
    default => "不明",
};

echo "曜日{$dayOfWeek} → {$dayType}" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 7. match式 vs switch文の違い
// ============================================================

echo "【7. match式 vs switch文の違い】" . PHP_EOL . PHP_EOL;

echo "■ 違い1: 型の厳密な比較" . PHP_EOL;

$value = "1";  // 文字列の"1"

// switch文: 緩い比較（==）
switch ($value) {
    case 1:  // 整数の1
        echo "switch: マッチしました（文字列\"1\" == 整数1）" . PHP_EOL;
        break;
    default:
        echo "switch: マッチしませんでした" . PHP_EOL;
        break;
}

// match式: 厳密な比較（===）
$result = match ($value) {
    1 => "match: マッチしました",
    "1" => "match: 文字列\"1\"にマッチしました",
    default => "match: マッチしませんでした",
};
echo $result . PHP_EOL;

echo PHP_EOL;

echo "■ 違い2: 値を返す" . PHP_EOL;

// match式は値を直接返す
$grade = 85;
$evaluation = match (true) {
    $grade >= 90 => "優秀",
    $grade >= 80 => "良好",
    $grade >= 70 => "普通",
    $grade >= 60 => "合格",
    default => "不合格",
};
echo "評価: {$evaluation}" . PHP_EOL;

echo PHP_EOL;

echo "■ 違い3: break不要" . PHP_EOL;
echo "match式はフォールスルーしないため、breakが不要" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 8. 条件式での論理演算子
// ============================================================

echo "【8. 条件式での論理演算子】" . PHP_EOL;

$username = "admin";
$password = "correct_password";
$isActive = true;

// AND条件
if ($username === "admin" && $password === "correct_password" && $isActive) {
    echo "管理者ログイン成功" . PHP_EOL;
}

// OR条件
$role = "moderator";
if ($role === "admin" || $role === "moderator") {
    echo "編集権限あり" . PHP_EOL;
}

// 複合条件
$age = 25;
$hasLicense = true;
if (($age >= 18 && $hasLicense) || $role === "admin") {
    echo "運転可能" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 9. 実用例：グレード判定システム
// ============================================================

echo "【9. 実用例：グレード判定システム】" . PHP_EOL;

/**
 * 点数からグレードを判定する
 *
 * @param int $score 点数
 * @return array<string, mixed> 判定結果
 */
function evaluateScore(int $score): array
{
    // 入力検証
    if ($score < 0 || $score > 100) {
        return [
            'grade' => 'ERROR',
            'message' => "無効な点数です（0-100の範囲で入力してください）",
            'passed' => false,
        ];
    }

    // グレード判定（match式）
    $grade = match (true) {
        $score >= 90 => 'S',
        $score >= 80 => 'A',
        $score >= 70 => 'B',
        $score >= 60 => 'C',
        default => 'F',
    };

    // 評価メッセージ（if-elseif-else）
    if ($score >= 90) {
        $message = "素晴らしい成績です！";
    } elseif ($score >= 80) {
        $message = "優秀な成績です";
    } elseif ($score >= 70) {
        $message = "良い成績です";
    } elseif ($score >= 60) {
        $message = "合格です";
    } else {
        $message = "もう少し頑張りましょう";
    }

    return [
        'score' => $score,
        'grade' => $grade,
        'message' => $message,
        'passed' => $score >= 60,
    ];
}

$testScores = [95, 82, 68, 55, 105];

foreach ($testScores as $score) {
    $result = evaluateScore($score);
    echo "点数{$score}: グレード{$result['grade']} - {$result['message']}" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 10. 実用例：ユーザー権限チェック
// ============================================================

echo "【10. 実用例：ユーザー権限チェック】" . PHP_EOL;

/**
 * ユーザーのアクセスレベルを判定
 *
 * @param string $role ユーザーロール
 * @param string $action 実行するアクション
 * @return bool アクセス許可の有無
 */
function checkPermission(string $role, string $action): bool
{
    return match ($role) {
        'admin' => true,  // 管理者はすべて許可
        'moderator' => in_array($action, ['read', 'write', 'edit']),
        'user' => in_array($action, ['read']),
        'guest' => $action === 'read',
        default => false,
    };
}

$roles = ['admin', 'moderator', 'user', 'guest'];
$actions = ['read', 'write', 'delete'];

foreach ($roles as $role) {
    foreach ($actions as $action) {
        $allowed = checkPermission($role, $action);
        $status = $allowed ? '✅' : '❌';
        echo "{$status} {$role} - {$action}" . PHP_EOL;
    }
    echo PHP_EOL;
}

// ============================================================
// 11. 実用例：料金計算システム
// ============================================================

echo "【11. 実用例：料金計算システム】" . PHP_EOL;

/**
 * 時間帯と会員タイプから料金を計算
 *
 * @param string $timeSlot 時間帯（morning, afternoon, evening, night）
 * @param string $memberType 会員タイプ（premium, regular, guest）
 * @return int 料金（円）
 */
function calculatePrice(string $timeSlot, string $memberType): int
{
    // 基本料金（時間帯別）
    $basePrice = match ($timeSlot) {
        'morning' => 1000,    // 朝
        'afternoon' => 1500,  // 午後
        'evening' => 2000,    // 夕方
        'night' => 1200,      // 夜
        default => 1500,
    };

    // 会員割引
    $discount = match ($memberType) {
        'premium' => 0.5,   // 50%割引
        'regular' => 0.3,   // 30%割引
        'guest' => 0.0,     // 割引なし
        default => 0.0,
    };

    // 最終料金
    $finalPrice = (int)($basePrice * (1 - $discount));

    return $finalPrice;
}

$timeSlots = ['morning', 'afternoon', 'evening', 'night'];
$memberTypes = ['premium', 'regular', 'guest'];

foreach ($timeSlots as $timeSlot) {
    $timeName = match ($timeSlot) {
        'morning' => '朝',
        'afternoon' => '午後',
        'evening' => '夕方',
        'night' => '夜',
        default => '不明',
    };

    echo "{$timeName}の時間帯:" . PHP_EOL;
    foreach ($memberTypes as $memberType) {
        $price = calculatePrice($timeSlot, $memberType);
        $memberName = match ($memberType) {
            'premium' => 'プレミアム会員',
            'regular' => '一般会員',
            'guest' => 'ゲスト',
            default => '不明',
        };
        echo "  {$memberName}: ¥" . number_format($price) . PHP_EOL;
    }
    echo PHP_EOL;
}

// ============================================================
// 学習のポイント
// ============================================================

echo "【学習のポイント】" . PHP_EOL;
echo "1. if文: 基本的な条件分岐、早期リターンパターンを活用" . PHP_EOL;
echo "2. switch文: 複数の値に対する分岐、フォールスルーに注意" . PHP_EOL;
echo "3. match式: 値を返す、厳密な比較（===）、breakが不要" . PHP_EOL;
echo "4. match式はswitch文より安全で簡潔（PHP 8.0+で推奨）" . PHP_EOL;
echo "5. ネストが深くなる場合は早期リターンや関数分割を検討" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.3: 条件分岐 完了 ===" . PHP_EOL;
