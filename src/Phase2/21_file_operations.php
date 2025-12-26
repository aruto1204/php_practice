<?php

declare(strict_types=1);

/**
 * Phase 2.5: ファイル操作の基礎
 *
 * このファイルでは、PHPのファイル操作について学習します。
 * ファイルの読み書き、ディレクトリ操作、ファイル情報の取得などを理解します。
 *
 * 学習内容:
 * 1. ファイルの読み込み
 * 2. ファイルの書き込み
 * 3. ファイルの存在確認と情報取得
 * 4. ディレクトリ操作
 * 5. ファイルのコピー・移動・削除
 * 6. ファイルロック
 */

echo "=== Phase 2.5: ファイル操作の基礎 ===\n\n";

// テスト用ディレクトリを作成
$testDir = '/tmp/php_file_test';
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

// ============================================================
// 1. ファイルの読み込み
// ============================================================

echo "--- 1. ファイルの読み込み ---\n\n";

/**
 * ファイルを読み込む方法はいくつかあります
 */

// テスト用ファイルを作成
$testFile = $testDir . '/test.txt';
file_put_contents($testFile, "Hello, World!\nThis is a test file.\nLine 3\n");

echo "【方法1: file_get_contents() - ファイル全体を文字列として読み込む】\n\n";

$content = file_get_contents($testFile);
echo "ファイルの内容:\n{$content}\n";

echo "特徴:\n";
echo "- ファイル全体を一度に読み込む\n";
echo "- シンプルで使いやすい\n";
echo "- 小〜中サイズのファイルに適している\n";
echo "- 大きなファイルはメモリを大量に消費\n\n";

echo "【方法2: file() - ファイルを配列として読み込む】\n\n";

$lines = file($testFile);
echo "行数: " . count($lines) . "\n";
foreach ($lines as $lineNum => $line) {
    echo "行 " . ($lineNum + 1) . ": {$line}";
}
echo "\n";

echo "特徴:\n";
echo "- 各行を配列の要素として読み込む\n";
echo "- 行単位で処理する場合に便利\n\n";

echo "【方法3: fopen() + fread() - ファイルハンドルを使った読み込み】\n\n";

$handle = fopen($testFile, 'r');
if ($handle === false) {
    echo "ファイルを開けませんでした\n";
} else {
    // 1024バイトずつ読み込む
    $content = fread($handle, 1024);
    echo "読み込んだ内容（最初の1024バイト）:\n{$content}\n";
    fclose($handle);
}

echo "特徴:\n";
echo "- ファイルハンドルを使った低レベルな操作\n";
echo "- 読み込むサイズを制御できる\n";
echo "- 大きなファイルを分割して読み込める\n\n";

echo "【方法4: fopen() + fgets() - 1行ずつ読み込む】\n\n";

$handle = fopen($testFile, 'r');
if ($handle !== false) {
    $lineNum = 1;
    while (($line = fgets($handle)) !== false) {
        echo "行 {$lineNum}: {$line}";
        $lineNum++;
    }
    fclose($handle);
}
echo "\n";

echo "特徴:\n";
echo "- 1行ずつメモリ効率よく読み込める\n";
echo "- 大きなファイルの処理に適している\n\n";

// ============================================================
// 2. ファイルの書き込み
// ============================================================

echo "--- 2. ファイルの書き込み ---\n\n";

echo "【方法1: file_put_contents() - 文字列をファイルに書き込む】\n\n";

$writeFile = $testDir . '/write_test.txt';
$data = "これは書き込みテストです\n";
$data .= "2行目のデータ\n";

// ファイルに書き込む（上書き）
$bytesWritten = file_put_contents($writeFile, $data);
echo "書き込んだバイト数: {$bytesWritten}\n";

// ファイルに追記
$additionalData = "3行目を追記\n";
file_put_contents($writeFile, $additionalData, FILE_APPEND);
echo "追記しました\n\n";

// 内容を確認
echo "ファイルの内容:\n";
echo file_get_contents($writeFile) . "\n";

echo "【方法2: fopen() + fwrite() - ファイルハンドルを使った書き込み】\n\n";

$writeFile2 = $testDir . '/write_test2.txt';
$handle = fopen($writeFile2, 'w'); // 'w' は書き込みモード（上書き）

if ($handle !== false) {
    fwrite($handle, "1行目\n");
    fwrite($handle, "2行目\n");
    fwrite($handle, "3行目\n");
    fclose($handle);
    echo "ファイルに書き込みました\n\n";
}

echo "【ファイルオープンモード】\n\n";

echo "r  : 読み込み専用（ファイルが存在する必要あり）\n";
echo "r+ : 読み書き可能（ファイルが存在する必要あり）\n";
echo "w  : 書き込み専用（ファイルを作成または上書き）\n";
echo "w+ : 読み書き可能（ファイルを作成または上書き）\n";
echo "a  : 追記専用（ファイルを作成または末尾に追記）\n";
echo "a+ : 読み書き可能（ファイルを作成または末尾に追記）\n";
echo "x  : 新規作成専用（ファイルが既に存在する場合は失敗）\n";
echo "x+ : 読み書き可能（新規作成、既存ファイルがある場合は失敗）\n\n";

// ============================================================
// 3. ファイルの存在確認と情報取得
// ============================================================

echo "--- 3. ファイルの存在確認と情報取得 ---\n\n";

echo "【ファイルの存在確認】\n\n";

echo "file_exists('{$testFile}'): " . (file_exists($testFile) ? 'true' : 'false') . "\n";
echo "is_file('{$testFile}'): " . (is_file($testFile) ? 'true' : 'false') . "\n";
echo "is_dir('{$testDir}'): " . (is_dir($testDir) ? 'true' : 'false') . "\n";
echo "is_readable('{$testFile}'): " . (is_readable($testFile) ? 'true' : 'false') . "\n";
echo "is_writable('{$testFile}'): " . (is_writable($testFile) ? 'true' : 'false') . "\n\n";

echo "【ファイル情報の取得】\n\n";

echo "filesize('{$testFile}'): " . filesize($testFile) . " bytes\n";
echo "filetype('{$testFile}'): " . filetype($testFile) . "\n";
echo "filemtime('{$testFile}'): " . date('Y-m-d H:i:s', filemtime($testFile)) . "\n";
echo "fileatime('{$testFile}'): " . date('Y-m-d H:i:s', fileatime($testFile)) . "\n";
echo "filectime('{$testFile}'): " . date('Y-m-d H:i:s', filectime($testFile)) . "\n";
echo "fileperms('{$testFile}'): " . decoct(fileperms($testFile)) . "\n\n";

echo "【pathinfo() - パス情報の取得】\n\n";

$pathInfo = pathinfo($testFile);
echo "dirname: {$pathInfo['dirname']}\n";
echo "basename: {$pathInfo['basename']}\n";
echo "filename: {$pathInfo['filename']}\n";
echo "extension: " . ($pathInfo['extension'] ?? 'なし') . "\n\n";

echo "【realpath() - 絶対パスの取得】\n\n";

$relativePath = 'test.txt';
echo "相対パス: {$relativePath}\n";
echo "絶対パス: " . realpath($testFile) . "\n\n";

// ============================================================
// 4. ディレクトリ操作
// ============================================================

echo "--- 4. ディレクトリ操作 ---\n\n";

echo "【ディレクトリの作成】\n\n";

$newDir = $testDir . '/subdir';
if (!is_dir($newDir)) {
    mkdir($newDir, 0755, true); // 再帰的にディレクトリを作成
    echo "ディレクトリを作成しました: {$newDir}\n";
}

// サブディレクトリにファイルを作成
file_put_contents($newDir . '/file1.txt', "File 1\n");
file_put_contents($newDir . '/file2.txt', "File 2\n");
file_put_contents($newDir . '/file3.log', "Log file\n");
echo "テストファイルを作成しました\n\n";

echo "【ディレクトリの内容を取得】\n\n";

echo "方法1: scandir()\n";
$files = scandir($newDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "  - {$file}\n";
    }
}
echo "\n";

echo "方法2: glob() - パターンマッチング\n";
$txtFiles = glob($newDir . '/*.txt');
echo "  .txt ファイル:\n";
foreach ($txtFiles as $file) {
    echo "  - " . basename($file) . "\n";
}
echo "\n";

echo "【ディレクトリの再帰的な走査】\n\n";

/**
 * ディレクトリを再帰的に走査する
 *
 * @param string $dir ディレクトリパス
 * @param int $depth 現在の深さ
 */
function listDirectory(string $dir, int $depth = 0): void
{
    $files = scandir($dir);
    $indent = str_repeat('  ', $depth);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . '/' . $file;
        echo $indent . '- ' . $file;

        if (is_dir($path)) {
            echo " (dir)\n";
            listDirectory($path, $depth + 1);
        } else {
            echo " (" . filesize($path) . " bytes)\n";
        }
    }
}

echo "ディレクトリ構造:\n";
listDirectory($testDir);
echo "\n";

echo "【RecursiveDirectoryIterator - より高度な走査】\n\n";

$iterator = new RecursiveDirectoryIterator($testDir);
$iterator = new RecursiveIteratorIterator($iterator);

echo "すべてのファイル:\n";
foreach ($iterator as $file) {
    if ($file->isFile()) {
        echo "  - " . $file->getPathname() . "\n";
    }
}
echo "\n";

// ============================================================
// 5. ファイルのコピー・移動・削除
// ============================================================

echo "--- 5. ファイルのコピー・移動・削除 ---\n\n";

echo "【ファイルのコピー】\n\n";

$sourceFile = $testDir . '/source.txt';
$copyFile = $testDir . '/copy.txt';

file_put_contents($sourceFile, "This is the source file\n");

if (copy($sourceFile, $copyFile)) {
    echo "ファイルをコピーしました: {$sourceFile} → {$copyFile}\n";
}
echo "\n";

echo "【ファイルの移動（リネーム）】\n\n";

$oldName = $testDir . '/old_name.txt';
$newName = $testDir . '/new_name.txt';

file_put_contents($oldName, "File to be renamed\n");

if (rename($oldName, $newName)) {
    echo "ファイルを移動しました: {$oldName} → {$newName}\n";
}
echo "\n";

echo "【ファイルの削除】\n\n";

$deleteFile = $testDir . '/delete_me.txt';
file_put_contents($deleteFile, "This file will be deleted\n");

if (unlink($deleteFile)) {
    echo "ファイルを削除しました: {$deleteFile}\n";
}
echo "\n";

echo "【ディレクトリの削除】\n\n";

// ディレクトリを削除する前に、中身を空にする必要があります
/**
 * ディレクトリを再帰的に削除する
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

$deleteDir = $testDir . '/to_delete';
mkdir($deleteDir, 0755);
file_put_contents($deleteDir . '/file.txt', "Test\n");

if (removeDirectory($deleteDir)) {
    echo "ディレクトリを削除しました: {$deleteDir}\n";
}
echo "\n";

// ============================================================
// 6. ファイルロック
// ============================================================

echo "--- 6. ファイルロック ---\n\n";

/**
 * ファイルロックは、複数のプロセスが同時に同じファイルにアクセスするのを防ぎます
 */

echo "【排他ロックの使用】\n\n";

$lockFile = $testDir . '/counter.txt';

// カウンターファイルがない場合は作成
if (!file_exists($lockFile)) {
    file_put_contents($lockFile, "0");
}

/**
 * カウンターをインクリメントする（ファイルロック使用）
 *
 * @param string $filename ファイル名
 * @return int 新しいカウンター値
 */
function incrementCounter(string $filename): int
{
    $handle = fopen($filename, 'r+');
    if ($handle === false) {
        throw new RuntimeException("ファイルを開けませんでした");
    }

    // 排他ロックを取得
    if (flock($handle, LOCK_EX)) {
        // ファイルの内容を読み込む
        $counter = (int)fread($handle, 100);

        // カウンターをインクリメント
        $counter++;

        // ファイルの先頭に戻る
        rewind($handle);

        // 新しい値を書き込む
        fwrite($handle, (string)$counter);

        // ファイルを切り詰める（余分なデータを削除）
        ftruncate($handle, ftell($handle));

        // ロックを解放
        flock($handle, LOCK_UN);

        fclose($handle);

        return $counter;
    } else {
        fclose($handle);
        throw new RuntimeException("ロックを取得できませんでした");
    }
}

// カウンターをインクリメント
$newValue = incrementCounter($lockFile);
echo "カウンター値: {$newValue}\n\n";

echo "ロックの種類:\n";
echo "LOCK_SH: 共有ロック（読み込み用）\n";
echo "LOCK_EX: 排他ロック（書き込み用）\n";
echo "LOCK_UN: ロック解除\n";
echo "LOCK_NB: ノンブロッキング（LOCK_SH や LOCK_EX と組み合わせる）\n\n";

// ============================================================
// 7. ストリームコンテキスト
// ============================================================

echo "--- 7. ストリームコンテキスト ---\n\n";

/**
 * ストリームコンテキストを使うと、ファイル操作のオプションを設定できます
 */

echo "【HTTP経由でファイルを読み込む例（概念のみ）】\n\n";

echo "<?php\n";
echo "\$context = stream_context_create([\n";
echo "    'http' => [\n";
echo "        'method' => 'GET',\n";
echo "        'header' => 'User-Agent: PHP Script',\n";
echo "        'timeout' => 5,\n";
echo "    ],\n";
echo "]);\n";
echo "\n";
echo "\$content = file_get_contents('https://example.com/data.json', false, \$context);\n\n";

// ============================================================
// 8. 一時ファイル
// ============================================================

echo "--- 8. 一時ファイル ---\n\n";

echo "【tmpfile() - 一時ファイルの作成】\n\n";

$tmpFile = tmpfile();
if ($tmpFile !== false) {
    fwrite($tmpFile, "This is a temporary file\n");
    rewind($tmpFile);
    $content = fread($tmpFile, 1024);
    echo "一時ファイルの内容: {$content}";
    fclose($tmpFile); // クローズすると自動的に削除される
}
echo "\n";

echo "【tempnam() - 名前付き一時ファイルの作成】\n\n";

$tmpFileName = tempnam(sys_get_temp_dir(), 'php_');
echo "一時ファイル名: {$tmpFileName}\n";
file_put_contents($tmpFileName, "Temporary data\n");
echo "内容を書き込みました\n";
unlink($tmpFileName); // 手動で削除する必要がある
echo "一時ファイルを削除しました\n\n";

// ============================================================
// 9. ベストプラクティス
// ============================================================

echo "--- 9. ベストプラクティス ---\n\n";

echo "【ファイル操作のベストプラクティス】\n\n";

echo "1. 常にエラーチェックを行う\n";
echo "   ✅ if (\$handle = fopen(\$file, 'r')) { ... }\n";
echo "   ❌ \$handle = fopen(\$file, 'r');\n\n";

echo "2. ファイルハンドルは必ずクローズする\n";
echo "   try-finally を使うか、fclose() を確実に呼ぶ\n\n";

echo "3. 大きなファイルは分割して処理\n";
echo "   file_get_contents() で全体を読むのではなく、\n";
echo "   fgets() や fread() で少しずつ読む\n\n";

echo "4. ファイルロックを適切に使用\n";
echo "   複数プロセスからのアクセスがある場合は flock() を使う\n\n";

echo "5. パスはrealpath()で正規化\n";
echo "   相対パスを絶対パスに変換して安全性を高める\n\n";

echo "6. ファイルアップロードは厳格に検証\n";
echo "   - ファイルサイズの制限\n";
echo "   - 拡張子のホワイトリスト\n";
echo "   - MIMEタイプの検証\n\n";

echo "7. 一時ファイルは確実に削除\n";
echo "   tmpfile() は自動削除、tempnam() は手動削除が必要\n\n";

// ============================================================
// 10. セキュリティ上の注意点
// ============================================================

echo "--- 10. セキュリティ上の注意点 ---\n\n";

echo "【ファイル操作のセキュリティ】\n\n";

echo "❌ 危険: ユーザー入力をそのままファイルパスに使う\n";
echo "<?php\n";
echo "\$file = \$_GET['file'];\n";
echo "echo file_get_contents(\$file); // ディレクトリトラバーサル攻撃の危険\n\n";

echo "✅ 安全: ファイル名を検証し、ベースディレクトリを固定\n";
echo "<?php\n";
echo "\$allowedFiles = ['file1.txt', 'file2.txt'];\n";
echo "\$file = \$_GET['file'];\n";
echo "if (in_array(\$file, \$allowedFiles)) {\n";
echo "    \$content = file_get_contents('/safe/path/' . \$file);\n";
echo "}\n\n";

echo "注意点:\n";
echo "1. ディレクトリトラバーサル攻撃（../ を使った不正アクセス）\n";
echo "2. ファイル名にNULLバイトを含めた攻撃\n";
echo "3. シンボリックリンクを使った攻撃\n";
echo "4. ファイルの上書きによるコード実行\n\n";

// ============================================================
// クリーンアップ
// ============================================================

echo "--- クリーンアップ ---\n\n";

// テストディレクトリを削除
if (removeDirectory($testDir)) {
    echo "テストディレクトリを削除しました\n";
}

// ============================================================
// まとめ
// ============================================================

echo "\n=== まとめ ===\n\n";

echo "ファイル操作の重要なポイント:\n";
echo "1. file_get_contents() は小さなファイル、fopen() + fgets() は大きなファイル\n";
echo "2. ファイルモード（r, w, a など）を適切に選択\n";
echo "3. file_exists(), is_file(), is_dir() で存在確認\n";
echo "4. scandir(), glob() でディレクトリ内容を取得\n";
echo "5. copy(), rename(), unlink() でファイル操作\n";
echo "6. flock() で同時アクセスを制御\n";
echo "7. エラーチェックとリソース解放を必ず行う\n";
echo "8. ユーザー入力をファイルパスに使う場合は厳格に検証\n\n";

echo "次のステップ:\n";
echo "- 22_file_advanced.php で CSV/JSON 処理を学習\n";
echo "- exercises/10_file_practice.php で実践的な演習に挑戦\n\n";

echo "=== 学習完了 ===\n";
