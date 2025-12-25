<?php

declare(strict_types=1);

/**
 * Phase 1.1: 変数スコープの学習プログラム
 *
 * このプログラムでは以下を学習します:
 * - グローバルスコープとローカルスコープ
 * - 関数内での変数の扱い
 * - global キーワード
 * - static 変数
 * - スーパーグローバル変数
 */

echo "==================================" . PHP_EOL;
echo "  Phase 1.1: 変数スコープ" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 1. グローバルスコープとローカルスコープ
// ============================================

echo "【1. グローバルスコープとローカルスコープ】" . PHP_EOL;
echo "---" . PHP_EOL;

// グローバル変数
$globalVariable = "グローバル変数";

/**
 * ローカル変数のデモンストレーション
 */
function demonstrateLocalScope(): void
{
    // ローカル変数（この関数内でのみ有効）
    $localVariable = "ローカル変数";
    echo "関数内: {$localVariable}" . PHP_EOL;

    // グローバル変数には直接アクセスできない
    // echo $globalVariable; // エラー: 未定義変数
}

demonstrateLocalScope();

echo "関数外: {$globalVariable}" . PHP_EOL;
// echo $localVariable; // エラー: 未定義変数
echo PHP_EOL;

// ============================================
// 2. global キーワード
// ============================================

echo "【2. global キーワード】" . PHP_EOL;
echo "---" . PHP_EOL;

$counter = 0;

/**
 * グローバル変数にアクセスする（非推奨の方法）
 */
function incrementCounterWithGlobal(): void
{
    global $counter;
    $counter++;
    echo "カウンター（global使用）: {$counter}" . PHP_EOL;
}

echo "初期値: {$counter}" . PHP_EOL;
incrementCounterWithGlobal();
incrementCounterWithGlobal();
echo "関数実行後: {$counter}" . PHP_EOL;
echo PHP_EOL;

echo "※ global キーワードは使用を避け、引数と戻り値を使うことを推奨します" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 3. 引数と戻り値を使った推奨パターン
// ============================================

echo "【3. 引数と戻り値を使った推奨パターン】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * カウンターをインクリメントする（推奨の方法）
 *
 * @param int $count 現在のカウント
 * @return int インクリメントされたカウント
 */
function incrementCounter(int $count): int
{
    return $count + 1;
}

$myCounter = 0;
echo "初期値: {$myCounter}" . PHP_EOL;

$myCounter = incrementCounter($myCounter);
echo "1回目実行後: {$myCounter}" . PHP_EOL;

$myCounter = incrementCounter($myCounter);
echo "2回目実行後: {$myCounter}" . PHP_EOL;

echo PHP_EOL;

// ============================================
// 4. static 変数
// ============================================

echo "【4. static 変数】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 関数呼び出し回数をカウントする
 *
 * @return int 呼び出し回数
 */
function countFunctionCalls(): int
{
    // static変数は関数の呼び出し間で値を保持する
    static $callCount = 0;
    $callCount++;
    return $callCount;
}

echo "1回目の呼び出し: " . countFunctionCalls() . "回目" . PHP_EOL;
echo "2回目の呼び出し: " . countFunctionCalls() . "回目" . PHP_EOL;
echo "3回目の呼び出し: " . countFunctionCalls() . "回目" . PHP_EOL;
echo "4回目の呼び出し: " . countFunctionCalls() . "回目" . PHP_EOL;
echo PHP_EOL;

/**
 * キャッシュ機能のデモンストレーション
 *
 * @param string $key キャッシュキー
 * @param string|null $value 保存する値（nullの場合は取得）
 * @return string|null キャッシュされた値
 */
function cache(string $key, ?string $value = null): ?string
{
    static $cacheData = [];

    if ($value !== null) {
        $cacheData[$key] = $value;
        echo "キャッシュに保存: {$key} = {$value}" . PHP_EOL;
    }

    return $cacheData[$key] ?? null;
}

// キャッシュに値を保存
cache("user_name", "山田太郎");
cache("user_email", "taro@example.com");

// キャッシュから値を取得
$name = cache("user_name");
$email = cache("user_email");

echo "キャッシュから取得: 名前 = {$name}, メール = {$email}" . PHP_EOL;
echo PHP_EOL;

// ============================================
// 5. スーパーグローバル変数
// ============================================

echo "【5. スーパーグローバル変数】" . PHP_EOL;
echo "---" . PHP_EOL;

echo "PHPには、どこからでもアクセスできるスーパーグローバル変数があります:" . PHP_EOL;
echo PHP_EOL;

/**
 * スーパーグローバル変数の説明を表示
 */
function explainSuperGlobals(): void
{
    echo "スーパーグローバル変数（関数内からもアクセス可能）:" . PHP_EOL;
    echo "  - \$_GET: URLパラメータ" . PHP_EOL;
    echo "  - \$_POST: POSTデータ" . PHP_EOL;
    echo "  - \$_SERVER: サーバー情報" . PHP_EOL;
    echo "  - \$_SESSION: セッションデータ" . PHP_EOL;
    echo "  - \$_COOKIE: クッキーデータ" . PHP_EOL;
    echo "  - \$_FILES: アップロードファイル" . PHP_EOL;
    echo "  - \$_ENV: 環境変数" . PHP_EOL;
    echo "  - \$GLOBALS: グローバル変数の配列" . PHP_EOL;
}

explainSuperGlobals();
echo PHP_EOL;

// $_SERVERの例
echo "\$_SERVER の例:" . PHP_EOL;
echo "  PHPバージョン: " . phpversion() . PHP_EOL;
echo "  スクリプト名: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . PHP_EOL;
echo "  実行ユーザー: " . get_current_user() . PHP_EOL;
echo PHP_EOL;

// ============================================
// 6. 実践例：計算機クラス
// ============================================

echo "【6. 実践例：計算機クラス】" . PHP_EOL;
echo "---" . PHP_EOL;

/**
 * 計算機クラス
 *
 * クラスを使うことで、状態（プロパティ）を適切に管理できる
 */
class Calculator
{
    /**
     * @var int 現在の値
     */
    private int $value = 0;

    /**
     * @var int 計算回数
     */
    private static int $calculationCount = 0;

    /**
     * 値を加算する
     *
     * @param int $number 加算する数
     * @return self
     */
    public function add(int $number): self
    {
        $this->value += $number;
        self::$calculationCount++;
        return $this;
    }

    /**
     * 値を減算する
     *
     * @param int $number 減算する数
     * @return self
     */
    public function subtract(int $number): self
    {
        $this->value -= $number;
        self::$calculationCount++;
        return $this;
    }

    /**
     * 値を乗算する
     *
     * @param int $number 乗算する数
     * @return self
     */
    public function multiply(int $number): self
    {
        $this->value *= $number;
        self::$calculationCount++;
        return $this;
    }

    /**
     * 現在の値を取得する
     *
     * @return int 現在の値
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * 総計算回数を取得する（static）
     *
     * @return int 総計算回数
     */
    public static function getTotalCalculations(): int
    {
        return self::$calculationCount;
    }

    /**
     * 値をリセットする
     *
     * @return self
     */
    public function reset(): self
    {
        $this->value = 0;
        return $this;
    }
}

// メソッドチェーンで計算
$calc = new Calculator();
$result = $calc
    ->add(10)
    ->add(5)
    ->multiply(2)
    ->subtract(3)
    ->getValue();

echo "計算結果: ((10 + 5) * 2) - 3 = {$result}" . PHP_EOL;
echo "総計算回数: " . Calculator::getTotalCalculations() . "回" . PHP_EOL;
echo PHP_EOL;

// ============================================
// まとめ
// ============================================

echo "==================================" . PHP_EOL;
echo "  学習のポイント" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

echo "✅ 変数スコープの種類:" . PHP_EOL;
echo "   - グローバルスコープ: ファイル全体で有効" . PHP_EOL;
echo "   - ローカルスコープ: 関数・メソッド内のみ有効" . PHP_EOL;
echo "   - static変数: 関数呼び出し間で値を保持" . PHP_EOL;
echo "   - クラスプロパティ: オブジェクト内で管理" . PHP_EOL;
echo PHP_EOL;

echo "✅ ベストプラクティス:" . PHP_EOL;
echo "   - global キーワードは避け、引数と戻り値を使う" . PHP_EOL;
echo "   - 状態管理にはクラスを使う" . PHP_EOL;
echo "   - スーパーグローバル変数の直接使用は最小限に" . PHP_EOL;
echo "   - 関数は純粋関数（副作用なし）を目指す" . PHP_EOL;
echo PHP_EOL;

echo "✅ 実践テクニック:" . PHP_EOL;
echo "   - static変数でキャッシュや呼び出し回数を記録" . PHP_EOL;
echo "   - クラスのstaticプロパティで全インスタンス共通のデータ管理" . PHP_EOL;
echo "   - メソッドチェーンで読みやすいコード" . PHP_EOL;
echo PHP_EOL;

echo "✅ 次のステップ:" . PHP_EOL;
echo "   → exercises/01_variable_practice.php で演習課題に挑戦します" . PHP_EOL;
echo PHP_EOL;
