<?php

declare(strict_types=1);

/**
 * Phase 2.4: 例外処理
 *
 * このファイルでは、PHPの例外処理について学習します。
 * try-catch-finally、カスタム例外クラス、例外チェーンなどを理解します。
 *
 * 学習内容:
 * 1. 例外の基本（throw と catch）
 * 2. try-catch-finally
 * 3. 複数の例外をキャッチ
 * 4. カスタム例外クラス
 * 5. 例外チェーン
 * 6. 例外のベストプラクティス
 */

echo "=== Phase 2.4: 例外処理 ===\n\n";

// ============================================================
// 1. 例外の基本（throw と catch）
// ============================================================

echo "--- 1. 例外の基本（throw と catch） ---\n\n";

/**
 * 例外とは？
 *
 * 例外は、プログラムの通常のフローを中断し、エラー状態を通知する仕組みです。
 * throw で例外を投げ、catch でキャッチします。
 */

echo "【基本的な例外処理】\n\n";

/**
 * 年齢をチェックする関数
 *
 * @param int $age 年齢
 * @return string メッセージ
 * @throws Exception 年齢が負の場合
 */
function checkAge(int $age): string
{
    if ($age < 0) {
        throw new Exception('年齢は0以上である必要があります');
    }

    return "年齢: {$age}歳";
}

try {
    echo checkAge(25) . "\n";
    echo checkAge(-5) . "\n"; // 例外が投げられる
    echo "この行は実行されません\n";
} catch (Exception $e) {
    echo "エラーをキャッチしました: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================
// 2. try-catch-finally
// ============================================================

echo "--- 2. try-catch-finally ---\n\n";

/**
 * finally ブロック
 *
 * 例外が発生してもしなくても、必ず実行されるコードを記述します。
 * リソースの解放（ファイルクローズ、DB接続クローズ）に便利です。
 */

echo "【finally ブロックの使用】\n\n";

/**
 * ファイルを読み込む関数
 *
 * @param string $filename ファイル名
 * @return string ファイルの内容
 * @throws RuntimeException ファイルが開けない場合
 */
function readFile(string $filename): string
{
    $handle = fopen($filename, 'r');

    if ($handle === false) {
        throw new RuntimeException("ファイルを開けませんでした: {$filename}");
    }

    try {
        $content = fread($handle, 1024);
        if ($content === false) {
            throw new RuntimeException("ファイルを読み込めませんでした");
        }
        return $content;
    } finally {
        // 例外が発生してもしなくても、ファイルをクローズ
        fclose($handle);
        echo "ファイルをクローズしました\n";
    }
}

try {
    // 存在しないファイルを読み込もうとする
    // readFile('nonexistent.txt');
    echo "※ファイル読み込みのデモはコメントアウトしています\n";
} catch (RuntimeException $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================
// 3. 複数の例外をキャッチ
// ============================================================

echo "--- 3. 複数の例外をキャッチ ---\n\n";

/**
 * 複数の catch ブロック
 *
 * 異なる種類の例外を別々に処理できます。
 */

echo "【複数の catch ブロック】\n\n";

/**
 * 割り算を実行する関数
 *
 * @param int|float $a 被除数
 * @param int|float $b 除数
 * @return float 結果
 * @throws InvalidArgumentException 引数が不正な場合
 * @throws DivisionByZeroError ゼロ除算の場合
 */
function divide(int|float $a, int|float $b): float
{
    if (!is_numeric($a) || !is_numeric($b)) {
        throw new InvalidArgumentException('引数は数値である必要があります');
    }

    if ($b === 0) {
        throw new DivisionByZeroError('ゼロで割ることはできません');
    }

    return $a / $b;
}

// 正常なケース
try {
    echo "10 ÷ 2 = " . divide(10, 2) . "\n";
} catch (InvalidArgumentException $e) {
    echo "引数エラー: " . $e->getMessage() . "\n";
} catch (DivisionByZeroError $e) {
    echo "ゼロ除算エラー: " . $e->getMessage() . "\n";
}

// ゼロ除算のケース
try {
    echo "10 ÷ 0 = " . divide(10, 0) . "\n";
} catch (InvalidArgumentException $e) {
    echo "引数エラー: " . $e->getMessage() . "\n";
} catch (DivisionByZeroError $e) {
    echo "ゼロ除算エラー: " . $e->getMessage() . "\n";
}

echo "\n";

/**
 * PHP 7.1+ の複数例外の一括キャッチ
 */

echo "【複数の例外を一括でキャッチ（PHP 7.1+）】\n\n";

try {
    throw new RuntimeException('実行時エラー');
} catch (InvalidArgumentException | RuntimeException $e) {
    echo "引数エラーまたは実行時エラー: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================
// 4. カスタム例外クラス
// ============================================================

echo "--- 4. カスタム例外クラス ---\n\n";

/**
 * カスタム例外クラス
 *
 * Exception クラスを継承して、独自の例外クラスを作成できます。
 */

/**
 * バリデーション例外
 */
class ValidationException extends Exception
{
    /**
     * コンストラクタ
     *
     * @param string $field フィールド名
     * @param string $message エラーメッセージ
     */
    public function __construct(
        private string $field,
        string $message = '',
    ) {
        parent::__construct($message ?: "{$field} の検証に失敗しました");
    }

    /**
     * フィールド名を取得
     *
     * @return string フィールド名
     */
    public function getField(): string
    {
        return $this->field;
    }
}

/**
 * データベース例外
 */
class DatabaseException extends Exception
{
    /**
     * コンストラクタ
     *
     * @param string $query SQLクエリ
     * @param string $message エラーメッセージ
     */
    public function __construct(
        private string $query,
        string $message = 'データベースエラーが発生しました',
    ) {
        parent::__construct($message);
    }

    /**
     * SQLクエリを取得
     *
     * @return string SQLクエリ
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}

/**
 * ユーザーが見つからない例外
 */
class UserNotFoundException extends Exception
{
    /**
     * コンストラクタ
     *
     * @param int $userId ユーザーID
     */
    public function __construct(
        private int $userId,
    ) {
        parent::__construct("ユーザーID {$userId} が見つかりませんでした");
    }

    /**
     * ユーザーIDを取得
     *
     * @return int ユーザーID
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}

echo "【カスタム例外の使用例】\n\n";

/**
 * メールアドレスを検証する
 *
 * @param string $email メールアドレス
 * @throws ValidationException 検証に失敗した場合
 */
function validateEmail(string $email): void
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException('email', 'メールアドレスの形式が正しくありません');
    }
}

try {
    validateEmail('invalid-email');
} catch (ValidationException $e) {
    echo "検証エラー: " . $e->getMessage() . "\n";
    echo "フィールド: " . $e->getField() . "\n";
}

echo "\n";

// ============================================================
// 5. 例外チェーン
// ============================================================

echo "--- 5. 例外チェーン ---\n\n";

/**
 * 例外チェーン
 *
 * ある例外をキャッチして、別の例外を投げる際に、
 * 元の例外を第3引数で渡すことで例外の連鎖を保持できます。
 */

echo "【例外チェーンの使用】\n\n";

/**
 * ユーザーを取得する
 *
 * @param int $userId ユーザーID
 * @return array ユーザー情報
 * @throws UserNotFoundException ユーザーが見つからない場合
 */
function getUser(int $userId): array
{
    try {
        // データベースアクセスをシミュレート
        throw new DatabaseException(
            "SELECT * FROM users WHERE id = {$userId}",
            'データベース接続に失敗しました'
        );
    } catch (DatabaseException $e) {
        // DatabaseException を UserNotFoundException に変換
        // 元の例外（$e）を第3引数で渡す
        throw new UserNotFoundException($userId);
    }
}

try {
    getUser(123);
} catch (UserNotFoundException $e) {
    echo "エラー: " . $e->getMessage() . "\n";

    // 元の例外を取得
    $previous = $e->getPrevious();
    if ($previous !== null) {
        echo "原因: " . $previous->getMessage() . "\n";
    }
}

echo "\n";

// ============================================================
// 6. 例外の情報を取得する
// ============================================================

echo "--- 6. 例外の情報を取得する ---\n\n";

/**
 * Exception クラスの主なメソッド
 */

echo "【Exception クラスの主なメソッド】\n\n";

try {
    throw new RuntimeException('エラーが発生しました', 500);
} catch (Exception $e) {
    echo "getMessage(): " . $e->getMessage() . "\n";
    echo "getCode(): " . $e->getCode() . "\n";
    echo "getFile(): " . $e->getFile() . "\n";
    echo "getLine(): " . $e->getLine() . "\n";
    echo "getTrace(): " . print_r($e->getTrace(), true);
    echo "getTraceAsString():\n" . $e->getTraceAsString() . "\n";
}

echo "\n";

// ============================================================
// 7. set_exception_handler
// ============================================================

echo "--- 7. set_exception_handler ---\n\n";

/**
 * グローバル例外ハンドラ
 *
 * キャッチされなかった例外を処理するハンドラを登録できます。
 */

echo "【グローバル例外ハンドラの登録】\n\n";

/**
 * グローバル例外ハンドラ
 *
 * @param Throwable $exception 例外
 */
function globalExceptionHandler(Throwable $exception): void
{
    // エラーログに記録
    error_log(sprintf(
        "[EXCEPTION] %s: %s in %s on line %d",
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    // ユーザーにはシンプルなメッセージを表示
    echo "申し訳ございません。エラーが発生しました。\n";
    echo "エラーID: " . uniqid() . "\n";

    // 開発環境では詳細を表示
    if (getenv('APP_ENV') === 'development') {
        echo "\n【デバッグ情報】\n";
        echo "エラー: " . $exception->getMessage() . "\n";
        echo "ファイル: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
    }
}

// グローバル例外ハンドラを登録（実際には使用しない）
// set_exception_handler('globalExceptionHandler');

echo "グローバル例外ハンドラの登録方法:\n";
echo "set_exception_handler('globalExceptionHandler');\n\n";

// ============================================================
// 8. 実践例: サービスクラスでの例外処理
// ============================================================

echo "--- 8. 実践例: サービスクラスでの例外処理 ---\n\n";

/**
 * ユーザーサービス
 */
class UserService
{
    /** @var array<int, array> */
    private array $users = [];
    private int $nextId = 1;

    /**
     * ユーザーを作成
     *
     * @param string $name 名前
     * @param string $email メールアドレス
     * @return array 作成されたユーザー
     * @throws ValidationException 検証に失敗した場合
     */
    public function createUser(string $name, string $email): array
    {
        // 名前の検証
        if (empty($name)) {
            throw new ValidationException('name', '名前は必須です');
        }

        if (strlen($name) < 2) {
            throw new ValidationException('name', '名前は2文字以上である必要があります');
        }

        // メールアドレスの検証
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('email', 'メールアドレスの形式が正しくありません');
        }

        // 重複チェック
        foreach ($this->users as $user) {
            if ($user['email'] === $email) {
                throw new ValidationException('email', 'このメールアドレスは既に使用されています');
            }
        }

        // ユーザーを作成
        $user = [
            'id' => $this->nextId++,
            'name' => $name,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->users[$user['id']] = $user;

        return $user;
    }

    /**
     * ユーザーを取得
     *
     * @param int $userId ユーザーID
     * @return array ユーザー情報
     * @throws UserNotFoundException ユーザーが見つからない場合
     */
    public function findUser(int $userId): array
    {
        if (!isset($this->users[$userId])) {
            throw new UserNotFoundException($userId);
        }

        return $this->users[$userId];
    }

    /**
     * ユーザーを削除
     *
     * @param int $userId ユーザーID
     * @throws UserNotFoundException ユーザーが見つからない場合
     */
    public function deleteUser(int $userId): void
    {
        if (!isset($this->users[$userId])) {
            throw new UserNotFoundException($userId);
        }

        unset($this->users[$userId]);
    }
}

// UserService を使用
$userService = new UserService();

echo "【ユーザーサービスのテスト】\n\n";

// 正常なユーザー作成
try {
    $user = $userService->createUser('太郎', 'taro@example.com');
    echo "ユーザーを作成しました: {$user['name']} ({$user['email']})\n";
} catch (ValidationException $e) {
    echo "検証エラー [{$e->getField()}]: {$e->getMessage()}\n";
}

// バリデーションエラー: 名前が短い
try {
    $userService->createUser('A', 'a@example.com');
} catch (ValidationException $e) {
    echo "検証エラー [{$e->getField()}]: {$e->getMessage()}\n";
}

// バリデーションエラー: メールアドレスが不正
try {
    $userService->createUser('花子', 'invalid-email');
} catch (ValidationException $e) {
    echo "検証エラー [{$e->getField()}]: {$e->getMessage()}\n";
}

// ユーザーが見つからない
try {
    $userService->findUser(999);
} catch (UserNotFoundException $e) {
    echo "エラー: {$e->getMessage()}\n";
}

echo "\n";

// ============================================================
// 9. ベストプラクティス
// ============================================================

echo "--- 9. ベストプラクティス ---\n\n";

echo "【例外処理のベストプラクティス】\n\n";

echo "1. 具体的な例外クラスを作成する\n";
echo "   ✅ ValidationException, UserNotFoundException\n";
echo "   ❌ 汎用的な Exception のみ使用\n\n";

echo "2. 例外メッセージは明確で具体的に\n";
echo "   ✅ 'ユーザーID 123 が見つかりませんでした'\n";
echo "   ❌ 'エラーが発生しました'\n\n";

echo "3. 例外はキャッチできる場所でキャッチ\n";
echo "   ✅ コントローラー層で統一的にキャッチ\n";
echo "   ❌ あらゆる場所で try-catch\n\n";

echo "4. 例外を握りつぶさない\n";
echo "   ❌ catch (Exception \$e) { /* 何もしない */ }\n";
echo "   ✅ ログに記録、再スロー、または適切に処理\n\n";

echo "5. finally でリソースを解放\n";
echo "   ファイル、DB接続、ネットワーク接続など\n\n";

echo "6. 例外チェーンで文脈を保持\n";
echo "   throw new CustomException(\$message, 0, \$previous);\n\n";

echo "7. ビジネスロジックの制御に例外を使わない\n";
echo "   ❌ 通常のフロー制御に例外を使う\n";
echo "   ✅ 例外は異常系のみ\n\n";

echo "8. 本番環境では詳細なエラーメッセージを表示しない\n";
echo "   セキュリティリスクを避けるため\n\n";

// ============================================================
// 10. 例外 vs エラーコード
// ============================================================

echo "--- 10. 例外 vs エラーコード ---\n\n";

echo "【エラーコードを返す方法（古い方法）】\n\n";

echo "function divide(\$a, \$b) {\n";
echo "    if (\$b === 0) {\n";
echo "        return false; // エラーコード\n";
echo "    }\n";
echo "    return \$a / \$b;\n";
echo "}\n";
echo "\n";
echo "\$result = divide(10, 0);\n";
echo "if (\$result === false) {\n";
echo "    echo 'エラー';\n";
echo "}\n\n";

echo "問題点:\n";
echo "- 戻り値とエラーコードが混在\n";
echo "- エラーチェックを忘れやすい\n";
echo "- エラーの詳細情報を伝えにくい\n\n";

echo "【例外を使う方法（モダンな方法）】\n\n";

echo "function divide(\$a, \$b) {\n";
echo "    if (\$b === 0) {\n";
echo "        throw new DivisionByZeroError();\n";
echo "    }\n";
echo "    return \$a / \$b;\n";
echo "}\n";
echo "\n";
echo "try {\n";
echo "    \$result = divide(10, 0);\n";
echo "} catch (DivisionByZeroError \$e) {\n";
echo "    echo 'エラー: ' . \$e->getMessage();\n";
echo "}\n\n";

echo "利点:\n";
echo "- 戻り値とエラーが分離\n";
echo "- エラーを無視できない（キャッチしないと停止）\n";
echo "- エラーの詳細情報を例外オブジェクトで伝達\n";
echo "- スタックトレースで原因を追跡しやすい\n\n";

// ============================================================
// まとめ
// ============================================================

echo "\n=== まとめ ===\n\n";

echo "例外処理の重要なポイント:\n";
echo "1. throw で例外を投げ、catch でキャッチする\n";
echo "2. finally は例外の有無に関わらず実行される\n";
echo "3. 複数の catch ブロックで異なる例外を処理できる\n";
echo "4. カスタム例外クラスを作成して用途別に使い分ける\n";
echo "5. 例外チェーンで元の例外を保持する\n";
echo "6. set_exception_handler でグローバルハンドラを登録\n";
echo "7. 例外は異常系のみに使用し、通常フローには使わない\n";
echo "8. ビジネスロジックに応じた適切な例外クラスを定義する\n\n";

echo "次のステップ:\n";
echo "- exercises/09_error_practice.php で実践的な演習に挑戦\n";
echo "- エラーハンドリングと例外処理を組み合わせた実装を学ぶ\n\n";

echo "=== 学習完了 ===\n";
