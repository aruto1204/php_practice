<?php

declare(strict_types=1);

/**
 * Phase 2.4: エラーハンドリングの基礎
 *
 * このファイルでは、PHPのエラーハンドリングについて学習します。
 * エラーと例外の違い、エラーの種類、エラーハンドリングの方法を理解します。
 *
 * 学習内容:
 * 1. エラーと例外の違い
 * 2. エラーの種類とレベル
 * 3. エラー報告の設定
 * 4. set_error_handler によるカスタムエラーハンドラ
 * 5. エラーを例外に変換する
 * 6. エラーログの記録
 */

echo "=== Phase 2.4: エラーハンドリングの基礎 ===\n\n";

// ============================================================
// 1. エラーと例外の違い
// ============================================================

echo "--- 1. エラーと例外の違い ---\n\n";

/**
 * エラーと例外の違い
 *
 * エラー (Error):
 * - PHP の実行時に発生する問題（文法エラー、致命的エラーなど）
 * - 通常、スクリプトの実行を停止させる
 * - エラーハンドラで処理できる
 *
 * 例外 (Exception):
 * - プログラマが意図的に投げる（throw）オブジェクト
 * - try-catch でキャッチして処理できる
 * - オブジェクト指向的なエラー処理
 */

echo "【エラーと例外の違い】\n\n";

echo "エラー:\n";
echo "- PHP自身が検出する問題\n";
echo "- 例: 未定義変数、型の不一致、ファイルが見つからないなど\n";
echo "- エラーレベルで分類される（E_ERROR, E_WARNING, E_NOTICEなど）\n\n";

echo "例外:\n";
echo "- プログラマが意図的に投げる\n";
echo "- 例: バリデーションエラー、ビジネスロジックエラーなど\n";
echo "- try-catch で制御できる\n\n";

echo "PHP 7以降:\n";
echo "- 多くの致命的エラーが Error クラスの例外として投げられる\n";
echo "- Error と Exception の両方を Throwable でキャッチできる\n\n";

// ============================================================
// 2. エラーの種類とレベル
// ============================================================

echo "--- 2. エラーの種類とレベル ---\n\n";

/**
 * PHPのエラーレベル
 */

echo "【主なエラーレベル】\n\n";

echo "E_ERROR (1):\n";
echo "- 致命的なエラー（実行を停止）\n";
echo "- 例: 未定義関数の呼び出し\n\n";

echo "E_WARNING (2):\n";
echo "- 警告（実行は継続）\n";
echo "- 例: include で存在しないファイルを読み込む\n\n";

echo "E_NOTICE (8):\n";
echo "- 通知（軽微な問題）\n";
echo "- 例: 未定義変数の使用\n\n";

echo "E_PARSE (4):\n";
echo "- 構文解析エラー\n";
echo "- 例: 文法ミス\n\n";

echo "E_DEPRECATED (8192):\n";
echo "- 非推奨機能の使用\n";
echo "- 例: 古い関数の使用\n\n";

echo "E_STRICT (2048):\n";
echo "- コーディング規約の推奨事項\n\n";

echo "E_ALL:\n";
echo "- すべてのエラーと警告（推奨設定）\n\n";

// ============================================================
// 3. エラー報告の設定
// ============================================================

echo "--- 3. エラー報告の設定 ---\n\n";

/**
 * error_reporting と ini_set
 *
 * エラー報告のレベルと表示方法を設定します。
 */

echo "【開発環境での推奨設定】\n\n";

echo "<?php\n";
echo "// すべてのエラーを報告\n";
echo "error_reporting(E_ALL);\n";
echo "\n";
echo "// エラーを画面に表示\n";
echo "ini_set('display_errors', '1');\n";
echo "\n";
echo "// エラーログに記録\n";
echo "ini_set('log_errors', '1');\n";
echo "ini_set('error_log', '/path/to/error.log');\n\n";

echo "【本番環境での推奨設定】\n\n";

echo "<?php\n";
echo "// すべてのエラーを報告（ログに記録）\n";
echo "error_reporting(E_ALL);\n";
echo "\n";
echo "// エラーを画面に表示しない（セキュリティのため）\n";
echo "ini_set('display_errors', '0');\n";
echo "\n";
echo "// エラーログに記録\n";
echo "ini_set('log_errors', '1');\n";
echo "ini_set('error_log', '/var/log/php/error.log');\n\n";

// 現在の設定を表示
echo "【現在のエラー報告設定】\n";
echo "error_reporting: " . error_reporting() . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n\n";

// ============================================================
// 4. set_error_handler によるカスタムエラーハンドラ
// ============================================================

echo "--- 4. カスタムエラーハンドラ ---\n\n";

/**
 * カスタムエラーハンドラ
 *
 * set_error_handler でエラー発生時の処理をカスタマイズできます。
 */

echo "【カスタムエラーハンドラの実装】\n\n";

/**
 * エラーハンドラ関数
 *
 * @param int $errno エラーレベル
 * @param string $errstr エラーメッセージ
 * @param string $errfile エラーが発生したファイル
 * @param int $errline エラーが発生した行番号
 * @return bool true を返すとPHPの標準エラーハンドラを実行しない
 */
function customErrorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
{
    // エラーレベルの名前を取得
    $errorType = match ($errno) {
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_DEPRECATED => 'DEPRECATED',
        E_STRICT => 'STRICT',
        default => 'UNKNOWN',
    };

    // エラーメッセージをフォーマット
    $message = sprintf(
        "[%s] %s in %s on line %d\n",
        $errorType,
        $errstr,
        $errfile,
        $errline
    );

    // エラーを表示
    echo "【カスタムエラーハンドラ】\n";
    echo $message;

    // エラーログに記録（実際にはファイルに書き込む）
    // error_log($message);

    // true を返すとPHPの標準エラーハンドラを実行しない
    return true;
}

// カスタムエラーハンドラを登録
set_error_handler('customErrorHandler');

echo "カスタムエラーハンドラを登録しました\n\n";

// エラーを発生させてテスト（警告レベル）
echo "【テスト: 未定義変数の使用】\n";
// @を付けてエラーを抑制しない場合、カスタムハンドラが呼ばれる
// echo $undefinedVariable; // これを実行するとエラーが発生
echo "※実際のエラーはコメントアウトしています\n\n";

// エラーハンドラを元に戻す
restore_error_handler();
echo "エラーハンドラを元に戻しました\n\n";

// ============================================================
// 5. エラーを例外に変換する
// ============================================================

echo "--- 5. エラーを例外に変換する ---\n\n";

/**
 * エラーを例外に変換するエラーハンドラ
 *
 * エラーが発生したら ErrorException として投げることで、
 * try-catch で統一的に処理できます。
 */

echo "【エラーを例外に変換するハンドラ】\n\n";

/**
 * エラーを ErrorException に変換するハンドラ
 *
 * @param int $errno エラーレベル
 * @param string $errstr エラーメッセージ
 * @param string $errfile エラーが発生したファイル
 * @param int $errline エラーが発生した行番号
 * @throws ErrorException
 */
function errorToExceptionHandler(int $errno, string $errstr, string $errfile, int $errline): bool
{
    // ErrorException として投げる
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

echo "コード例:\n";
echo "<?php\n";
echo "set_error_handler('errorToExceptionHandler');\n";
echo "\n";
echo "try {\n";
echo "    // エラーが発生すると例外として投げられる\n";
echo "    \$file = fopen('nonexistent.txt', 'r');\n";
echo "} catch (ErrorException \$e) {\n";
echo "    echo \"エラーをキャッチ: \" . \$e->getMessage();\n";
echo "}\n\n";

// ============================================================
// 6. エラーログの記録
// ============================================================

echo "--- 6. エラーログの記録 ---\n\n";

/**
 * error_log 関数でエラーログを記録
 */

echo "【error_log 関数の使い方】\n\n";

// エラーログに記録（実際には php.ini の error_log 設定に従う）
$logMessage = "アプリケーションエラー: データベース接続に失敗しました";
error_log($logMessage);
echo "エラーログに記録: {$logMessage}\n\n";

// ファイルに直接記録
$logFile = '/tmp/app_error.log';
error_log($logMessage . "\n", 3, $logFile);
echo "ファイルに記録: {$logFile}\n\n";

echo "【ログメッセージのフォーマット】\n\n";

/**
 * ログメッセージをフォーマットする
 *
 * @param string $level ログレベル
 * @param string $message メッセージ
 * @return string フォーマットされたログメッセージ
 */
function formatLogMessage(string $level, string $message): string
{
    $timestamp = date('Y-m-d H:i:s');
    $pid = getmypid(); // プロセスID
    return "[{$timestamp}] [{$pid}] [{$level}] {$message}";
}

$formattedMessage = formatLogMessage('ERROR', 'データベース接続エラー');
echo "フォーマット例: {$formattedMessage}\n\n";

// ============================================================
// 7. 実践例: ログクラスの実装
// ============================================================

echo "--- 7. 実践例: ログクラスの実装 ---\n\n";

/**
 * シンプルなロガークラス
 */
class SimpleLogger
{
    /**
     * コンストラクタ
     *
     * @param string $logFile ログファイルのパス
     */
    public function __construct(
        private string $logFile,
    ) {}

    /**
     * ログを記録する
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     */
    private function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [{$level}] {$message}\n";

        // ファイルに追記
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);

        // コンソールにも出力（デモ用）
        echo $formattedMessage;
    }

    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }

    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }

    public function debug(string $message): void
    {
        $this->log('DEBUG', $message);
    }
}

// ロガーを使用
$logger = new SimpleLogger('/tmp/app.log');
$logger->info('アプリケーションを起動しました');
$logger->warning('メモリ使用量が80%を超えました');
$logger->error('データベース接続に失敗しました');
$logger->debug('変数の値: x=10, y=20');

echo "\n";

// ============================================================
// 8. エラー抑制演算子 (@) の注意点
// ============================================================

echo "--- 8. エラー抑制演算子 (@) の注意点 ---\n\n";

/**
 * @ 演算子
 *
 * エラーメッセージを抑制しますが、推奨されません。
 */

echo "【@ 演算子の使用例と問題点】\n\n";

echo "❌ 悪い例（エラーを隠してしまう）:\n";
echo "<?php\n";
echo "\$file = @fopen('nonexistent.txt', 'r');\n";
echo "// エラーが発生しても何も表示されない\n\n";

echo "✅ 良い例（適切にエラー処理）:\n";
echo "<?php\n";
echo "\$file = fopen('nonexistent.txt', 'r');\n";
echo "if (\$file === false) {\n";
echo "    throw new RuntimeException('ファイルを開けませんでした');\n";
echo "}\n\n";

echo "@ 演算子を使っても良い場合:\n";
echo "- エラーが予想され、適切に処理する場合\n";
echo "- error_get_last() でエラー内容を確認する場合\n\n";

echo "例:\n";
echo "<?php\n";
echo "\$file = @fopen('config.txt', 'r');\n";
echo "if (\$file === false) {\n";
echo "    \$error = error_get_last();\n";
echo "    throw new RuntimeException(\$error['message']);\n";
echo "}\n\n";

// ============================================================
// 9. PHP 7+ の Error クラス
// ============================================================

echo "--- 9. PHP 7+ の Error クラス ---\n\n";

/**
 * PHP 7以降では、多くの致命的エラーが Error として投げられます
 */

echo "【Error クラスの階層】\n\n";

echo "Throwable (interface)\n";
echo "├── Error\n";
echo "│   ├── ArithmeticError\n";
echo "│   │   └── DivisionByZeroError\n";
echo "│   ├── AssertionError\n";
echo "│   ├── ParseError\n";
echo "│   ├── TypeError\n";
echo "│   └── ValueError (PHP 8.0+)\n";
echo "└── Exception\n";
echo "    └── ErrorException\n\n";

echo "【Error をキャッチする】\n\n";

echo "<?php\n";
echo "try {\n";
echo "    // 型エラーが発生\n";
echo "    function test(int \$x): void {}\n";
echo "    test('string'); // TypeError\n";
echo "} catch (TypeError \$e) {\n";
echo "    echo \"型エラー: \" . \$e->getMessage();\n";
echo "}\n\n";

echo "【Throwable で Error と Exception の両方をキャッチ】\n\n";

echo "<?php\n";
echo "try {\n";
echo "    // Error または Exception が発生する可能性\n";
echo "} catch (Throwable \$e) {\n";
echo "    // すべてのエラーと例外をキャッチ\n";
echo "    echo \"エラー: \" . \$e->getMessage();\n";
echo "}\n\n";

// ============================================================
// 10. ベストプラクティス
// ============================================================

echo "--- 10. ベストプラクティス ---\n\n";

echo "【エラーハンドリングのベストプラクティス】\n\n";

echo "1. 開発環境ではすべてのエラーを表示\n";
echo "   error_reporting(E_ALL);\n";
echo "   ini_set('display_errors', '1');\n\n";

echo "2. 本番環境ではエラーを表示せず、ログに記録\n";
echo "   ini_set('display_errors', '0');\n";
echo "   ini_set('log_errors', '1');\n\n";

echo "3. エラーを例外に変換して try-catch で処理\n";
echo "   set_error_handler で ErrorException を投げる\n\n";

echo "4. @ 演算子は避け、適切なエラー処理を実装\n";
echo "   if (\$result === false) { throw new Exception(); }\n\n";

echo "5. カスタムエラーハンドラでログを記録\n";
echo "   エラー内容、ファイル名、行番号を記録\n\n";

echo "6. Throwable で Error と Exception の両方をキャッチ\n";
echo "   try { } catch (Throwable \$e) { }\n\n";

echo "7. ユーザーには詳細なエラーメッセージを表示しない\n";
echo "   セキュリティリスクを避けるため\n\n";

// ============================================================
// まとめ
// ============================================================

echo "\n=== まとめ ===\n\n";

echo "エラーハンドリングの重要なポイント:\n";
echo "1. エラーはPHPが検出、例外はプログラマが投げる\n";
echo "2. error_reporting で報告するエラーレベルを設定\n";
echo "3. set_error_handler でカスタムエラーハンドラを登録\n";
echo "4. エラーを ErrorException に変換して try-catch で処理\n";
echo "5. error_log でエラーログを記録\n";
echo "6. PHP 7+ では Error クラスで致命的エラーをキャッチ可能\n";
echo "7. Throwable で Error と Exception の両方をキャッチできる\n";
echo "8. 本番環境ではエラーを表示せず、ログに記録する\n\n";

echo "次のステップ:\n";
echo "- 20_exceptions.php で例外処理を詳しく学習\n";
echo "- exercises/09_error_practice.php で実践的な演習に挑戦\n\n";

echo "=== 学習完了 ===\n";
