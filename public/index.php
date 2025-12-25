<?php

declare(strict_types=1);

/**
 * PHP学習プロジェクト - メインエントリーポイント
 *
 * このファイルはプロジェクトのメインエントリーポイントです。
 * 基本的なPHPの動作確認とオートローディングの設定を行います。
 */

// Composerのオートローダーを読み込み
require_once __DIR__ . '/../vendor/autoload.php';

// 環境情報の表示
echo "==================================" . PHP_EOL;
echo "  PHP学習プロジェクト" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo PHP_EOL;

echo "Hello, PHP World!" . PHP_EOL;
echo PHP_EOL;

// PHP環境情報
echo "【PHP環境情報】" . PHP_EOL;
echo "PHPバージョン: " . PHP_VERSION . PHP_EOL;
echo "Zendエンジン: " . zend_version() . PHP_EOL;
echo "OSタイプ: " . PHP_OS . PHP_EOL;
echo PHP_EOL;

// 重要な拡張機能の確認
echo "【拡張機能の確認】" . PHP_EOL;
$extensions = ['mbstring', 'pdo', 'json', 'curl', 'xml'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✓ 有効' : '✗ 無効';
    echo "  {$ext}: {$status}" . PHP_EOL;
}
echo PHP_EOL;

echo "環境構築が正常に完了しました！" . PHP_EOL;
echo "==================================" . PHP_EOL;
