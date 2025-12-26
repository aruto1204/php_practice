<?php

declare(strict_types=1);

/**
 * Phase 2.5: ファイル操作 - 実践演習
 *
 * この演習では、ファイル操作を実践的に学習します。
 *
 * 演習内容:
 * 1. CSVデータインポート/エクスポートシステム
 * 2. ログ分析ツール
 * 3. 設定ファイル管理システム
 */

echo "=== Phase 2.5: ファイル操作 - 実践演習 ===\n\n";

// テスト用ディレクトリ
$testDir = '/tmp/php_file_practice';
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

// ============================================================
// 演習1: CSVデータインポート/エクスポートシステム
// ============================================================

echo "--- 演習1: CSVデータインポート/エクスポートシステム ---\n\n";

/**
 * CSV例外クラス
 */
class CsvException extends Exception {}

/**
 * CSVマネージャー
 */
class CsvManager
{
    /**
     * CSVファイルを読み込んで連想配列として返す
     *
     * @param string $filename ファイル名
     * @param string $delimiter 区切り文字
     * @param string $enclosure 囲み文字
     * @return array<array<string, string>> データ
     * @throws CsvException
     */
    public function import(
        string $filename,
        string $delimiter = ',',
        string $enclosure = '"'
    ): array {
        if (!file_exists($filename)) {
            throw new CsvException("ファイルが見つかりません: {$filename}");
        }

        $handle = fopen($filename, 'r');
        if ($handle === false) {
            throw new CsvException("ファイルを開けませんでした: {$filename}");
        }

        $data = [];

        // ヘッダー行を取得
        $headers = fgetcsv($handle, 0, $delimiter, $enclosure);
        if ($headers === false) {
            fclose($handle);
            throw new CsvException("ヘッダー行が見つかりません");
        }

        // データ行を読み込む
        $lineNum = 1;
        while (($row = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
            $lineNum++;

            // 列数チェック
            if (count($row) !== count($headers)) {
                fclose($handle);
                throw new CsvException(
                    "行 {$lineNum}: 列数が一致しません（期待: " . count($headers) . ", 実際: " . count($row) . "）"
                );
            }

            $data[] = array_combine($headers, $row);
        }

        fclose($handle);

        return $data;
    }

    /**
     * 連想配列をCSVファイルにエクスポート
     *
     * @param string $filename ファイル名
     * @param array<array<string, mixed>> $data データ
     * @param array<string>|null $headers ヘッダー（nullの場合は自動検出）
     * @param string $delimiter 区切り文字
     * @param string $enclosure 囲み文字
     * @throws CsvException
     */
    public function export(
        string $filename,
        array $data,
        ?array $headers = null,
        string $delimiter = ',',
        string $enclosure = '"'
    ): void {
        if (empty($data)) {
            throw new CsvException("データが空です");
        }

        $handle = fopen($filename, 'w');
        if ($handle === false) {
            throw new CsvException("ファイルを開けませんでした: {$filename}");
        }

        // ヘッダーを決定
        if ($headers === null) {
            $headers = array_keys($data[0]);
        }

        // ヘッダーを書き込む
        fputcsv($handle, $headers, $delimiter, $enclosure);

        // データを書き込む
        foreach ($data as $row) {
            $values = [];
            foreach ($headers as $header) {
                $values[] = $row[$header] ?? '';
            }
            fputcsv($handle, $values, $delimiter, $enclosure);
        }

        fclose($handle);
    }

    /**
     * CSVデータをフィルタリング
     *
     * @param array<array<string, string>> $data データ
     * @param callable $callback フィルタ関数
     * @return array<array<string, string>> フィルタされたデータ
     */
    public function filter(array $data, callable $callback): array
    {
        return array_filter($data, $callback);
    }

    /**
     * CSVデータをソート
     *
     * @param array<array<string, string>> $data データ
     * @param string $column ソート対象の列
     * @param bool $ascending 昇順の場合true
     * @return array<array<string, string>> ソートされたデータ
     */
    public function sort(array $data, string $column, bool $ascending = true): array
    {
        usort($data, function ($a, $b) use ($column, $ascending) {
            $valueA = $a[$column] ?? '';
            $valueB = $b[$column] ?? '';

            $result = $valueA <=> $valueB;

            return $ascending ? $result : -$result;
        });

        return $data;
    }

    /**
     * CSVデータの統計情報を取得
     *
     * @param array<array<string, string>> $data データ
     * @return array 統計情報
     */
    public function getStats(array $data): array
    {
        if (empty($data)) {
            return ['count' => 0, 'columns' => []];
        }

        $columns = array_keys($data[0]);

        return [
            'count' => count($data),
            'columns' => $columns,
            'column_count' => count($columns),
        ];
    }
}

echo "【CSVマネージャーのテスト】\n\n";

// サンプルデータを作成
$salesCsv = $testDir . '/sales.csv';
$salesData = "date,product,quantity,price\n";
$salesData .= "2024-01-01,りんご,10,120\n";
$salesData .= "2024-01-01,バナナ,15,100\n";
$salesData .= "2024-01-02,りんご,8,120\n";
$salesData .= "2024-01-02,オレンジ,12,150\n";
$salesData .= "2024-01-03,バナナ,20,100\n";
file_put_contents($salesCsv, $salesData);

$csvManager = new CsvManager();

// インポート
$sales = $csvManager->import($salesCsv);
echo "CSVをインポートしました（" . count($sales) . "件）\n";

// 統計情報
$stats = $csvManager->getStats($sales);
echo "列: " . implode(', ', $stats['columns']) . "\n\n";

// フィルタリング（りんごのみ）
$apples = $csvManager->filter($sales, fn($row) => $row['product'] === 'りんご');
echo "りんごの売上: " . count($apples) . "件\n";
foreach ($apples as $sale) {
    echo "  - {$sale['date']}: {$sale['quantity']}個\n";
}
echo "\n";

// ソート（数量の多い順）
$sorted = $csvManager->sort($sales, 'quantity', false);
echo "数量でソート（降順）:\n";
foreach ($sorted as $sale) {
    echo "  - {$sale['product']}: {$sale['quantity']}個\n";
}
echo "\n";

// エクスポート
$exportCsv = $testDir . '/sales_export.csv';
$csvManager->export($exportCsv, $apples);
echo "りんごのデータをエクスポートしました: {$exportCsv}\n\n";

// ============================================================
// 演習2: ログ分析ツール
// ============================================================

echo "\n--- 演習2: ログ分析ツール ---\n\n";

/**
 * ログアナライザー
 */
class LogAnalyzer
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
     * ログを解析
     *
     * @return array 解析結果
     * @throws RuntimeException
     */
    public function analyze(): array
    {
        if (!file_exists($this->logFile)) {
            throw new RuntimeException("ログファイルが見つかりません: {$this->logFile}");
        }

        $handle = fopen($this->logFile, 'r');
        if ($handle === false) {
            throw new RuntimeException("ログファイルを開けませんでした");
        }

        $stats = [
            'total_lines' => 0,
            'levels' => [
                'INFO' => 0,
                'WARNING' => 0,
                'ERROR' => 0,
                'DEBUG' => 0,
            ],
            'errors' => [],
        ];

        while (($line = fgets($handle)) !== false) {
            $stats['total_lines']++;

            // ログレベルを抽出
            if (preg_match('/\[(INFO|WARNING|ERROR|DEBUG)\]/', $line, $matches)) {
                $level = $matches[1];
                $stats['levels'][$level]++;

                // エラーの場合は詳細を記録
                if ($level === 'ERROR') {
                    $stats['errors'][] = trim($line);
                }
            }
        }

        fclose($handle);

        return $stats;
    }

    /**
     * 特定のレベルのログを抽出
     *
     * @param string $level ログレベル
     * @return array<string> ログ行
     */
    public function filterByLevel(string $level): array
    {
        $result = [];
        $handle = fopen($this->logFile, 'r');

        if ($handle === false) {
            return $result;
        }

        while (($line = fgets($handle)) !== false) {
            if (str_contains($line, "[{$level}]")) {
                $result[] = trim($line);
            }
        }

        fclose($handle);

        return $result;
    }

    /**
     * ログをHTML形式でエクスポート
     *
     * @param string $outputFile 出力ファイル
     */
    public function exportToHtml(string $outputFile): void
    {
        $handle = fopen($this->logFile, 'r');
        if ($handle === false) {
            throw new RuntimeException("ログファイルを開けませんでした");
        }

        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<meta charset=\"UTF-8\">\n";
        $html .= "<title>ログ分析結果</title>\n";
        $html .= "<style>\n";
        $html .= ".error { color: red; }\n";
        $html .= ".warning { color: orange; }\n";
        $html .= ".info { color: blue; }\n";
        $html .= ".debug { color: gray; }\n";
        $html .= "</style>\n";
        $html .= "</head>\n<body>\n<h1>ログ分析結果</h1>\n<pre>\n";

        while (($line = fgets($handle)) !== false) {
            $line = htmlspecialchars($line);

            // ログレベルに応じて色付け
            if (str_contains($line, '[ERROR]')) {
                $line = "<span class=\"error\">{$line}</span>";
            } elseif (str_contains($line, '[WARNING]')) {
                $line = "<span class=\"warning\">{$line}</span>";
            } elseif (str_contains($line, '[INFO]')) {
                $line = "<span class=\"info\">{$line}</span>";
            } elseif (str_contains($line, '[DEBUG]')) {
                $line = "<span class=\"debug\">{$line}</span>";
            }

            $html .= $line;
        }

        $html .= "</pre>\n</body>\n</html>";

        fclose($handle);

        file_put_contents($outputFile, $html);
    }
}

echo "【ログアナライザーのテスト】\n\n";

// サンプルログを作成
$logFile = $testDir . '/app.log';
$logContent = <<<LOG
[2024-01-01 10:00:00] [INFO] アプリケーションを起動しました
[2024-01-01 10:05:23] [DEBUG] データベース接続を確立しました
[2024-01-01 10:10:15] [WARNING] メモリ使用量が80%を超えました
[2024-01-01 10:15:42] [ERROR] データベースクエリが失敗しました: connection timeout
[2024-01-01 10:20:11] [INFO] ユーザーがログインしました: user_id=123
[2024-01-01 10:25:33] [ERROR] ファイルが見つかりません: /path/to/file.txt
[2024-01-01 10:30:05] [WARNING] キャッシュの有効期限が切れています
[2024-01-01 10:35:22] [INFO] バッチ処理を開始しました
[2024-01-01 10:40:18] [DEBUG] 処理時間: 1.23秒
[2024-01-01 10:45:55] [INFO] バッチ処理が完了しました
LOG;
file_put_contents($logFile, $logContent);

$analyzer = new LogAnalyzer($logFile);

// ログを解析
$stats = $analyzer->analyze();
echo "ログ分析結果:\n";
echo "総行数: {$stats['total_lines']}\n";
echo "INFO: {$stats['levels']['INFO']}\n";
echo "WARNING: {$stats['levels']['WARNING']}\n";
echo "ERROR: {$stats['levels']['ERROR']}\n";
echo "DEBUG: {$stats['levels']['DEBUG']}\n\n";

// エラーログを抽出
echo "エラーログ:\n";
foreach ($stats['errors'] as $error) {
    echo "  - {$error}\n";
}
echo "\n";

// WARNINGレベルのログを抽出
$warnings = $analyzer->filterByLevel('WARNING');
echo "警告ログ (" . count($warnings) . "件):\n";
foreach ($warnings as $warning) {
    echo "  - {$warning}\n";
}
echo "\n";

// HTMLにエクスポート
$htmlFile = $testDir . '/log_report.html';
$analyzer->exportToHtml($htmlFile);
echo "ログをHTMLにエクスポートしました: {$htmlFile}\n\n";

// ============================================================
// 演習3: 設定ファイル管理システム
// ============================================================

echo "\n--- 演習3: 設定ファイル管理システム ---\n\n";

/**
 * 設定マネージャー
 */
class ConfigManager
{
    private array $config = [];

    /**
     * コンストラクタ
     *
     * @param string $configFile 設定ファイルのパス
     */
    public function __construct(
        private string $configFile,
    ) {
        $this->load();
    }

    /**
     * 設定を読み込む
     *
     * @throws RuntimeException
     */
    private function load(): void
    {
        if (!file_exists($this->configFile)) {
            $this->config = [];
            return;
        }

        $content = file_get_contents($this->configFile);
        if ($content === false) {
            throw new RuntimeException("設定ファイルの読み込みに失敗しました");
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("設定ファイルのパースに失敗しました: " . json_last_error_msg());
        }

        $this->config = $data;
    }

    /**
     * 設定を保存
     *
     * @throws RuntimeException
     */
    public function save(): void
    {
        $json = json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException("設定のエンコードに失敗しました: " . json_last_error_msg());
        }

        // バックアップを作成
        if (file_exists($this->configFile)) {
            $backupFile = $this->configFile . '.backup';
            copy($this->configFile, $backupFile);
        }

        $result = file_put_contents($this->configFile, $json, LOCK_EX);

        if ($result === false) {
            throw new RuntimeException("設定ファイルの保存に失敗しました");
        }
    }

    /**
     * 設定値を取得
     *
     * @param string $key キー（ドット記法対応: "database.host"）
     * @param mixed $default デフォルト値
     * @return mixed 設定値
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * 設定値を設定
     *
     * @param string $key キー（ドット記法対応）
     * @param mixed $value 値
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }

    /**
     * 設定値を削除
     *
     * @param string $key キー
     */
    public function delete(string $key): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                unset($config[$k]);
            } else {
                if (!isset($config[$k])) {
                    return;
                }
                $config = &$config[$k];
            }
        }
    }

    /**
     * すべての設定を取得
     *
     * @return array すべての設定
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * 設定が存在するかチェック
     *
     * @param string $key キー
     * @return bool 存在する場合true
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
}

echo "【設定マネージャーのテスト】\n\n";

$configFile = $testDir . '/config.json';
$config = new ConfigManager($configFile);

// 設定を追加
$config->set('app.name', 'My Application');
$config->set('app.version', '1.0.0');
$config->set('database.host', 'localhost');
$config->set('database.port', 3306);
$config->set('database.name', 'myapp_db');
$config->set('features.cache', true);
$config->set('features.debug', false);

echo "設定を追加しました\n";

// 設定を保存
$config->save();
echo "設定を保存しました: {$configFile}\n\n";

// 設定を取得
echo "設定の取得:\n";
echo "app.name: " . $config->get('app.name') . "\n";
echo "database.host: " . $config->get('database.host') . "\n";
echo "database.port: " . $config->get('database.port') . "\n";
echo "features.cache: " . ($config->get('features.cache') ? 'true' : 'false') . "\n";
echo "存在しないキー: " . ($config->get('nonexistent.key', 'デフォルト値') ?? 'null') . "\n\n";

// 設定ファイルの内容を表示
echo "設定ファイルの内容:\n";
echo file_get_contents($configFile) . "\n";

// ============================================================
// クリーンアップ
// ============================================================

echo "--- クリーンアップ ---\n\n";

/**
 * ディレクトリを再帰的に削除
 *
 * @param string $dir ディレクトリパス
 * @return bool 成功した場合true
 */
function removeDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

removeDirectory($testDir);
echo "テストディレクトリを削除しました\n";

// ============================================================
// まとめ
// ============================================================

echo "\n=== 演習のまとめ ===\n\n";

echo "学習した内容:\n";
echo "1. CSVデータのインポート/エクスポート、フィルタリング、ソート\n";
echo "2. ログファイルの解析とHTML形式でのエクスポート\n";
echo "3. JSON設定ファイルの管理（ドット記法でのアクセス、バックアップ）\n";
echo "4. エラーハンドリングとファイルロックの活用\n";
echo "5. 実用的なファイル操作パターンの実装\n\n";

echo "ベストプラクティス:\n";
echo "- ファイル操作には常にエラーチェックを実装\n";
echo "- ファイルハンドルは必ずクローズする\n";
echo "- 重要なファイルは保存前にバックアップを作成\n";
echo "- ドット記法で階層的な設定にアクセス\n";
echo "- ログは構造化して解析しやすくする\n\n";

echo "=== すべての演習が完了しました ===\n";
