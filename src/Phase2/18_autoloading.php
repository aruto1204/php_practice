<?php

declare(strict_types=1);

/**
 * Phase 2.3: オートローディング
 *
 * このファイルでは、PHPのオートローディングについて学習します。
 * オートローディングは、クラスファイルを自動的に読み込む仕組みです。
 *
 * 学習内容:
 * 1. オートローディングの必要性
 * 2. spl_autoload_register の使い方
 * 3. PSR-4 オートローディング標準
 * 4. Composer のオートローディング
 * 5. オートローディングのベストプラクティス
 */

echo "=== Phase 2.3: オートローディング ===\n\n";

// ============================================================
// 1. オートローディングの必要性
// ============================================================

echo "--- 1. オートローディングの必要性 ---\n\n";

/**
 * オートローディングとは？
 *
 * オートローディングは、クラスが使用される際に自動的にそのクラスファイルを
 * 読み込む仕組みです。これにより、手動でrequire/includeする必要がなくなります。
 */

echo "【オートローディング無しの問題】\n\n";

echo "問題のあるコード例:\n";
echo "<?php\n";
echo "require_once 'Models/User.php';\n";
echo "require_once 'Models/Post.php';\n";
echo "require_once 'Models/Comment.php';\n";
echo "require_once 'Services/UserService.php';\n";
echo "require_once 'Services/PostService.php';\n";
echo "// ... 何十個も続く ...\n\n";

echo "問題点:\n";
echo "- ファイルの数が増えるとメンテナンスが困難\n";
echo "- ファイルパスの変更に弱い\n";
echo "- 不要なファイルまで読み込んでしまう可能性\n";
echo "- タイポによるエラーが発生しやすい\n\n";

echo "【オートローディングの利点】\n\n";

echo "✅ 必要なクラスだけを自動的に読み込む\n";
echo "✅ require/include文を書く必要がない\n";
echo "✅ ファイル構成の変更に強い\n";
echo "✅ コードがシンプルで読みやすい\n\n";

// ============================================================
// 2. spl_autoload_register の基本
// ============================================================

echo "--- 2. spl_autoload_register の基本 ---\n\n";

/**
 * spl_autoload_register
 *
 * PHPの標準関数で、クラスが見つからない時に呼ばれる関数を登録します。
 */

echo "【基本的な使い方】\n\n";

// 例: シンプルなオートローダー
echo "<?php\n";
echo "spl_autoload_register(function (\$className) {\n";
echo "    // クラス名からファイルパスを生成\n";
echo "    \$file = __DIR__ . '/' . \$className . '.php';\n";
echo "    \n";
echo "    // ファイルが存在すれば読み込む\n";
echo "    if (file_exists(\$file)) {\n";
echo "        require_once \$file;\n";
echo "    }\n";
echo "});\n\n";

echo "動作の流れ:\n";
echo "1. new User() が実行される\n";
echo "2. User クラスが見つからない\n";
echo "3. 登録されたオートローダーが呼ばれる\n";
echo "4. クラス名（'User'）が引数として渡される\n";
echo "5. ファイルパス（'User.php'）を生成\n";
echo "6. ファイルが存在すれば require_once で読み込む\n\n";

// ============================================================
// 3. 名前空間対応のオートローダー
// ============================================================

echo "--- 3. 名前空間対応のオートローダー ---\n\n";

/**
 * 名前空間を含むクラス名の処理
 *
 * 名前空間の区切り文字（\\）をディレクトリ区切り文字（/）に変換します。
 */

echo "【名前空間対応オートローダー】\n\n";

echo "<?php\n";
echo "spl_autoload_register(function (\$className) {\n";
echo "    // 名前空間の区切りをディレクトリ区切りに変換\n";
echo "    // App\\Models\\User → App/Models/User\n";
echo "    \$classPath = str_replace('\\\\', DIRECTORY_SEPARATOR, \$className);\n";
echo "    \n";
echo "    // ファイルパスを生成\n";
echo "    \$file = __DIR__ . '/src/' . \$classPath . '.php';\n";
echo "    \n";
echo "    if (file_exists(\$file)) {\n";
echo "        require_once \$file;\n";
echo "    }\n";
echo "});\n\n";

echo "例:\n";
echo "クラス名: App\\Models\\User\n";
echo "↓ 変換\n";
echo "パス: src/App/Models/User.php\n\n";

// ============================================================
// 4. PSR-4 オートローディング標準
// ============================================================

echo "--- 4. PSR-4 オートローディング標準 ---\n\n";

/**
 * PSR-4 とは？
 *
 * PHP Standards Recommendations の第4番目の標準。
 * 名前空間とファイルパスの対応関係を定義しています。
 */

echo "【PSR-4 の基本ルール】\n\n";

echo "1. 名前空間のプレフィックスとベースディレクトリを対応させる\n";
echo "   名前空間: App\\\n";
echo "   ベース: src/\n\n";

echo "2. サブ名前空間はサブディレクトリに対応\n";
echo "   App\\Models\\User → src/Models/User.php\n";
echo "   App\\Services\\UserService → src/Services/UserService.php\n\n";

echo "3. クラス名はファイル名に対応（拡張子 .php）\n";
echo "   User クラス → User.php\n\n";

echo "【PSR-4 オートローダーの実装】\n\n";

echo "<?php\n";
echo "spl_autoload_register(function (\$className) {\n";
echo "    // 名前空間プレフィックス\n";
echo "    \$prefix = 'App\\\\';\n";
echo "    \n";
echo "    // ベースディレクトリ\n";
echo "    \$baseDir = __DIR__ . '/src/';\n";
echo "    \n";
echo "    // クラスがプレフィックスを使用しているか確認\n";
echo "    \$len = strlen(\$prefix);\n";
echo "    if (strncmp(\$prefix, \$className, \$len) !== 0) {\n";
echo "        // プレフィックスが一致しない場合は処理しない\n";
echo "        return;\n";
echo "    }\n";
echo "    \n";
echo "    // 相対クラス名を取得\n";
echo "    \$relativeClass = substr(\$className, \$len);\n";
echo "    \n";
echo "    // ファイルパスを生成\n";
echo "    \$file = \$baseDir . str_replace('\\\\', '/', \$relativeClass) . '.php';\n";
echo "    \n";
echo "    // ファイルが存在すれば読み込む\n";
echo "    if (file_exists(\$file)) {\n";
echo "        require \$file;\n";
echo "    }\n";
echo "});\n\n";

echo "動作例:\n";
echo "クラス: App\\Models\\User\n";
echo "プレフィックス: App\\\n";
echo "相対クラス名: Models\\User\n";
echo "ファイルパス: src/Models/User.php\n\n";

// ============================================================
// 5. Composer のオートローディング
// ============================================================

echo "--- 5. Composer のオートローディング ---\n\n";

/**
 * Composer のオートローディング
 *
 * Composer は PSR-4 準拠のオートローダーを自動生成してくれます。
 * これが最も推奨される方法です。
 */

echo "【composer.json の設定】\n\n";

echo "{\n";
echo "    \"autoload\": {\n";
echo "        \"psr-4\": {\n";
echo "            \"App\\\\\": \"src/\"\n";
echo "        }\n";
echo "    }\n";
echo "}\n\n";

echo "設定の意味:\n";
echo "- App\\ で始まる名前空間は src/ ディレクトリに対応\n";
echo "- App\\Models\\User → src/Models/User.php\n";
echo "- App\\Services\\UserService → src/Services/UserService.php\n\n";

echo "【複数の名前空間を登録】\n\n";

echo "{\n";
echo "    \"autoload\": {\n";
echo "        \"psr-4\": {\n";
echo "            \"App\\\\\": \"src/\",\n";
echo "            \"Database\\\\\": \"database/\",\n";
echo "            \"Tests\\\\\": \"tests/\"\n";
echo "        }\n";
echo "    }\n";
echo "}\n\n";

echo "【Composer オートローダーの生成】\n\n";

echo "コマンド:\n";
echo "$ composer dump-autoload\n\n";

echo "これにより生成されるファイル:\n";
echo "- vendor/autoload.php\n";
echo "- vendor/composer/autoload_*.php\n\n";

echo "【オートローダーの使用】\n\n";

echo "<?php\n";
echo "// すべてのPHPファイルの先頭で読み込む\n";
echo "require_once __DIR__ . '/vendor/autoload.php';\n";
echo "\n";
echo "// これだけで全クラスが自動的に読み込まれる\n";
echo "use App\\Models\\User;\n";
echo "use App\\Services\\UserService;\n";
echo "\n";
echo "\$user = new User('Taro', 'taro@example.com');\n";
echo "\$service = new UserService();\n\n";

// ============================================================
// 6. クラスマップとファイルのオートロード
// ============================================================

echo "--- 6. クラスマップとファイルのオートロード ---\n\n";

/**
 * Composer には PSR-4 以外のオートロード方法もあります
 */

echo "【classmap: 特定のディレクトリを全スキャン】\n\n";

echo "{\n";
echo "    \"autoload\": {\n";
echo "        \"psr-4\": {\n";
echo "            \"App\\\\\": \"src/\"\n";
echo "        },\n";
echo "        \"classmap\": [\n";
echo "            \"database/migrations\",\n";
echo "            \"database/seeds\"\n";
echo "        ]\n";
echo "    }\n";
echo "}\n\n";

echo "特徴:\n";
echo "- 指定したディレクトリ内のすべてのPHPファイルをスキャン\n";
echo "- 名前空間の規約に従わないクラスも読み込める\n";
echo "- パフォーマンスが良い（事前にマップを生成）\n\n";

echo "【files: 個別のファイルを読み込み】\n\n";

echo "{\n";
echo "    \"autoload\": {\n";
echo "        \"files\": [\n";
echo "            \"src/helpers.php\",\n";
echo "            \"src/constants.php\"\n";
echo "        ]\n";
echo "    }\n";
echo "}\n\n";

echo "用途:\n";
echo "- グローバル関数の定義ファイル\n";
echo "- グローバル定数の定義ファイル\n";
echo "- 初期化スクリプト\n\n";

// ============================================================
// 7. 開発用オートローディング（autoload-dev）
// ============================================================

echo "--- 7. 開発用オートローディング（autoload-dev） ---\n\n";

/**
 * テストコードなど、開発時のみ使用するクラスの設定
 */

echo "{\n";
echo "    \"autoload\": {\n";
echo "        \"psr-4\": {\n";
echo "            \"App\\\\\": \"src/\"\n";
echo "        }\n";
echo "    },\n";
echo "    \"autoload-dev\": {\n";
echo "        \"psr-4\": {\n";
echo "            \"Tests\\\\\": \"tests/\"\n";
echo "        }\n";
echo "    }\n";
echo "}\n\n";

echo "違い:\n";
echo "- autoload: 本番環境でも読み込まれる\n";
echo "- autoload-dev: 開発環境でのみ読み込まれる\n\n";

echo "本番環境でのインストール:\n";
echo "$ composer install --no-dev\n";
echo "→ autoload-dev は読み込まれない\n\n";

// ============================================================
// 8. オートローディングの最適化
// ============================================================

echo "--- 8. オートローディングの最適化 ---\n\n";

/**
 * Composer にはオートローディングを高速化するオプションがあります
 */

echo "【最適化レベル】\n\n";

echo "1. 通常のオートロード\n";
echo "   $ composer dump-autoload\n";
echo "   - ファイルを都度検索\n\n";

echo "2. クラスマップの最適化（-o）\n";
echo "   $ composer dump-autoload -o\n";
echo "   $ composer dump-autoload --optimize\n";
echo "   - PSR-4/PSR-0 の名前空間をクラスマップに変換\n";
echo "   - 本番環境で推奨\n\n";

echo "3. Authoritative クラスマップ（-a）\n";
echo "   $ composer dump-autoload -a\n";
echo "   $ composer dump-autoload --classmap-authoritative\n";
echo "   - クラスマップにないクラスは存在しないと判断\n";
echo "   - 最も高速だが、新しいクラスを追加したら再生成が必要\n\n";

echo "4. APCu キャッシュ（--apcu）\n";
echo "   $ composer dump-autoload --apcu\n";
echo "   - APCu 拡張を使ってクラスマップをキャッシュ\n";
echo "   - さらなる高速化が可能\n\n";

echo "本番環境での推奨設定:\n";
echo "$ composer install --no-dev --optimize-autoloader\n\n";

// ============================================================
// 9. 実践例: プロジェクト構成とオートローディング
// ============================================================

echo "--- 9. 実践例: プロジェクト構成とオートローディング ---\n\n";

echo "【プロジェクト構成】\n\n";

echo "my-project/\n";
echo "├── composer.json\n";
echo "├── public/\n";
echo "│   └── index.php\n";
echo "├── src/\n";
echo "│   ├── Models/\n";
echo "│   │   └── User.php\n";
echo "│   ├── Services/\n";
echo "│   │   └── UserService.php\n";
echo "│   └── Controllers/\n";
echo "│       └── UserController.php\n";
echo "└── vendor/\n";
echo "    └── autoload.php\n\n";

echo "【composer.json】\n\n";

echo "{\n";
echo "    \"name\": \"my-company/my-project\",\n";
echo "    \"autoload\": {\n";
echo "        \"psr-4\": {\n";
echo "            \"App\\\\\": \"src/\"\n";
echo "        }\n";
echo "    },\n";
echo "    \"require\": {\n";
echo "        \"php\": \"^8.1\"\n";
echo "    }\n";
echo "}\n\n";

echo "【src/Models/User.php】\n\n";

echo "<?php\n";
echo "namespace App\\Models;\n";
echo "\n";
echo "class User\n";
echo "{\n";
echo "    public function __construct(\n";
echo "        private string \$name,\n";
echo "        private string \$email,\n";
echo "    ) {}\n";
echo "}\n\n";

echo "【public/index.php】\n\n";

echo "<?php\n";
echo "require_once __DIR__ . '/../vendor/autoload.php';\n";
echo "\n";
echo "use App\\Models\\User;\n";
echo "use App\\Services\\UserService;\n";
echo "\n";
echo "\$user = new User('Taro', 'taro@example.com');\n";
echo "\$service = new UserService();\n\n";

// ============================================================
// 10. ベストプラクティス
// ============================================================

echo "--- 10. ベストプラクティス ---\n\n";

echo "【オートローディングのベストプラクティス】\n\n";

echo "1. Composer のオートローディングを使う\n";
echo "   ✅ 標準的で信頼性が高い\n";
echo "   ✅ メンテナンスが容易\n";
echo "   ❌ 独自実装は避ける\n\n";

echo "2. PSR-4 標準に従う\n";
echo "   ✅ 名前空間とディレクトリ構造を一致させる\n";
echo "   ✅ 1ファイル = 1クラス\n\n";

echo "3. 本番環境では最適化する\n";
echo "   $ composer install --no-dev --optimize-autoloader\n\n";

echo "4. クラス名とファイル名を一致させる\n";
echo "   ✅ User クラス → User.php\n";
echo "   ❌ User クラス → user.php（大文字小文字が違う）\n\n";

echo "5. vendor/autoload.php を一度だけ読み込む\n";
echo "   ✅ エントリーポイント（index.php）で読み込む\n";
echo "   ❌ 各ファイルで重複して読み込まない\n\n";

echo "6. テストコードは autoload-dev に配置\n";
echo "   本番環境に不要なコードを含めない\n\n";

// ============================================================
// 11. トラブルシューティング
// ============================================================

echo "--- 11. トラブルシューティング ---\n\n";

echo "【よくある問題と解決方法】\n\n";

echo "❌ 問題1: Class not found エラー\n";
echo "原因:\n";
echo "- composer.json の autoload 設定が間違っている\n";
echo "- ファイルパスや名前空間が PSR-4 に従っていない\n";
echo "- composer dump-autoload を実行していない\n\n";

echo "✅ 解決:\n";
echo "1. composer.json の autoload 設定を確認\n";
echo "2. ファイルパスと名前空間が一致しているか確認\n";
echo "3. composer dump-autoload を実行\n\n";

echo "---\n\n";

echo "❌ 問題2: 新しいクラスが読み込まれない\n";
echo "原因:\n";
echo "- 最適化されたオートローダーが古い\n\n";

echo "✅ 解決:\n";
echo "$ composer dump-autoload\n\n";

echo "---\n\n";

echo "❌ 問題3: 大文字小文字の問題（Mac/Linux vs Windows）\n";
echo "原因:\n";
echo "- ファイル名: user.php\n";
echo "- クラス名: User\n";
echo "- Mac/Linux では動作するが、Windows では動作しない可能性\n\n";

echo "✅ 解決:\n";
echo "ファイル名とクラス名の大文字小文字を完全に一致させる\n\n";

// ============================================================
// まとめ
// ============================================================

echo "\n=== まとめ ===\n\n";

echo "オートローディングの重要なポイント:\n";
echo "1. オートローディングで require/include を書く必要がなくなる\n";
echo "2. spl_autoload_register でカスタムオートローダーを登録できる\n";
echo "3. PSR-4 標準に従い、名前空間とディレクトリを一致させる\n";
echo "4. Composer のオートローディングが最も推奨される方法\n";
echo "5. composer.json の autoload セクションで設定する\n";
echo "6. composer dump-autoload でオートローダーを生成・更新\n";
echo "7. 本番環境では --optimize-autoloader で最適化する\n\n";

echo "Composer オートローディングの設定手順:\n";
echo "1. composer.json に autoload セクションを追加\n";
echo "2. composer dump-autoload を実行\n";
echo "3. vendor/autoload.php を読み込む\n";
echo "4. use文でクラスをインポートして使用\n\n";

echo "次のステップ:\n";
echo "- exercises/08_namespace_practice.php で実践的な演習に挑戦\n";
echo "- 実際にComposerでプロジェクトを構成してみる\n\n";

echo "=== 学習完了 ===\n";
