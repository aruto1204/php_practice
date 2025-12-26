<?php

declare(strict_types=1);

/**
 * Phase 1.3: 制御構造の演習課題
 *
 * このファイルでは、条件分岐とループを使った実践的な演習を行います：
 * - 演習1: FizzBuzz問題
 * - 演習2: 九九表の出力
 * - 演習3: 配列の反復処理と集計
 * - 演習4: パターンマッチング
 * - 演習5: 素数判定
 */

echo "=== Phase 1.3: 制御構造の演習課題 ===" . PHP_EOL . PHP_EOL;

// ============================================================
// 演習1: FizzBuzz問題
// ============================================================

echo "【演習1: FizzBuzz問題】" . PHP_EOL;
echo "1から100までの数字を出力する。ただし：" . PHP_EOL;
echo "- 3の倍数の場合は「Fizz」" . PHP_EOL;
echo "- 5の倍数の場合は「Buzz」" . PHP_EOL;
echo "- 3と5の両方の倍数の場合は「FizzBuzz」" . PHP_EOL;
echo PHP_EOL;

/**
 * FizzBuzzを実行する（基本版）
 *
 * @param int $max 最大値
 * @return void
 */
function fizzBuzz(int $max): void
{
    for ($i = 1; $i <= $max; $i++) {
        if ($i % 15 === 0) {  // 3と5の両方で割り切れる（15で割り切れる）
            echo "FizzBuzz";
        } elseif ($i % 3 === 0) {
            echo "Fizz";
        } elseif ($i % 5 === 0) {
            echo "Buzz";
        } else {
            echo $i;
        }

        // 10個ごとに改行
        if ($i % 10 === 0) {
            echo PHP_EOL;
        } else {
            echo " ";
        }
    }
    echo PHP_EOL;
}

fizzBuzz(100);

echo PHP_EOL;

/**
 * FizzBuzzを実行する（match式版）
 *
 * @param int $max 最大値
 * @return array<string> 結果の配列
 */
function fizzBuzzWithMatch(int $max): array
{
    $results = [];

    for ($i = 1; $i <= $max; $i++) {
        $results[] = match (true) {
            $i % 15 === 0 => 'FizzBuzz',
            $i % 3 === 0 => 'Fizz',
            $i % 5 === 0 => 'Buzz',
            default => (string)$i,
        };
    }

    return $results;
}

$fizzBuzzResults = fizzBuzzWithMatch(30);
echo "FizzBuzz（match式版、1-30）:" . PHP_EOL;
echo implode(' ', $fizzBuzzResults) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// 演習2: 九九表の出力
// ============================================================

echo "【演習2: 九九表の出力】" . PHP_EOL . PHP_EOL;

/**
 * 九九表を出力する
 *
 * @param int $max 最大値（デフォルト: 9）
 * @return void
 */
function multiplicationTable(int $max = 9): void
{
    // ヘッダー行
    echo "   |";
    for ($i = 1; $i <= $max; $i++) {
        echo str_pad((string)$i, 4, ' ', STR_PAD_LEFT);
    }
    echo PHP_EOL;

    // 区切り線
    echo str_repeat('-', 4 + $max * 4) . PHP_EOL;

    // 各行
    for ($i = 1; $i <= $max; $i++) {
        echo str_pad((string)$i, 3, ' ', STR_PAD_LEFT) . "|";

        for ($j = 1; $j <= $max; $j++) {
            $result = $i * $j;
            echo str_pad((string)$result, 4, ' ', STR_PAD_LEFT);
        }
        echo PHP_EOL;
    }
}

echo "九九表（9x9）:" . PHP_EOL;
multiplicationTable(9);

echo PHP_EOL;

echo "小さな九九表（5x5）:" . PHP_EOL;
multiplicationTable(5);

echo PHP_EOL;

// ============================================================
// 演習3: 配列の反復処理と集計
// ============================================================

echo "【演習3: 配列の反復処理と集計】" . PHP_EOL . PHP_EOL;

/**
 * 学生の成績を分析する
 *
 * @param array<array<string, mixed>> $students 学生データ
 * @return array<string, mixed> 分析結果
 */
function analyzeStudentScores(array $students): array
{
    $totalScore = 0;
    $passCount = 0;
    $failCount = 0;
    $highestScore = 0;
    $lowestScore = 100;
    $topStudent = '';
    $gradeDistribution = [
        'S' => 0,
        'A' => 0,
        'B' => 0,
        'C' => 0,
        'F' => 0,
    ];

    foreach ($students as $student) {
        $score = $student['score'];
        $name = $student['name'];

        // 合計点
        $totalScore += $score;

        // 合否カウント
        if ($score >= 60) {
            $passCount++;
        } else {
            $failCount++;
        }

        // 最高点・最低点
        if ($score > $highestScore) {
            $highestScore = $score;
            $topStudent = $name;
        }
        if ($score < $lowestScore) {
            $lowestScore = $score;
        }

        // グレード分布
        $grade = match (true) {
            $score >= 90 => 'S',
            $score >= 80 => 'A',
            $score >= 70 => 'B',
            $score >= 60 => 'C',
            default => 'F',
        };
        $gradeDistribution[$grade]++;
    }

    $studentCount = count($students);
    $averageScore = $studentCount > 0 ? $totalScore / $studentCount : 0;

    return [
        'total_students' => $studentCount,
        'average_score' => round($averageScore, 2),
        'pass_count' => $passCount,
        'fail_count' => $failCount,
        'pass_rate' => $studentCount > 0 ? round(($passCount / $studentCount) * 100, 2) : 0,
        'highest_score' => $highestScore,
        'lowest_score' => $lowestScore,
        'top_student' => $topStudent,
        'grade_distribution' => $gradeDistribution,
    ];
}

$students = [
    ['name' => '山田太郎', 'score' => 95],
    ['name' => '佐藤花子', 'score' => 88],
    ['name' => '鈴木一郎', 'score' => 72],
    ['name' => '田中美咲', 'score' => 65],
    ['name' => '伊藤健太', 'score' => 58],
    ['name' => '渡辺さくら', 'score' => 91],
    ['name' => '中村拓也', 'score' => 45],
    ['name' => '小林愛', 'score' => 82],
];

$analysis = analyzeStudentScores($students);

echo "成績分析結果:" . PHP_EOL;
echo "総学生数: {$analysis['total_students']}人" . PHP_EOL;
echo "平均点: {$analysis['average_score']}点" . PHP_EOL;
echo "合格者: {$analysis['pass_count']}人 / 不合格者: {$analysis['fail_count']}人" . PHP_EOL;
echo "合格率: {$analysis['pass_rate']}%" . PHP_EOL;
echo "最高点: {$analysis['highest_score']}点（{$analysis['top_student']}）" . PHP_EOL;
echo "最低点: {$analysis['lowest_score']}点" . PHP_EOL;

echo PHP_EOL . "グレード分布:" . PHP_EOL;
foreach ($analysis['grade_distribution'] as $grade => $count) {
    $bar = str_repeat('■', $count);
    echo "  {$grade}: {$bar} ({$count}人)" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 演習4: パターンマッチングとデータ変換
// ============================================================

echo "【演習4: パターンマッチングとデータ変換】" . PHP_EOL . PHP_EOL;

/**
 * 注文データを処理する
 *
 * @param array<array<string, mixed>> $orders 注文データ
 * @return array<string, mixed> 処理結果
 */
function processOrders(array $orders): array
{
    $totalRevenue = 0;
    $ordersByStatus = [
        'pending' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ];
    $processedOrders = [];

    foreach ($orders as $order) {
        $orderId = $order['id'];
        $amount = $order['amount'];
        $status = $order['status'];

        // ステータスカウント
        $ordersByStatus[$status]++;

        // 売上計算（キャンセル以外）
        if ($status !== 'cancelled') {
            $totalRevenue += $amount;
        }

        // ステータスに応じた日本語メッセージ
        $statusMessage = match ($status) {
            'pending' => '注文受付中',
            'processing' => '処理中',
            'shipped' => '配送中',
            'delivered' => '配送完了',
            'cancelled' => 'キャンセル',
            default => '不明',
        };

        // 優先度判定
        $priority = match ($status) {
            'pending', 'processing' => '高',
            'shipped' => '中',
            'delivered', 'cancelled' => '低',
            default => '不明',
        };

        $processedOrders[] = [
            'id' => $orderId,
            'amount' => $amount,
            'status' => $status,
            'status_message' => $statusMessage,
            'priority' => $priority,
        ];
    }

    return [
        'total_orders' => count($orders),
        'total_revenue' => $totalRevenue,
        'orders_by_status' => $ordersByStatus,
        'processed_orders' => $processedOrders,
    ];
}

$orders = [
    ['id' => 'ORD001', 'amount' => 15000, 'status' => 'delivered'],
    ['id' => 'ORD002', 'amount' => 8500, 'status' => 'shipped'],
    ['id' => 'ORD003', 'amount' => 25000, 'status' => 'processing'],
    ['id' => 'ORD004', 'amount' => 3200, 'status' => 'pending'],
    ['id' => 'ORD005', 'amount' => 12000, 'status' => 'cancelled'],
    ['id' => 'ORD006', 'amount' => 18500, 'status' => 'delivered'],
];

$orderResult = processOrders($orders);

echo "注文処理結果:" . PHP_EOL;
echo "総注文数: {$orderResult['total_orders']}件" . PHP_EOL;
echo "総売上: ¥" . number_format($orderResult['total_revenue']) . PHP_EOL;

echo PHP_EOL . "ステータス別件数:" . PHP_EOL;
foreach ($orderResult['orders_by_status'] as $status => $count) {
    if ($count > 0) {
        echo "  {$status}: {$count}件" . PHP_EOL;
    }
}

echo PHP_EOL . "処理済み注文一覧:" . PHP_EOL;
foreach ($orderResult['processed_orders'] as $order) {
    echo "  {$order['id']}: ¥" . number_format($order['amount']);
    echo " - {$order['status_message']}（優先度: {$order['priority']}）" . PHP_EOL;
}

echo PHP_EOL;

// ============================================================
// 演習5: 素数判定
// ============================================================

echo "【演習5: 素数判定】" . PHP_EOL . PHP_EOL;

/**
 * 素数かどうか判定する
 *
 * @param int $number 判定する数値
 * @return bool 素数ならtrue
 */
function isPrime(int $number): bool
{
    // 2未満は素数ではない
    if ($number < 2) {
        return false;
    }

    // 2は素数
    if ($number === 2) {
        return true;
    }

    // 偶数は素数ではない（2を除く）
    if ($number % 2 === 0) {
        return false;
    }

    // 3から√number まで奇数でチェック
    $sqrt = (int)sqrt($number);
    for ($i = 3; $i <= $sqrt; $i += 2) {
        if ($number % $i === 0) {
            return false;
        }
    }

    return true;
}

/**
 * 指定範囲内の素数を見つける
 *
 * @param int $start 開始値
 * @param int $end 終了値
 * @return array<int> 素数の配列
 */
function findPrimes(int $start, int $end): array
{
    $primes = [];

    for ($i = $start; $i <= $end; $i++) {
        if (isPrime($i)) {
            $primes[] = $i;
        }
    }

    return $primes;
}

echo "1から50までの素数:" . PHP_EOL;
$primes = findPrimes(1, 50);
echo implode(', ', $primes) . PHP_EOL;
echo "見つかった素数の個数: " . count($primes) . "個" . PHP_EOL;

echo PHP_EOL;

echo "100から150までの素数:" . PHP_EOL;
$primes = findPrimes(100, 150);
echo implode(', ', $primes) . PHP_EOL;
echo "見つかった素数の個数: " . count($primes) . "個" . PHP_EOL;

echo PHP_EOL;

// ============================================================
// ボーナス演習1: カレンダー生成
// ============================================================

echo "【ボーナス演習1: 簡易カレンダー生成】" . PHP_EOL . PHP_EOL;

/**
 * 月のカレンダーを生成する（簡易版）
 *
 * @param int $year 年
 * @param int $month 月
 * @return void
 */
function generateCalendar(int $year, int $month): void
{
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDay = (int)date('w', mktime(0, 0, 0, $month, 1, $year));

    $monthName = date('Y年n月', mktime(0, 0, 0, $month, 1, $year));
    echo "{$monthName}" . PHP_EOL;

    // 曜日ヘッダー
    echo "日 月 火 水 木 金 土" . PHP_EOL;

    // 最初の週の空白
    for ($i = 0; $i < $firstDay; $i++) {
        echo "   ";
    }

    // 日付を出力
    for ($day = 1; $day <= $daysInMonth; $day++) {
        echo str_pad((string)$day, 2, ' ', STR_PAD_LEFT) . " ";

        // 土曜日の後は改行
        $currentDay = ($firstDay + $day - 1) % 7;
        if ($currentDay === 6) {
            echo PHP_EOL;
        }
    }

    echo PHP_EOL;
}

generateCalendar(2025, 12);

echo PHP_EOL;

// ============================================================
// ボーナス演習2: フィボナッチ数列
// ============================================================

echo "【ボーナス演習2: フィボナッチ数列】" . PHP_EOL . PHP_EOL;

/**
 * フィボナッチ数列を生成する
 *
 * @param int $count 生成する数の個数
 * @return array<int> フィボナッチ数列
 */
function generateFibonacci(int $count): array
{
    if ($count <= 0) {
        return [];
    }

    if ($count === 1) {
        return [0];
    }

    $fibonacci = [0, 1];

    for ($i = 2; $i < $count; $i++) {
        $fibonacci[] = $fibonacci[$i - 1] + $fibonacci[$i - 2];
    }

    return $fibonacci;
}

echo "フィボナッチ数列（最初の15個）:" . PHP_EOL;
$fib = generateFibonacci(15);
echo implode(', ', $fib) . PHP_EOL;

echo PHP_EOL;

// ============================================================
// ボーナス演習3: ダイヤモンドパターン
// ============================================================

echo "【ボーナス演習3: ダイヤモンドパターン】" . PHP_EOL . PHP_EOL;

/**
 * ダイヤモンドパターンを出力する
 *
 * @param int $size サイズ（奇数を推奨）
 * @return void
 */
function printDiamond(int $size): void
{
    $mid = (int)($size / 2);

    // 上半分（中央含む）
    for ($i = 0; $i <= $mid; $i++) {
        $spaces = $mid - $i;
        $stars = 2 * $i + 1;

        echo str_repeat(' ', $spaces);
        echo str_repeat('*', $stars);
        echo PHP_EOL;
    }

    // 下半分
    for ($i = $mid - 1; $i >= 0; $i--) {
        $spaces = $mid - $i;
        $stars = 2 * $i + 1;

        echo str_repeat(' ', $spaces);
        echo str_repeat('*', $stars);
        echo PHP_EOL;
    }
}

echo "ダイヤモンドパターン（サイズ: 7）:" . PHP_EOL;
printDiamond(7);

echo PHP_EOL;

// ============================================================
// 学習のまとめ
// ============================================================

echo "【学習のまとめ】" . PHP_EOL;
echo "演習1: FizzBuzz - 条件分岐とループの組み合わせ" . PHP_EOL;
echo "演習2: 九九表 - ネストしたループとフォーマット" . PHP_EOL;
echo "演習3: 成績分析 - 配列の反復処理と集計" . PHP_EOL;
echo "演習4: 注文処理 - match式を使ったパターンマッチング" . PHP_EOL;
echo "演習5: 素数判定 - 効率的なアルゴリズムとループ制御" . PHP_EOL;
echo "ボーナス1: カレンダー - 複雑なループロジック" . PHP_EOL;
echo "ボーナス2: フィボナッチ - 数列生成アルゴリズム" . PHP_EOL;
echo "ボーナス3: ダイヤモンド - パターン生成とループ" . PHP_EOL;

echo PHP_EOL;
echo "=== Phase 1.3: 制御構造の演習課題 完了 ===" . PHP_EOL;
