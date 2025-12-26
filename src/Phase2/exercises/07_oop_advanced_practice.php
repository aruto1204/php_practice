<?php

declare(strict_types=1);

/**
 * Phase 2.2: OOP応用の演習課題
 *
 * このファイルでは、継承、インターフェース、トレイト、静的メソッドを使った
 * 実践的な演習を行います。
 */

echo "=== OOP応用の演習課題 ===" . PHP_EOL . PHP_EOL;

// =============================================================================
// 1. 図形の継承階層
// =============================================================================

echo "=== 1. 図形の継承階層 ===" . PHP_EOL;

/**
 * 図形インターフェース
 */
interface ShapeInterface
{
    /**
     * 面積を計算する
     *
     * @return float 面積
     */
    public function calculateArea(): float;

    /**
     * 周囲の長さを計算する
     *
     * @return float 周囲の長さ
     */
    public function calculatePerimeter(): float;
}

/**
 * 抽象図形クラス
 */
abstract class Shape implements ShapeInterface
{
    /**
     * コンストラクタ
     *
     * @param string $color 色
     */
    public function __construct(
        protected string $color,
    ) {
    }

    /**
     * 色を取得する
     *
     * @return string 色
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * 情報を取得する
     *
     * @return string 情報
     */
    abstract public function getInfo(): string;
}

/**
 * 円クラス
 */
class Circle extends Shape
{
    /**
     * コンストラクタ
     *
     * @param string $color 色
     * @param float $radius 半径
     */
    public function __construct(
        string $color,
        private float $radius,
    ) {
        parent::__construct($color);
    }

    /**
     * 面積を計算する
     *
     * @return float 面積
     */
    public function calculateArea(): float
    {
        return pi() * $this->radius ** 2;
    }

    /**
     * 周囲の長さを計算する
     *
     * @return float 周囲の長さ
     */
    public function calculatePerimeter(): float
    {
        return 2 * pi() * $this->radius;
    }

    /**
     * 情報を取得する
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return sprintf(
            "円（色: %s、半径: %.2f、面積: %.2f、周囲: %.2f）",
            $this->color,
            $this->radius,
            $this->calculateArea(),
            $this->calculatePerimeter()
        );
    }
}

/**
 * 長方形クラス
 */
class Rectangle extends Shape
{
    /**
     * コンストラクタ
     *
     * @param string $color 色
     * @param float $width 幅
     * @param float $height 高さ
     */
    public function __construct(
        string $color,
        private float $width,
        private float $height,
    ) {
        parent::__construct($color);
    }

    /**
     * 面積を計算する
     *
     * @return float 面積
     */
    public function calculateArea(): float
    {
        return $this->width * $this->height;
    }

    /**
     * 周囲の長さを計算する
     *
     * @return float 周囲の長さ
     */
    public function calculatePerimeter(): float
    {
        return 2 * ($this->width + $this->height);
    }

    /**
     * 情報を取得する
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return sprintf(
            "長方形（色: %s、幅: %.2f、高さ: %.2f、面積: %.2f、周囲: %.2f）",
            $this->color,
            $this->width,
            $this->height,
            $this->calculateArea(),
            $this->calculatePerimeter()
        );
    }
}

/**
 * 三角形クラス
 */
class Triangle extends Shape
{
    /**
     * コンストラクタ
     *
     * @param string $color 色
     * @param float $base 底辺
     * @param float $height 高さ
     * @param float $sideA 辺A
     * @param float $sideB 辺B
     */
    public function __construct(
        string $color,
        private float $base,
        private float $height,
        private float $sideA,
        private float $sideB,
    ) {
        parent::__construct($color);
    }

    /**
     * 面積を計算する
     *
     * @return float 面積
     */
    public function calculateArea(): float
    {
        return ($this->base * $this->height) / 2;
    }

    /**
     * 周囲の長さを計算する
     *
     * @return float 周囲の長さ
     */
    public function calculatePerimeter(): float
    {
        return $this->base + $this->sideA + $this->sideB;
    }

    /**
     * 情報を取得する
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return sprintf(
            "三角形（色: %s、底辺: %.2f、高さ: %.2f、面積: %.2f、周囲: %.2f）",
            $this->color,
            $this->base,
            $this->height,
            $this->calculateArea(),
            $this->calculatePerimeter()
        );
    }
}

// 図形のテスト
$shapes = [
    new Circle("赤", 5),
    new Rectangle("青", 10, 5),
    new Triangle("緑", 8, 6, 5, 7),
];

echo "図形リスト:" . PHP_EOL;
$totalArea = 0;
foreach ($shapes as $shape) {
    echo "- " . $shape->getInfo() . PHP_EOL;
    $totalArea += $shape->calculateArea();
}

echo PHP_EOL . "総面積: " . number_format($totalArea, 2) . PHP_EOL;

echo PHP_EOL;

// =============================================================================
// 2. 支払いシステム
// =============================================================================

echo "=== 2. 支払いシステム ===" . PHP_EOL;

/**
 * 支払い可能インターフェース
 */
interface PayableInterface
{
    /**
     * 支払いを処理する
     *
     * @param float $amount 金額
     * @return bool 成功の場合true
     */
    public function process(float $amount): bool;

    /**
     * 手数料を計算する
     *
     * @param float $amount 金額
     * @return float 手数料
     */
    public function calculateFee(float $amount): float;
}

/**
 * 領収書発行トレイト
 */
trait ReceiptGenerator
{
    /**
     * 領収書を生成する
     *
     * @param float $amount 金額
     * @param float $fee 手数料
     * @return string 領収書
     */
    protected function generateReceipt(float $amount, float $fee): string
    {
        $total = $amount + $fee;
        $timestamp = date('Y-m-d H:i:s');

        return <<<EOT
=== 領収書 ===
日時: {$timestamp}
支払方法: {$this->getPaymentMethodName()}
金額: {$amount}円
手数料: {$fee}円
合計: {$total}円
EOT;
    }

    /**
     * 支払方法名を取得する
     *
     * @return string 支払方法名
     */
    abstract protected function getPaymentMethodName(): string;
}

/**
 * 抽象支払いクラス
 */
abstract class Payment implements PayableInterface
{
    use ReceiptGenerator;

    /**
     * 支払いを実行する
     *
     * @param float $amount 金額
     * @return bool 成功の場合true
     */
    public function process(float $amount): bool
    {
        $fee = $this->calculateFee($amount);
        $total = $amount + $fee;

        echo "=== {$this->getPaymentMethodName()}での支払い ===" . PHP_EOL;
        echo "金額: " . number_format($amount) . "円" . PHP_EOL;
        echo "手数料: " . number_format($fee) . "円" . PHP_EOL;
        echo "合計: " . number_format($total) . "円" . PHP_EOL;

        if ($this->authorize($amount)) {
            echo "支払いが承認されました" . PHP_EOL;
            echo $this->generateReceipt($amount, $fee) . PHP_EOL;
            return true;
        }

        echo "支払いが拒否されました" . PHP_EOL;
        return false;
    }

    /**
     * 支払いを承認する
     *
     * @param float $amount 金額
     * @return bool 承認された場合true
     */
    abstract protected function authorize(float $amount): bool;
}

/**
 * クレジットカード支払い
 */
class CreditCardPayment extends Payment
{
    /**
     * コンストラクタ
     *
     * @param string $cardNumber カード番号
     * @param string $cardHolder カード名義
     */
    public function __construct(
        private string $cardNumber,
        private string $cardHolder,
    ) {
    }

    /**
     * 手数料を計算する（3%）
     *
     * @param float $amount 金額
     * @return float 手数料
     */
    public function calculateFee(float $amount): float
    {
        return $amount * 0.03;
    }

    /**
     * 支払いを承認する
     *
     * @param float $amount 金額
     * @return bool 承認された場合true
     */
    protected function authorize(float $amount): bool
    {
        // カード番号の簡易チェック
        return strlen($this->cardNumber) === 16;
    }

    /**
     * 支払方法名を取得する
     *
     * @return string 支払方法名
     */
    protected function getPaymentMethodName(): string
    {
        return "クレジットカード";
    }
}

/**
 * 銀行振込支払い
 */
class BankTransferPayment extends Payment
{
    /**
     * コンストラクタ
     *
     * @param string $accountNumber 口座番号
     * @param string $bankName 銀行名
     */
    public function __construct(
        private string $accountNumber,
        private string $bankName,
    ) {
    }

    /**
     * 手数料を計算する（固定300円）
     *
     * @param float $amount 金額
     * @return float 手数料
     */
    public function calculateFee(float $amount): float
    {
        return 300;
    }

    /**
     * 支払いを承認する
     *
     * @param float $amount 金額
     * @return bool 承認された場合true
     */
    protected function authorize(float $amount): bool
    {
        // 口座番号の簡易チェック
        return !empty($this->accountNumber);
    }

    /**
     * 支払方法名を取得する
     *
     * @return string 支払方法名
     */
    protected function getPaymentMethodName(): string
    {
        return "銀行振込（{$this->bankName}）";
    }
}

/**
 * 電子マネー支払い
 */
class ElectronicMoneyPayment extends Payment
{
    /**
     * コンストラクタ
     *
     * @param string $accountId アカウントID
     * @param float $balance 残高
     */
    public function __construct(
        private string $accountId,
        private float $balance,
    ) {
    }

    /**
     * 手数料を計算する（無料）
     *
     * @param float $amount 金額
     * @return float 手数料
     */
    public function calculateFee(float $amount): float
    {
        return 0;
    }

    /**
     * 支払いを承認する
     *
     * @param float $amount 金額
     * @return bool 承認された場合true
     */
    protected function authorize(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * 支払方法名を取得する
     *
     * @return string 支払方法名
     */
    protected function getPaymentMethodName(): string
    {
        return "電子マネー";
    }
}

// 支払いのテスト
$payments = [
    new CreditCardPayment("1234567812345678", "山田太郎"),
    new BankTransferPayment("1234567", "三菱UFJ銀行"),
    new ElectronicMoneyPayment("user123", 10000),
];

foreach ($payments as $payment) {
    $payment->process(5000);
    echo PHP_EOL;
}

// =============================================================================
// 3. ロガーシステム
// =============================================================================

echo "=== 3. ロガーシステム ===" . PHP_EOL;

/**
 * ロガーインターフェース
 */
interface LoggerInterface
{
    public function log(string $level, string $message): void;
    public function info(string $message): void;
    public function warning(string $message): void;
    public function error(string $message): void;
}

/**
 * フォーマッタブルトレイト
 */
trait Formattable
{
    /**
     * ログメッセージをフォーマットする
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @return string フォーマット済みメッセージ
     */
    protected function format(string $level, string $message): string
    {
        $timestamp = date('Y-m-d H:i:s');
        return "[{$timestamp}] [{$level}] {$message}";
    }
}

/**
 * 抽象ロガークラス
 */
abstract class Logger implements LoggerInterface
{
    use Formattable;

    /**
     * ログを記録する
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @return void
     */
    public function log(string $level, string $message): void
    {
        $formatted = $this->format($level, $message);
        $this->write($formatted);
    }

    /**
     * 情報ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    /**
     * 警告ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }

    /**
     * エラーログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }

    /**
     * ログを書き込む
     *
     * @param string $message メッセージ
     * @return void
     */
    abstract protected function write(string $message): void;
}

/**
 * ファイルロガー
 */
class FileLogger extends Logger
{
    /**
     * コンストラクタ
     *
     * @param string $filePath ファイルパス
     */
    public function __construct(
        private string $filePath,
    ) {
    }

    /**
     * ログを書き込む
     *
     * @param string $message メッセージ
     * @return void
     */
    protected function write(string $message): void
    {
        echo "[FileLogger:{$this->filePath}] {$message}" . PHP_EOL;
    }
}

/**
 * データベースロガー
 */
class DatabaseLogger extends Logger
{
    /**
     * @var array<string> ログ履歴
     */
    private static array $logs = [];

    /**
     * ログを書き込む
     *
     * @param string $message メッセージ
     * @return void
     */
    protected function write(string $message): void
    {
        self::$logs[] = $message;
        echo "[DatabaseLogger] {$message}" . PHP_EOL;
    }

    /**
     * すべてのログを取得する
     *
     * @return array<string> ログ履歴
     */
    public static function getAllLogs(): array
    {
        return self::$logs;
    }
}

/**
 * 複数ロガー
 */
class MultiLogger implements LoggerInterface
{
    /**
     * @var array<LoggerInterface> ロガーリスト
     */
    private array $loggers = [];

    /**
     * ロガーを追加する
     *
     * @param LoggerInterface $logger ロガー
     * @return void
     */
    public function addLogger(LoggerInterface $logger): void
    {
        $this->loggers[] = $logger;
    }

    /**
     * ログを記録する
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @return void
     */
    public function log(string $level, string $message): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message);
        }
    }

    /**
     * 情報ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    /**
     * 警告ログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }

    /**
     * エラーログを記録する
     *
     * @param string $message メッセージ
     * @return void
     */
    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }
}

// ロガーのテスト
$multiLogger = new MultiLogger();
$multiLogger->addLogger(new FileLogger('/var/log/app.log'));
$multiLogger->addLogger(new DatabaseLogger());

$multiLogger->info('アプリケーションを開始しました');
$multiLogger->warning('メモリ使用量が高くなっています');
$multiLogger->error('データベース接続に失敗しました');

echo PHP_EOL . "データベースに保存されたログ:" . PHP_EOL;
foreach (DatabaseLogger::getAllLogs() as $log) {
    echo "- {$log}" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================

echo "=== 演習課題完了 ===" . PHP_EOL;
echo "OOP応用の実践的な演習が完了しました！" . PHP_EOL;
echo "学習した内容:" . PHP_EOL;
echo "- 継承とインターフェースを使った図形システム" . PHP_EOL;
echo "- 抽象クラスとトレイトを使った支払いシステム" . PHP_EOL;
echo "- ポリモーフィズムを活用したロガーシステム" . PHP_EOL;
echo "- 静的メソッド・プロパティの実践的な使用" . PHP_EOL;
