<?php

declare(strict_types=1);

/**
 * Phase 2.5: ファイル操作 - CSV/JSON処理
 *
 * このファイルでは、CSV と JSON ファイルの処理について学習します。
 * データのインポート/エクスポート、ファイルアップロードなども理解します。
 *
 * 学習内容:
 * 1. CSV ファイルの読み込み
 * 2. CSV ファイルの書き込み
 * 3. JSON ファイルの読み込み
 * 4. JSON ファイルの書き込み
 * 5. ファイルアップロードの処理
 * 6. 実践例
 */

echo "=== Phase 2.5: ファイル操作 - CSV/JSON処理 ===\n\n";

// テスト用ディレクトリ
$testDir = '/tmp/php_advanced_file_test';
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

// ============================================================
// 1. CSV ファイルの読み込み
// ============================================================

echo "--- 1. CSV ファイルの読み込み ---\n\n";

// テスト用CSVファイルを作成
$csvFile = $testDir . '/users.csv';
$csvData = <<<CSV
id,name,email,age
1,山田太郎,yamada@example.com,25
2,佐藤花子,sato@example.com,30
3,鈴木一郎,suzuki@example.com,28
4,田中美咲,tanaka@example.com,22
CSV;

file_put_contents($csvFile, $csvData);

echo "【方法1: fgetcsv() - 1行ずつ読み込む】\n\n";

$handle = fopen($csvFile, 'r');
if ($handle !== false) {
    $rowNum = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if ($rowNum === 0) {
            echo "ヘッダー: " . implode(', ', $row) . "\n";
        } else {
            echo "行 {$rowNum}: " . implode(', ', $row) . "\n";
        }
        $rowNum++;
    }
    fclose($handle);
}
echo "\n";

echo "【方法2: 連想配列として読み込む】\n\n";

/**
 * CSVファイルを連想配列として読み込む
 *
 * @param string $filename ファイル名
 * @return array<array<string, string>> データ
 */
function readCsvAsAssoc(string $filename): array
{
    $data = [];
    $handle = fopen($filename, 'r');

    if ($handle === false) {
        return $data;
    }

    // ヘッダー行を取得
    $headers = fgetcsv($handle);
    if ($headers === false) {
        fclose($handle);
        return $data;
    }

    // データ行を読み込む
    while (($row = fgetcsv($handle)) !== false) {
        $data[] = array_combine($headers, $row);
    }

    fclose($handle);
    return $data;
}

$users = readCsvAsAssoc($csvFile);
echo "ユーザー数: " . count($users) . "\n";
foreach ($users as $user) {
    echo "- {$user['name']} ({$user['email']}) - {$user['age']}歳\n";
}
echo "\n";

// ============================================================
// 2. CSV ファイルの書き込み
// ============================================================

echo "--- 2. CSV ファイルの書き込み ---\n\n";

echo "【fputcsv() を使った書き込み】\n\n";

$outputCsv = $testDir . '/output.csv';
$handle = fopen($outputCsv, 'w');

if ($handle !== false) {
    // ヘッダー行
    fputcsv($handle, ['ID', '商品名', '価格', '在庫']);

    // データ行
    fputcsv($handle, [1, 'ノートPC', 89800, 5]);
    fputcsv($handle, [2, 'マウス', 2980, 20]);
    fputcsv($handle, [3, 'キーボード', 5980, 10]);

    fclose($handle);
    echo "CSVファイルを作成しました: {$outputCsv}\n\n";
}

// 作成したCSVの内容を表示
echo "作成したCSVの内容:\n";
echo file_get_contents($outputCsv) . "\n";

echo "【配列からCSVファイルを作成】\n\n";

/**
 * 配列からCSVファイルを作成
 *
 * @param string $filename ファイル名
 * @param array<array<string, mixed>> $data データ
 * @param array<string>|null $headers ヘッダー（nullの場合は最初の要素のキーを使用）
 */
function writeCsvFromArray(string $filename, array $data, ?array $headers = null): void
{
    $handle = fopen($filename, 'w');

    if ($handle === false) {
        throw new RuntimeException("ファイルを開けませんでした: {$filename}");
    }

    // ヘッダーが指定されていない場合、最初の要素のキーを使用
    if ($headers === null && !empty($data)) {
        $headers = array_keys($data[0]);
    }

    // ヘッダーを書き込む
    if ($headers !== null) {
        fputcsv($handle, $headers);
    }

    // データを書き込む
    foreach ($data as $row) {
        fputcsv($handle, $row);
    }

    fclose($handle);
}

$products = [
    ['id' => 1, 'name' => 'りんご', 'price' => 120, 'category' => '果物'],
    ['id' => 2, 'name' => 'バナナ', 'price' => 100, 'category' => '果物'],
    ['id' => 3, 'name' => 'キャベツ', 'price' => 180, 'category' => '野菜'],
];

$productsCsv = $testDir . '/products.csv';
writeCsvFromArray($productsCsv, $products);
echo "商品CSVを作成しました\n";
echo file_get_contents($productsCsv) . "\n";

// ============================================================
// 3. JSON ファイルの読み込み
// ============================================================

echo "--- 3. JSON ファイルの読み込み ---\n\n";

// テスト用JSONファイルを作成
$jsonFile = $testDir . '/data.json';
$jsonData = [
    'users' => [
        ['id' => 1, 'name' => '太郎', 'email' => 'taro@example.com'],
        ['id' => 2, 'name' => '花子', 'email' => 'hanako@example.com'],
    ],
    'settings' => [
        'timezone' => 'Asia/Tokyo',
        'language' => 'ja',
    ],
];

file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "【JSONファイルの読み込み】\n\n";

$content = file_get_contents($jsonFile);
$data = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSONのパースエラー: " . json_last_error_msg() . "\n";
} else {
    echo "ユーザー数: " . count($data['users']) . "\n";
    foreach ($data['users'] as $user) {
        echo "- {$user['name']} ({$user['email']})\n";
    }
    echo "\n設定:\n";
    echo "- タイムゾーン: {$data['settings']['timezone']}\n";
    echo "- 言語: {$data['settings']['language']}\n";
}
echo "\n";

echo "【安全なJSON読み込み関数】\n\n";

/**
 * JSONファイルを安全に読み込む
 *
 * @param string $filename ファイル名
 * @return array データ
 * @throws RuntimeException ファイルの読み込みまたはパースに失敗した場合
 */
function readJsonFile(string $filename): array
{
    if (!file_exists($filename)) {
        throw new RuntimeException("ファイルが見つかりません: {$filename}");
    }

    $content = file_get_contents($filename);
    if ($content === false) {
        throw new RuntimeException("ファイルの読み込みに失敗しました: {$filename}");
    }

    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("JSONのパースエラー: " . json_last_error_msg());
    }

    return $data;
}

$data = readJsonFile($jsonFile);
echo "JSONを読み込みました（ユーザー数: " . count($data['users']) . "）\n\n";

// ============================================================
// 4. JSON ファイルの書き込み
// ============================================================

echo "--- 4. JSON ファイルの書き込み ---\n\n";

echo "【JSONファイルの書き込み】\n\n";

/**
 * JSONファイルに書き込む
 *
 * @param string $filename ファイル名
 * @param mixed $data データ
 * @param int $flags json_encode のフラグ
 * @throws RuntimeException 書き込みに失敗した場合
 */
function writeJsonFile(string $filename, mixed $data, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): void
{
    $json = json_encode($data, $flags);

    if ($json === false) {
        throw new RuntimeException("JSONのエンコードエラー: " . json_last_error_msg());
    }

    $result = file_put_contents($filename, $json);

    if ($result === false) {
        throw new RuntimeException("ファイルの書き込みに失敗しました: {$filename}");
    }
}

$config = [
    'app_name' => 'My Application',
    'version' => '1.0.0',
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'myapp_db',
    ],
    'features' => [
        'cache' => true,
        'debug' => false,
    ],
];

$configFile = $testDir . '/config.json';
writeJsonFile($configFile, $config);
echo "設定ファイルを作成しました: {$configFile}\n";
echo file_get_contents($configFile) . "\n";

echo "【JSONエンコードのオプション】\n\n";

$data = ['name' => '太郎', 'items' => ['りんご', 'バナナ']];

echo "デフォルト:\n";
echo json_encode($data) . "\n\n";

echo "JSON_PRETTY_PRINT（整形）:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

echo "JSON_UNESCAPED_UNICODE（日本語をそのまま）:\n";
echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";

echo "JSON_UNESCAPED_SLASHES（スラッシュをエスケープしない）:\n";
echo json_encode(['url' => 'https://example.com/path'], JSON_UNESCAPED_SLASHES) . "\n\n";

// ============================================================
// 5. ファイルアップロードの処理
// ============================================================

echo "--- 5. ファイルアップロードの処理 ---\n\n";

/**
 * ファイルアップロードの処理
 *
 * 実際にはHTTPリクエストからファイルを受け取りますが、
 * ここでは処理の流れを示します。
 */

echo "【ファイルアップロードの流れ】\n\n";

echo "1. HTMLフォーム:\n";
echo "<form method=\"POST\" enctype=\"multipart/form-data\">\n";
echo "  <input type=\"file\" name=\"uploaded_file\">\n";
echo "  <button type=\"submit\">アップロード</button>\n";
echo "</form>\n\n";

echo "2. PHPでの処理:\n";
echo "<?php\n";
echo "if (isset(\$_FILES['uploaded_file'])) {\n";
echo "    \$file = \$_FILES['uploaded_file'];\n";
echo "    \n";
echo "    // エラーチェック\n";
echo "    if (\$file['error'] !== UPLOAD_ERR_OK) {\n";
echo "        die('アップロードエラー');\n";
echo "    }\n";
echo "    \n";
echo "    // ファイルサイズチェック（5MB以下）\n";
echo "    if (\$file['size'] > 5 * 1024 * 1024) {\n";
echo "        die('ファイルサイズが大きすぎます');\n";
echo "    }\n";
echo "    \n";
echo "    // 拡張子チェック\n";
echo "    \$allowed = ['jpg', 'jpeg', 'png', 'gif'];\n";
echo "    \$ext = strtolower(pathinfo(\$file['name'], PATHINFO_EXTENSION));\n";
echo "    if (!in_array(\$ext, \$allowed)) {\n";
echo "        die('許可されていないファイル形式です');\n";
echo "    }\n";
echo "    \n";
echo "    // ファイルを保存\n";
echo "    \$uploadDir = 'uploads/';\n";
echo "    \$filename = uniqid() . '.' . \$ext;\n";
echo "    move_uploaded_file(\$file['tmp_name'], \$uploadDir . \$filename);\n";
echo "}\n\n";

/**
 * ファイルアップロードハンドラー
 */
class FileUploadHandler
{
    /**
     * コンストラクタ
     *
     * @param string $uploadDir アップロード先ディレクトリ
     * @param array<string> $allowedExtensions 許可する拡張子
     * @param int $maxSize 最大ファイルサイズ（バイト）
     */
    public function __construct(
        private string $uploadDir,
        private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'],
        private int $maxSize = 5242880, // 5MB
    ) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }

    /**
     * ファイルをアップロード
     *
     * @param array $file $_FILES の要素
     * @return string 保存されたファイルのパス
     * @throws RuntimeException アップロードに失敗した場合
     */
    public function upload(array $file): string
    {
        // エラーチェック
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->getUploadErrorMessage($file['error']));
        }

        // ファイルサイズチェック
        if ($file['size'] > $this->maxSize) {
            throw new RuntimeException(
                "ファイルサイズが大きすぎます（最大: " . $this->formatBytes($this->maxSize) . "）"
            );
        }

        // 拡張子チェック
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions)) {
            throw new RuntimeException(
                "許可されていないファイル形式です（許可: " . implode(', ', $this->allowedExtensions) . "）"
            );
        }

        // ファイル名を生成（ユニーク）
        $filename = uniqid('upload_', true) . '.' . $ext;
        $filepath = $this->uploadDir . '/' . $filename;

        // ファイルを移動
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new RuntimeException("ファイルの保存に失敗しました");
        }

        return $filepath;
    }

    /**
     * アップロードエラーメッセージを取得
     *
     * @param int $errorCode エラーコード
     * @return string エラーメッセージ
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'ファイルサイズがphp.iniの制限を超えています',
            UPLOAD_ERR_FORM_SIZE => 'ファイルサイズがフォームの制限を超えています',
            UPLOAD_ERR_PARTIAL => 'ファイルが部分的にしかアップロードされませんでした',
            UPLOAD_ERR_NO_FILE => 'ファイルがアップロードされませんでした',
            UPLOAD_ERR_NO_TMP_DIR => '一時ディレクトリが見つかりません',
            UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました',
            UPLOAD_ERR_EXTENSION => 'PHPの拡張機能によってアップロードが中断されました',
            default => '不明なエラー',
        };
    }

    /**
     * バイト数を人間が読める形式に変換
     *
     * @param int $bytes バイト数
     * @return string フォーマットされた文字列
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}

echo "【FileUploadHandlerの使用例】\n\n";

echo "<?php\n";
echo "\$handler = new FileUploadHandler(\n";
echo "    uploadDir: 'uploads/',\n";
echo "    allowedExtensions: ['jpg', 'png', 'pdf'],\n";
echo "    maxSize: 10 * 1024 * 1024 // 10MB\n";
echo ");\n";
echo "\n";
echo "try {\n";
echo "    \$filepath = \$handler->upload(\$_FILES['file']);\n";
echo "    echo \"ファイルをアップロードしました: {\$filepath}\";\n";
echo "} catch (RuntimeException \$e) {\n";
echo "    echo \"エラー: \" . \$e->getMessage();\n";
echo "}\n\n";

// ============================================================
// 6. 実践例: ログファイル管理
// ============================================================

echo "--- 6. 実践例: ログファイル管理 ---\n\n";

/**
 * ログマネージャー
 */
class LogManager
{
    /**
     * コンストラクタ
     *
     * @param string $logDir ログディレクトリ
     * @param int $maxFileSize 最大ファイルサイズ（バイト）
     */
    public function __construct(
        private string $logDir,
        private int $maxFileSize = 1048576, // 1MB
    ) {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * ログを書き込む
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     */
    public function log(string $level, string $message): void
    {
        $logFile = $this->getLogFile();

        // ファイルサイズをチェック
        if (file_exists($logFile) && filesize($logFile) > $this->maxFileSize) {
            $this->rotateLog($logFile);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] [{$level}] {$message}\n";

        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * ログファイルのパスを取得
     *
     * @return string ログファイルのパス
     */
    private function getLogFile(): string
    {
        $date = date('Y-m-d');
        return $this->logDir . "/app-{$date}.log";
    }

    /**
     * ログファイルをローテーション
     *
     * @param string $logFile ログファイルのパス
     */
    private function rotateLog(string $logFile): void
    {
        $timestamp = date('YmdHis');
        $rotatedFile = $logFile . '.' . $timestamp;
        rename($logFile, $rotatedFile);
    }

    /**
     * ログを読み込む
     *
     * @param string|null $date 日付（null の場合は今日）
     * @param int $limit 取得する行数（0 = すべて）
     * @return array<string> ログ行
     */
    public function read(?string $date = null, int $limit = 0): array
    {
        $date = $date ?? date('Y-m-d');
        $logFile = $this->logDir . "/app-{$date}.log";

        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES);

        if ($limit > 0 && count($lines) > $limit) {
            return array_slice($lines, -$limit);
        }

        return $lines;
    }
}

echo "【ログマネージャーのテスト】\n\n";

$logManager = new LogManager($testDir . '/logs');

$logManager->log('INFO', 'アプリケーションを起動しました');
$logManager->log('WARNING', 'メモリ使用量が80%を超えました');
$logManager->log('ERROR', 'データベース接続に失敗しました');

echo "ログを書き込みました\n\n";

echo "ログの内容:\n";
$logs = $logManager->read();
foreach ($logs as $log) {
    echo $log . "\n";
}

echo "\n";

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

echo "\n=== まとめ ===\n\n";

echo "CSV/JSON処理の重要なポイント:\n";
echo "1. CSV: fgetcsv() で読み込み、fputcsv() で書き込み\n";
echo "2. ヘッダー行を使って連想配列に変換すると扱いやすい\n";
echo "3. JSON: json_decode() で読み込み、json_encode() で書き込み\n";
echo "4. JSON エラーは json_last_error() でチェック\n";
echo "5. ファイルアップロードは厳格なバリデーションが必須\n";
echo "6. ログファイルはローテーションして肥大化を防ぐ\n";
echo "7. ファイル操作には常にエラーハンドリングを実装\n\n";

echo "次のステップ:\n";
echo "- exercises/10_file_practice.php で実践的な演習に挑戦\n\n";

echo "=== 学習完了 ===\n";
