<?php

declare(strict_types=1);

/**
 * Phase 4.4: 実践プロジェクト1 - Todo アプリケーション
 *
 * このファイルでは、これまで学んだ技術を組み合わせて
 * 実践的なTodoアプリケーションを実装します。
 *
 * 実装内容:
 * 1. タスクの追加・編集・削除（CRUD操作）
 * 2. タスクの完了/未完了切り替え
 * 3. カテゴリー分類
 * 4. 期限管理
 *
 * 使用技術:
 * - OOP（クラス、インターフェース）
 * - デザインパターン（Repository、Service層）
 * - データベース操作（リポジトリパターン）
 * - セッション管理
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Phase4\TodoApp\TodoApp;

// Todoアプリケーションの実行
$app = new TodoApp();
$app->run();

echo "\n";
echo "=== Todoアプリケーションの実装について ===\n\n";

echo "【アーキテクチャ】\n";
echo "このアプリケーションは、レイヤードアーキテクチャを採用しています。\n\n";

echo "1. エンティティ層（Entity）\n";
echo "   - Task.php: タスクのドメインモデル\n";
echo "   - Category.php: カテゴリーのドメインモデル\n";
echo "   - ビジネスルールとバリデーションを含む\n\n";

echo "2. リポジトリ層（Repository）\n";
echo "   - TaskRepository.php: タスクのデータアクセス\n";
echo "   - CategoryRepository.php: カテゴリーのデータアクセス\n";
echo "   - データの永続化と検索を担当\n\n";

echo "3. サービス層（Service）\n";
echo "   - TaskService.php: タスクのビジネスロジック\n";
echo "   - CategoryService.php: カテゴリーのビジネスロジック\n";
echo "   - 複数のリポジトリを調整\n\n";

echo "4. アプリケーション層\n";
echo "   - TodoApp.php: アプリケーションのメインクラス\n";
echo "   - 各サービスを統合して機能を提供\n\n";

echo "【設計パターン】\n";
echo "- Repository パターン: データアクセスの抽象化\n";
echo "- Service パターン: ビジネスロジックのカプセル化\n";
echo "- Dependency Injection: 依存性の注入\n";
echo "- Value Object: イミュータブルな値オブジェクト\n\n";

echo "【主な機能】\n";
echo "1. タスク管理\n";
echo "   - タスクの作成、更新、削除\n";
echo "   - タスクの完了/未完了切り替え\n";
echo "   - タスクの一覧表示（全件、未完了、完了、期限切れ）\n\n";

echo "2. カテゴリー管理\n";
echo "   - カテゴリーの作成、更新、削除\n";
echo "   - カテゴリー別タスク表示\n";
echo "   - カテゴリー削除時の関連タスク処理\n\n";

echo "3. 期限管理\n";
echo "   - タスクへの期限設定\n";
echo "   - 期限切れタスクの検出\n";
echo "   - 期限までの日数計算\n\n";

echo "4. 統計情報\n";
echo "   - 総タスク数\n";
echo "   - 完了/未完了タスク数\n";
echo "   - 期限切れタスク数\n";
echo "   - 完了率の計算\n\n";

echo "【拡張可能性】\n";
echo "このアプリケーションは以下のように拡張できます:\n";
echo "- データベース永続化（PDO、SQLite/MySQL）\n";
echo "- Web UIの追加（HTML、CSS、JavaScript）\n";
echo "- ユーザー認証\n";
echo "- タスクの優先度管理\n";
echo "- タスクの共有機能\n";
echo "- タスクの検索・フィルタリング\n";
echo "- タスクの並び替え（ドラッグ&ドロップ）\n";
echo "- リマインダー通知\n";
echo "- タスクの繰り返し設定\n\n";

echo "【テスト】\n";
echo "tests/TodoApp/ ディレクトリにユニットテストが含まれています。\n";
echo "実行コマンド: composer test tests/TodoApp/\n\n";

echo "=== Phase 4.4 完了 ===\n";
