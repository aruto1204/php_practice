<?php

declare(strict_types=1);

/**
 * Phase 2.2: 継承とポリモーフィズム
 *
 * このファイルでは、PHPの継承とポリモーフィズムを学習します。
 *
 * 学習内容:
 * 1. 継承の基本
 * 2. メソッドのオーバーライド
 * 3. parent キーワード
 * 4. ポリモーフィズム
 * 5. final キーワード
 * 6. 型宣言と継承
 */

echo "=== 1. 継承の基本 ===" . PHP_EOL;

/**
 * 基底クラス - 動物
 */
class Animal
{
    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param int $age 年齢
     */
    public function __construct(
        protected string $name,
        protected int $age,
    ) {
    }

    /**
     * 鳴く
     *
     * @return string 鳴き声
     */
    public function makeSound(): string
    {
        return "{$this->name}が鳴いています";
    }

    /**
     * 情報を取得する
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return "{$this->name}（{$this->age}歳）";
    }

    /**
     * 名前を取得する
     *
     * @return string 名前
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 年齢を取得する
     *
     * @return int 年齢
     */
    public function getAge(): int
    {
        return $this->age;
    }
}

/**
 * 犬クラス（Animalを継承）
 */
class Dog extends Animal
{
    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param int $age 年齢
     * @param string $breed 犬種
     */
    public function __construct(
        string $name,
        int $age,
        private string $breed,
    ) {
        // 親クラスのコンストラクタを呼び出す
        parent::__construct($name, $age);
    }

    /**
     * 鳴く（オーバーライド）
     *
     * @return string 鳴き声
     */
    public function makeSound(): string
    {
        return "{$this->name}が「ワンワン！」と吠えました";
    }

    /**
     * お手をする（Dogクラス独自のメソッド）
     *
     * @return string メッセージ
     */
    public function giveHand(): string
    {
        return "{$this->name}がお手をしました";
    }

    /**
     * 犬種を取得する
     *
     * @return string 犬種
     */
    public function getBreed(): string
    {
        return $this->breed;
    }

    /**
     * 情報を取得する（オーバーライド）
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return "{$this->name}（{$this->breed}、{$this->age}歳）";
    }
}

/**
 * 猫クラス（Animalを継承）
 */
class Cat extends Animal
{
    /**
     * 鳴く（オーバーライド）
     *
     * @return string 鳴き声
     */
    public function makeSound(): string
    {
        return "{$this->name}が「ニャー！」と鳴きました";
    }

    /**
     * 爪を研ぐ（Catクラス独自のメソッド）
     *
     * @return string メッセージ
     */
    public function scratchPost(): string
    {
        return "{$this->name}が爪を研いでいます";
    }
}

// 継承の使用例
$dog = new Dog("ポチ", 3, "柴犬");
echo $dog->getInfo() . PHP_EOL;
echo $dog->makeSound() . PHP_EOL;
echo $dog->giveHand() . PHP_EOL;

echo PHP_EOL;

$cat = new Cat("タマ", 2);
echo $cat->getInfo() . PHP_EOL;
echo $cat->makeSound() . PHP_EOL;
echo $cat->scratchPost() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 2. ポリモーフィズム ===" . PHP_EOL;

/**
 * 動物園クラス
 */
class Zoo
{
    /**
     * @var array<Animal> 動物のリスト
     */
    private array $animals = [];

    /**
     * 動物を追加する
     *
     * @param Animal $animal 動物
     * @return void
     */
    public function addAnimal(Animal $animal): void
    {
        $this->animals[] = $animal;
    }

    /**
     * すべての動物を鳴かせる
     *
     * @return void
     */
    public function makeAllAnimalsSound(): void
    {
        echo "=== 動物園の動物たち ===" . PHP_EOL;
        foreach ($this->animals as $animal) {
            // ポリモーフィズム: 実際のクラスに応じた makeSound() が呼ばれる
            echo $animal->makeSound() . PHP_EOL;
        }
    }

    /**
     * すべての動物の情報を表示する
     *
     * @return void
     */
    public function showAllAnimals(): void
    {
        echo "=== 動物園の動物リスト ===" . PHP_EOL;
        foreach ($this->animals as $index => $animal) {
            $number = $index + 1;
            echo "{$number}. {$animal->getInfo()}" . PHP_EOL;
        }
    }
}

$zoo = new Zoo();
$zoo->addAnimal(new Dog("ポチ", 3, "柴犬"));
$zoo->addAnimal(new Cat("タマ", 2));
$zoo->addAnimal(new Dog("ハチ", 5, "秋田犬"));
$zoo->addAnimal(new Cat("ミケ", 1));

$zoo->showAllAnimals();
echo PHP_EOL;
$zoo->makeAllAnimalsSound();

echo PHP_EOL;

// =============================================================================

echo "=== 3. parent キーワード ===" . PHP_EOL;

/**
 * 従業員クラス
 */
class Employee
{
    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param float $baseSalary 基本給
     */
    public function __construct(
        protected string $name,
        protected float $baseSalary,
    ) {
    }

    /**
     * 給料を計算する
     *
     * @return float 給料
     */
    public function calculateSalary(): float
    {
        return $this->baseSalary;
    }

    /**
     * 情報を表示する
     *
     * @return void
     */
    public function displayInfo(): void
    {
        echo "名前: {$this->name}" . PHP_EOL;
        echo "給料: " . number_format($this->calculateSalary()) . "円" . PHP_EOL;
    }
}

/**
 * マネージャークラス（Employeeを継承）
 */
class Manager extends Employee
{
    /**
     * コンストラクタ
     *
     * @param string $name 名前
     * @param float $baseSalary 基本給
     * @param float $bonus ボーナス
     */
    public function __construct(
        string $name,
        float $baseSalary,
        private float $bonus,
    ) {
        parent::__construct($name, $baseSalary);
    }

    /**
     * 給料を計算する（オーバーライド）
     *
     * @return float 給料
     */
    public function calculateSalary(): float
    {
        // 親クラスのメソッドを呼び出して、ボーナスを追加
        return parent::calculateSalary() + $this->bonus;
    }

    /**
     * 情報を表示する（オーバーライド）
     *
     * @return void
     */
    public function displayInfo(): void
    {
        echo "=== マネージャー情報 ===" . PHP_EOL;
        parent::displayInfo();  // 親クラスのメソッドを呼び出す
        echo "ボーナス: " . number_format($this->bonus) . "円" . PHP_EOL;
    }
}

$employee = new Employee("山田太郎", 300000);
$employee->displayInfo();

echo PHP_EOL;

$manager = new Manager("佐藤花子", 500000, 200000);
$manager->displayInfo();

echo PHP_EOL;

// =============================================================================

echo "=== 4. 多段階継承 ===" . PHP_EOL;

/**
 * 図形クラス
 */
class Shape
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
     * 情報を取得する
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return "色: {$this->color}";
    }
}

/**
 * 2D図形クラス（Shapeを継承）
 */
class Shape2D extends Shape
{
    /**
     * 面積を計算する（サブクラスで実装する）
     *
     * @return float 面積
     */
    public function calculateArea(): float
    {
        return 0;
    }

    /**
     * 情報を取得する（オーバーライド）
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        $area = $this->calculateArea();
        return parent::getInfo() . "、面積: {$area}";
    }
}

/**
 * 円クラス（Shape2Dを継承）
 */
class Circle extends Shape2D
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
     * 面積を計算する（オーバーライド）
     *
     * @return float 面積
     */
    public function calculateArea(): float
    {
        return pi() * $this->radius * $this->radius;
    }

    /**
     * 情報を取得する（オーバーライド）
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return "円 - " . parent::getInfo() . "、半径: {$this->radius}";
    }
}

/**
 * 長方形クラス（Shape2Dを継承）
 */
class Rectangle extends Shape2D
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
     * 面積を計算する（オーバーライド）
     *
     * @return float 面積
     */
    public function calculateArea(): float
    {
        return $this->width * $this->height;
    }

    /**
     * 情報を取得する（オーバーライド）
     *
     * @return string 情報
     */
    public function getInfo(): string
    {
        return "長方形 - " . parent::getInfo() . "、幅: {$this->width}、高さ: {$this->height}";
    }
}

$circle = new Circle("赤", 5);
echo $circle->getInfo() . PHP_EOL;

$rectangle = new Rectangle("青", 10, 5);
echo $rectangle->getInfo() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 5. final キーワード ===" . PHP_EOL;

/**
 * 支払いクラス
 */
class Payment
{
    /**
     * コンストラクタ
     *
     * @param float $amount 金額
     */
    public function __construct(
        protected float $amount,
    ) {
    }

    /**
     * 処理する
     *
     * @return string メッセージ
     */
    public function process(): string
    {
        return "支払いを処理しました: " . number_format($this->amount) . "円";
    }

    /**
     * 領収書を発行する（final - オーバーライド不可）
     *
     * @return string 領収書
     */
    final public function generateReceipt(): string
    {
        return "=== 領収書 ===" . PHP_EOL .
               "金額: " . number_format($this->amount) . "円" . PHP_EOL .
               "日時: " . date('Y-m-d H:i:s');
    }
}

/**
 * クレジットカード支払いクラス
 */
class CreditCardPayment extends Payment
{
    /**
     * コンストラクタ
     *
     * @param float $amount 金額
     * @param string $cardNumber カード番号
     */
    public function __construct(
        float $amount,
        private string $cardNumber,
    ) {
        parent::__construct($amount);
    }

    /**
     * 処理する（オーバーライド）
     *
     * @return string メッセージ
     */
    public function process(): string
    {
        $maskedCard = "****-****-****-" . substr($this->cardNumber, -4);
        return "クレジットカードで支払いを処理しました: " .
               number_format($this->amount) . "円（カード: {$maskedCard}）";
    }

    // generateReceipt() はfinalなのでオーバーライドできない
}

$payment = new CreditCardPayment(5000, "1234567812345678");
echo $payment->process() . PHP_EOL;
echo $payment->generateReceipt() . PHP_EOL;

echo PHP_EOL;

// =============================================================================

echo "=== 6. final クラス ===" . PHP_EOL;

/**
 * 文字列ユーティリティクラス（final - 継承不可）
 */
final class StringUtil
{
    /**
     * 文字列を大文字に変換する
     *
     * @param string $str 文字列
     * @return string 大文字の文字列
     */
    public static function toUpper(string $str): string
    {
        return strtoupper($str);
    }

    /**
     * 文字列を小文字に変換する
     *
     * @param string $str 文字列
     * @return string 小文字の文字列
     */
    public static function toLower(string $str): string
    {
        return strtolower($str);
    }
}

echo "大文字: " . StringUtil::toUpper("hello world") . PHP_EOL;
echo "小文字: " . StringUtil::toLower("HELLO WORLD") . PHP_EOL;

// class ExtendedStringUtil extends StringUtil {} // エラー！finalクラスは継承できない

echo PHP_EOL;

// =============================================================================

echo "=== 7. 型宣言と継承 ===" . PHP_EOL;

/**
 * ロガーインターフェース
 */
interface LoggerInterface
{
    public function log(string $message): void;
}

/**
 * ファイルロガー
 */
class FileLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo "[FileLogger] {$message}" . PHP_EOL;
    }
}

/**
 * データベースロガー
 */
class DatabaseLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo "[DatabaseLogger] {$message}" . PHP_EOL;
    }

    /**
     * データベース固有のメソッド
     *
     * @return void
     */
    public function flush(): void
    {
        echo "[DatabaseLogger] ログをフラッシュしました" . PHP_EOL;
    }
}

/**
 * アプリケーションクラス
 */
class Application
{
    /**
     * コンストラクタ
     *
     * @param LoggerInterface $logger ロガー
     */
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * 実行する
     *
     * @return void
     */
    public function run(): void
    {
        $this->logger->log("アプリケーションを開始しました");
        $this->logger->log("処理を実行中...");
        $this->logger->log("アプリケーションを終了しました");

        // 型チェックしてから固有のメソッドを呼び出す
        if ($this->logger instanceof DatabaseLogger) {
            $this->logger->flush();
        }
    }
}

echo "--- FileLoggerを使用 ---" . PHP_EOL;
$app1 = new Application(new FileLogger());
$app1->run();

echo PHP_EOL . "--- DatabaseLoggerを使用 ---" . PHP_EOL;
$app2 = new Application(new DatabaseLogger());
$app2->run();

echo PHP_EOL;

// =============================================================================

echo "=== 8. メソッドの可視性 ===" . PHP_EOL;

/**
 * 基底クラス
 */
class BaseClass
{
    protected function protectedMethod(): string
    {
        return "protectedメソッド";
    }

    private function privateMethod(): string
    {
        return "privateメソッド";
    }

    public function publicMethod(): string
    {
        return "publicメソッド";
    }

    public function callAllMethods(): void
    {
        echo $this->publicMethod() . PHP_EOL;
        echo $this->protectedMethod() . PHP_EOL;
        echo $this->privateMethod() . PHP_EOL;
    }
}

/**
 * 派生クラス
 */
class DerivedClass extends BaseClass
{
    // protectedメソッドはオーバーライド可能
    protected function protectedMethod(): string
    {
        return "オーバーライドされたprotectedメソッド";
    }

    // privateメソッドはオーバーライドできない（新しいメソッドとして扱われる）
    private function privateMethod(): string
    {
        return "新しいprivateメソッド";
    }

    public function callProtected(): void
    {
        echo $this->protectedMethod() . PHP_EOL;
        // echo $this->privateMethod() . PHP_EOL; // 親のprivateメソッドにはアクセスできない
    }
}

$derived = new DerivedClass();
echo "派生クラスからすべてのメソッドを呼び出し:" . PHP_EOL;
$derived->callAllMethods();

echo PHP_EOL . "protectedメソッドを呼び出し:" . PHP_EOL;
$derived->callProtected();

echo PHP_EOL;

// =============================================================================

echo "=== 完了 ===" . PHP_EOL;
echo "継承とポリモーフィズムを学習しました！" . PHP_EOL;
echo "次は 15_abstract_interface.php で抽象クラスとインターフェースを学習します。" . PHP_EOL;
