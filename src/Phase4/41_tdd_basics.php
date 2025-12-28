<?php

declare(strict_types=1);

/**
 * Phase 4.3: テスト駆動開発（TDD）
 *
 * このファイルでは、PHPUnitを使ったテスト駆動開発について学びます。
 *
 * 学習内容:
 * 1. PHPUnit の基礎
 * 2. ユニットテストの書き方
 * 3. モックとスタブ
 * 4. テストカバレッジ
 * 5. TDDのサイクル（Red-Green-Refactor）
 */

echo "=== テスト駆動開発（TDD）===\n\n";

echo "このファイルでは、TDDの概念とテスト対象のクラスを定義します。\n";
echo "実際のテストは tests/ ディレクトリに配置されます。\n\n";

// ============================================
// 1. テスト対象クラス: Calculator
// ============================================

namespace App\Phase4;

/**
 * 計算機クラス
 */
class Calculator
{
    /**
     * 加算
     */
    public function add(int|float $a, int|float $b): int|float
    {
        return $a + $b;
    }

    /**
     * 減算
     */
    public function subtract(int|float $a, int|float $b): int|float
    {
        return $a - $b;
    }

    /**
     * 乗算
     */
    public function multiply(int|float $a, int|float $b): int|float
    {
        return $a * $b;
    }

    /**
     * 除算
     */
    public function divide(int|float $a, int|float $b): int|float
    {
        if ($b === 0) {
            throw new \InvalidArgumentException("ゼロで割ることはできません");
        }
        return $a / $b;
    }

    /**
     * 累乗
     */
    public function power(int|float $base, int $exponent): int|float
    {
        return $base ** $exponent;
    }

    /**
     * 平方根
     */
    public function squareRoot(int|float $number): float
    {
        if ($number < 0) {
            throw new \InvalidArgumentException("負の数の平方根は計算できません");
        }
        return sqrt($number);
    }
}

// ============================================
// 2. テスト対象クラス: StringHelper
// ============================================

/**
 * 文字列ヘルパークラス
 */
class StringHelper
{
    /**
     * 文字列を逆順にする
     */
    public function reverse(string $str): string
    {
        return strrev($str);
    }

    /**
     * パリンドロームかどうかをチェック
     */
    public function isPalindrome(string $str): bool
    {
        $cleaned = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
        return $cleaned === strrev($cleaned);
    }

    /**
     * 単語数をカウント
     */
    public function countWords(string $str): int
    {
        return str_word_count($str);
    }

    /**
     * 大文字に変換
     */
    public function toUpperCase(string $str): string
    {
        return mb_strtoupper($str);
    }

    /**
     * 小文字に変換
     */
    public function toLowerCase(string $str): string
    {
        return mb_strtolower($str);
    }

    /**
     * キャメルケースに変換
     */
    public function toCamelCase(string $str): string
    {
        $words = preg_split('/[\s_-]+/', $str);
        $camelCase = array_shift($words);
        foreach ($words as $word) {
            $camelCase .= ucfirst(strtolower($word));
        }
        return $camelCase;
    }

    /**
     * スネークケースに変換
     */
    public function toSnakeCase(string $str): string
    {
        $snake = preg_replace('/([A-Z])/', '_$1', $str);
        return strtolower(ltrim($snake, '_'));
    }
}

// ============================================
// 3. テスト対象クラス: UserValidator
// ============================================

/**
 * ユーザーバリデーター
 */
class UserValidator
{
    /**
     * メールアドレスの検証
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * パスワードの検証
     * - 最低8文字
     * - 大文字を含む
     * - 小文字を含む
     * - 数字を含む
     */
    public function validatePassword(string $password): bool
    {
        if (strlen($password) < 8) {
            return false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * ユーザー名の検証
     * - 3文字以上20文字以下
     * - 英数字とアンダースコアのみ
     */
    public function validateUsername(string $username): bool
    {
        if (strlen($username) < 3 || strlen($username) > 20) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9_]+$/', $username) === 1;
    }

    /**
     * 年齢の検証
     * - 0歳以上150歳以下
     */
    public function validateAge(int $age): bool
    {
        return $age >= 0 && $age <= 150;
    }
}

// ============================================
// 4. テスト対象クラス: ShoppingCart（依存性のあるクラス）
// ============================================

/**
 * 価格計算インターフェース
 */
interface PriceCalculator
{
    public function calculateTotal(array $items): float;
    public function calculateTax(float $amount): float;
}

/**
 * 標準価格計算
 */
class StandardPriceCalculator implements PriceCalculator
{
    public function __construct(
        private readonly float $taxRate = 0.1,
    ) {}

    public function calculateTotal(array $items): float
    {
        return array_reduce(
            $items,
            fn(float $total, array $item) => $total + ($item['price'] * $item['quantity']),
            0.0
        );
    }

    public function calculateTax(float $amount): float
    {
        return $amount * $this->taxRate;
    }
}

/**
 * ショッピングカート
 */
class ShoppingCart
{
    private array $items = [];

    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {}

    public function addItem(string $name, float $price, int $quantity = 1): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException("価格は0以上である必要があります");
        }

        if ($quantity < 1) {
            throw new \InvalidArgumentException("数量は1以上である必要があります");
        }

        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getItemCount(): int
    {
        return array_reduce(
            $this->items,
            fn(int $total, array $item) => $total + $item['quantity'],
            0
        );
    }

    public function getSubtotal(): float
    {
        return $this->priceCalculator->calculateTotal($this->items);
    }

    public function getTax(): float
    {
        return $this->priceCalculator->calculateTax($this->getSubtotal());
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() + $this->getTax();
    }

    public function clear(): void
    {
        $this->items = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

// ============================================
// 5. テスト対象クラス: User（エンティティ）
// ============================================

/**
 * ユーザーエンティティ
 */
class User
{
    private string $passwordHash;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $lastLoginAt = null;

    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        string $password,
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("無効なメールアドレスです");
        }

        $this->passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function updateEmail(string $newEmail): void
    {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("無効なメールアドレスです");
        }
        $this->email = $newEmail;
    }

    public function updatePassword(string $newPassword): void
    {
        $this->passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
    }

    public function recordLogin(): void
    {
        $this->lastLoginAt = new \DateTimeImmutable();
    }

    public function getDaysSinceCreation(): int
    {
        $now = new \DateTimeImmutable();
        return (int)$now->diff($this->createdAt)->days;
    }
}

// ============================================
// 使用例
// ============================================

echo "--- 使用例 ---\n\n";

// Calculator
echo "1. Calculator:\n";
$calc = new Calculator();
echo "  10 + 5 = " . $calc->add(10, 5) . "\n";
echo "  10 - 5 = " . $calc->subtract(10, 5) . "\n";
echo "  10 * 5 = " . $calc->multiply(10, 5) . "\n";
echo "  10 / 5 = " . $calc->divide(10, 5) . "\n";
echo "  2 ^ 3 = " . $calc->power(2, 3) . "\n";
echo "  √16 = " . $calc->squareRoot(16) . "\n\n";

// StringHelper
echo "2. StringHelper:\n";
$strHelper = new StringHelper();
echo "  reverse('hello') = " . $strHelper->reverse('hello') . "\n";
echo "  isPalindrome('racecar') = " . ($strHelper->isPalindrome('racecar') ? 'true' : 'false') . "\n";
echo "  countWords('Hello World PHP') = " . $strHelper->countWords('Hello World PHP') . "\n";
echo "  toCamelCase('hello_world_php') = " . $strHelper->toCamelCase('hello_world_php') . "\n";
echo "  toSnakeCase('HelloWorldPhp') = " . $strHelper->toSnakeCase('HelloWorldPhp') . "\n\n";

// UserValidator
echo "3. UserValidator:\n";
$validator = new UserValidator();
echo "  validateEmail('test@example.com') = " . ($validator->validateEmail('test@example.com') ? 'true' : 'false') . "\n";
echo "  validateEmail('invalid-email') = " . ($validator->validateEmail('invalid-email') ? 'true' : 'false') . "\n";
echo "  validatePassword('Password123') = " . ($validator->validatePassword('Password123') ? 'true' : 'false') . "\n";
echo "  validatePassword('weak') = " . ($validator->validatePassword('weak') ? 'true' : 'false') . "\n";
echo "  validateUsername('john_doe') = " . ($validator->validateUsername('john_doe') ? 'true' : 'false') . "\n";
echo "  validateAge(25) = " . ($validator->validateAge(25) ? 'true' : 'false') . "\n\n";

// ShoppingCart
echo "4. ShoppingCart:\n";
$priceCalc = new StandardPriceCalculator(0.1);
$cart = new ShoppingCart($priceCalc);
$cart->addItem('ノートPC', 100000, 1);
$cart->addItem('マウス', 2000, 2);
echo "  商品数: " . $cart->getItemCount() . "\n";
echo "  小計: ¥" . number_format($cart->getSubtotal()) . "\n";
echo "  消費税: ¥" . number_format($cart->getTax()) . "\n";
echo "  合計: ¥" . number_format($cart->getTotal()) . "\n\n";

// User
echo "5. User:\n";
$user = new User(1, 'alice', 'alice@example.com', 'SecurePassword123');
echo "  ユーザー名: " . $user->getUsername() . "\n";
echo "  メール: " . $user->getEmail() . "\n";
echo "  パスワード検証: " . ($user->verifyPassword('SecurePassword123') ? 'OK' : 'NG') . "\n";
echo "  作成日: " . $user->getCreatedAt()->format('Y-m-d H:i:s') . "\n\n";

echo "=== Phase 4.3 学習ガイド ===\n";
echo "\n";
echo "【TDDのサイクル】\n";
echo "1. Red: 失敗するテストを書く\n";
echo "2. Green: テストを通す最小限のコードを書く\n";
echo "3. Refactor: コードをリファクタリングする\n";
echo "\n";
echo "【テスト実行コマンド】\n";
echo "  composer test              # すべてのテストを実行\n";
echo "  composer test -- --filter Calculator  # 特定のテストを実行\n";
echo "  composer test -- --coverage-html coverage  # カバレッジレポートを生成\n";
echo "\n";
echo "【学習のポイント】\n";
echo "- 各メソッドごとにテストケースを作成する\n";
echo "- 正常系と異常系の両方をテストする\n";
echo "- エッジケース（境界値）をテストする\n";
echo "- モックを使って外部依存を切り離す\n";
echo "- テストカバレッジを80%以上に保つ\n";
echo "\n";
echo "次のステップ: tests/ ディレクトリのテストファイルを確認してください\n";
