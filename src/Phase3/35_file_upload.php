<?php

declare(strict_types=1);

/**
 * Phase 3.4: フォーム処理とバリデーション - ファイルアップロード
 *
 * このファイルでは、セキュアなファイルアップロード処理を学習します。
 *
 * 学習内容:
 * - $_FILESの構造
 * - ファイルアップロードのバリデーション
 * - セキュリティ対策
 * - 複数ファイルのアップロード
 * - ファイル保存と管理
 */

echo "=== Phase 3.4: ファイルアップロード処理 ===\n\n";

echo "--- 1. $_FILESの構造 ---\n";
/**
 * $_FILES配列の構造:
 *
 * $_FILES['field_name'] = [
 *     'name' => 'original_filename.jpg',  // 元のファイル名
 *     'type' => 'image/jpeg',              // MIMEタイプ
 *     'tmp_name' => '/tmp/phpXXXXXX',     // 一時ファイルのパス
 *     'error' => 0,                        // エラーコード
 *     'size' => 12345                      // ファイルサイズ（バイト）
 * ];
 */

echo "✅ \$_FILESの構造:\n";
echo "  - name: 元のファイル名\n";
echo "  - type: MIMEタイプ（ブラウザ提供、信頼できない）\n";
echo "  - tmp_name: 一時ファイルパス\n";
echo "  - error: エラーコード（UPLOAD_ERR_*定数）\n";
echo "  - size: ファイルサイズ（バイト）\n\n";

echo "--- 2. アップロードエラーコード ---\n";

/**
 * アップロードエラーコードクラス
 */
class UploadError
{
    public const ERROR_MESSAGES = [
        UPLOAD_ERR_OK => 'アップロード成功',
        UPLOAD_ERR_INI_SIZE => 'アップロードファイルがphp.iniのupload_max_filesizeを超えています',
        UPLOAD_ERR_FORM_SIZE => 'アップロードファイルがフォームのMAX_FILE_SIZEを超えています',
        UPLOAD_ERR_PARTIAL => 'ファイルが部分的にしかアップロードされませんでした',
        UPLOAD_ERR_NO_FILE => 'ファイルがアップロードされませんでした',
        UPLOAD_ERR_NO_TMP_DIR => '一時フォルダがありません',
        UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました',
        UPLOAD_ERR_EXTENSION => 'PHP拡張モジュールがアップロードを中断しました',
    ];

    public static function getMessage(int $errorCode): string
    {
        return self::ERROR_MESSAGES[$errorCode] ?? '不明なエラー';
    }
}

echo "✅ エラーコード一覧:\n";
foreach (UploadError::ERROR_MESSAGES as $code => $message) {
    echo "  $code: $message\n";
}
echo "\n";

echo "--- 3. ファイルアップロードハンドラー ---\n";

/**
 * ファイルアップロードハンドラークラス
 */
class FileUploadHandler
{
    private string $uploadDir;
    private int $maxFileSize;
    private array $allowedMimeTypes;
    private array $allowedExtensions;
    private array $errors = [];

    public function __construct(
        string $uploadDir = 'uploads',
        int $maxFileSize = 5 * 1024 * 1024, // 5MB
        array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf']
    ) {
        $this->uploadDir = $uploadDir;
        $this->maxFileSize = $maxFileSize;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->allowedExtensions = $allowedExtensions;

        $this->ensureUploadDirectory();
    }

    /**
     * アップロードディレクトリを作成
     */
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * ファイルをアップロード
     */
    public function upload(array $file): ?string
    {
        // バリデーション
        if (!$this->validate($file)) {
            return null;
        }

        // 安全なファイル名を生成
        $safeFilename = $this->generateSafeFilename($file['name']);

        // ファイルパス
        $destination = $this->uploadDir . '/' . $safeFilename;

        // ファイルを移動
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'ファイルの保存に失敗しました';
            return null;
        }

        // パーミッション設定
        chmod($destination, 0644);

        return $safeFilename;
    }

    /**
     * ファイルをバリデーション
     */
    private function validate(array $file): bool
    {
        // エラーチェック
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = UploadError::getMessage($file['error']);
            return false;
        }

        // ファイルがアップロードされたものか確認
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = '不正なファイルアップロードです';
            return false;
        }

        // ファイルサイズチェック
        if ($file['size'] > $this->maxFileSize) {
            $maxSizeMB = $this->maxFileSize / 1024 / 1024;
            $this->errors[] = sprintf('ファイルサイズは%.1fMB以下にしてください', $maxSizeMB);
            return false;
        }

        // 拡張子チェック
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->errors[] = '許可されていないファイル形式です（' . implode(', ', $this->allowedExtensions) . 'のみ）';
            return false;
        }

        // MIMEタイプチェック（finfoを使用して実際のMIMEタイプを検証）
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
            $this->errors[] = '許可されていないファイルタイプです';
            return false;
        }

        // 画像ファイルの場合、画像として有効かチェック
        if (str_starts_with($mimeType, 'image/')) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $this->errors[] = '有効な画像ファイルではありません';
                return false;
            }
        }

        return true;
    }

    /**
     * 安全なファイル名を生成
     */
    private function generateSafeFilename(string $originalName): string
    {
        // 拡張子を取得
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // ユニークなファイル名を生成（タイムスタンプ + ランダム文字列）
        $uniqueName = date('YmdHis') . '_' . bin2hex(random_bytes(8));

        return $uniqueName . '.' . $extension;
    }

    /**
     * エラーを取得
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * エラーをクリア
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
}

echo "✅ FileUploadHandlerクラスを実装しました\n\n";

echo "--- 4. シミュレーション: 画像ファイルのアップロード ---\n";

// シミュレーション用の一時ファイルを作成
$tempDir = sys_get_temp_dir();
$tempFile = $tempDir . '/test_upload_' . uniqid() . '.jpg';

// 1x1ピクセルの有効なJPEG画像を作成
$image = imagecreate(1, 1);
imagecolorallocate($image, 255, 255, 255);
imagejpeg($image, $tempFile);
imagedestroy($image);

// $_FILESのシミュレーション
$file = [
    'name' => 'test_image.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tempFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($tempFile)
];

echo "✅ アップロードテスト:\n";
echo "ファイル名: {$file['name']}\n";
echo "サイズ: {$file['size']} バイト\n";
echo "MIMEタイプ: {$file['type']}\n\n";

$uploadDir = __DIR__ . '/../../../temp_uploads';
$handler = new FileUploadHandler(
    uploadDir: $uploadDir,
    maxFileSize: 5 * 1024 * 1024, // 5MB
    allowedMimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif']
);

$savedFilename = $handler->upload($file);

if ($savedFilename !== null) {
    echo "✓ アップロード成功\n";
    echo "保存先: $uploadDir/$savedFilename\n\n";

    // クリーンアップ
    @unlink($uploadDir . '/' . $savedFilename);
    @rmdir($uploadDir);
} else {
    echo "✗ アップロード失敗\n";
    foreach ($handler->getErrors() as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

// 一時ファイルを削除
@unlink($tempFile);

echo "--- 5. 不正なファイルのテスト ---\n";

// PHPファイルのアップロード試行（セキュリティリスク）
$phpFile = $tempDir . '/malicious_' . uniqid() . '.php';
file_put_contents($phpFile, '<?php echo "malicious code"; ?>');

$maliciousFile = [
    'name' => 'script.php',
    'type' => 'application/x-php', // 偽装されたMIMEタイプ
    'tmp_name' => $phpFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($phpFile)
];

echo "✅ 不正なファイルのアップロード試行:\n";
echo "ファイル名: {$maliciousFile['name']}\n\n";

$handler->clearErrors();
$savedFilename = $handler->upload($maliciousFile);

if ($savedFilename !== null) {
    echo "✗ セキュリティ警告: 不正なファイルがアップロードされました\n\n";
} else {
    echo "✓ セキュリティ: 不正なファイルを検出し、ブロックしました\n";
    foreach ($handler->getErrors() as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

// 一時ファイルを削除
@unlink($phpFile);

echo "--- 6. 複数ファイルのアップロード ---\n";

/**
 * 複数ファイルアップロードハンドラー
 */
class MultiFileUploadHandler extends FileUploadHandler
{
    /**
     * 複数ファイルをアップロード
     */
    public function uploadMultiple(array $files): array
    {
        $uploaded = [];

        // $_FILESの配列を正規化
        $normalizedFiles = $this->normalizeFilesArray($files);

        foreach ($normalizedFiles as $file) {
            // ファイルがアップロードされているか
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $filename = $this->upload($file);
            if ($filename !== null) {
                $uploaded[] = $filename;
            }
        }

        return $uploaded;
    }

    /**
     * $_FILESの配列を正規化
     *
     * $_FILES['files']['name'][0] => $_FILES[0]['name'] の形式に変換
     */
    private function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        // 単一ファイルの場合
        if (isset($files['name']) && is_string($files['name'])) {
            return [$files];
        }

        // 複数ファイルの場合
        if (isset($files['name']) && is_array($files['name'])) {
            $count = count($files['name']);

            for ($i = 0; $i < $count; $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
            }
        }

        return $normalized;
    }
}

echo "✅ 複数ファイルアップロードのシミュレーション:\n";

// 複数の一時ファイルを作成
$tempFiles = [];
for ($i = 0; $i < 3; $i++) {
    $tempFile = $tempDir . '/multi_test_' . $i . '_' . uniqid() . '.jpg';
    $image = imagecreate(1, 1);
    imagecolorallocate($image, 255, 255, 255);
    imagejpeg($image, $tempFile);
    imagedestroy($image);
    $tempFiles[] = $tempFile;
}

// 複数ファイルの $_FILES シミュレーション
$multiFiles = [
    'name' => ['image1.jpg', 'image2.jpg', 'image3.jpg'],
    'type' => ['image/jpeg', 'image/jpeg', 'image/jpeg'],
    'tmp_name' => $tempFiles,
    'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_OK],
    'size' => array_map('filesize', $tempFiles)
];

$multiHandler = new MultiFileUploadHandler($uploadDir);
$uploadedFiles = $multiHandler->uploadMultiple($multiFiles);

echo "アップロードされたファイル数: " . count($uploadedFiles) . "\n";
foreach ($uploadedFiles as $index => $filename) {
    echo "  " . ($index + 1) . ". $filename\n";
}
echo "\n";

// クリーンアップ
foreach ($uploadedFiles as $filename) {
    @unlink($uploadDir . '/' . $filename);
}
foreach ($tempFiles as $tempFile) {
    @unlink($tempFile);
}
@rmdir($uploadDir);

echo "--- 7. セキュリティのベストプラクティス ---\n";
echo "✅ ファイルアップロードのセキュリティ対策:\n";
echo "  1. is_uploaded_file()で正規のアップロードか確認\n";
echo "  2. move_uploaded_file()でファイルを移動\n";
echo "  3. ファイルサイズを制限\n";
echo "  4. 拡張子をホワイトリスト方式で検証\n";
echo "  5. finfoでMIMEタイプを検証（\$_FILES['type']は信頼しない）\n";
echo "  6. 画像はgetimagesize()で有効性を確認\n";
echo "  7. ファイル名はランダム生成（元の名前を使わない）\n";
echo "  8. アップロードディレクトリは実行権限を与えない\n";
echo "  9. アップロードディレクトリはドキュメントルート外に配置\n";
echo "  10. .htaccessでPHPの実行を禁止\n\n";

echo "❌ 危険な実装:\n";
echo "  1. \$_FILES['type']を信頼する\n";
echo "  2. 元のファイル名をそのまま使用\n";
echo "  3. ファイル検証なしでアップロード\n";
echo "  4. 実行可能ファイルのアップロードを許可\n";
echo "  5. エラーハンドリングなし\n\n";

echo "--- 8. php.iniの設定 ---\n";
echo "✅ アップロード関連のphp.ini設定:\n";
echo "  file_uploads = On                    ; アップロードを有効化\n";
echo "  upload_max_filesize = 10M            ; 最大ファイルサイズ\n";
echo "  post_max_size = 12M                  ; POSTデータの最大サイズ\n";
echo "  max_file_uploads = 20                ; 同時アップロード数\n";
echo "  upload_tmp_dir = /tmp                ; 一時ディレクトリ\n\n";

echo "現在の設定:\n";
echo "  upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "  post_max_size: " . ini_get('post_max_size') . "\n";
echo "  max_file_uploads: " . ini_get('max_file_uploads') . "\n\n";

echo "--- 9. まとめ ---\n";
echo "セキュアなファイルアップロード:\n";
echo "  ✓ 厳格なバリデーション（サイズ、拡張子、MIMEタイプ）\n";
echo "  ✓ ファイル名はランダム生成\n";
echo "  ✓ アップロードディレクトリのセキュリティ設定\n";
echo "  ✓ エラーハンドリングの実装\n";
echo "  ✓ 複数ファイルのサポート\n\n";

echo "=== Phase 3.4: ファイルアップロード処理 完了 ===\n";
