# CLAUDE.md

このファイルは、このリポジトリでClaude Code (claude.ai/code) が作業する際のガイダンスを提供します。

## プロジェクト概要

これは、基礎構文から実践的なスキルまで学習者を導く包括的なPHP学習プロジェクトです。プロジェクトはモダンなPHPのベストプラクティス（PHP 8.1+）に従い、4つのフェーズにわたる構造化されたカリキュラムを含んでいます。

## ドキュメント構成

`docs/` ディレクトリには包括的な学習教材が含まれています：

- **[README.md](docs/README.md)** - ドキュメントの目次とナビゲーションガイド
- **[requirements.md](docs/requirements.md)** - プロジェクトの目的、学習目標、カリキュラム構成
- **[tech_stack.md](docs/tech_stack.md)** - 使用技術、ツール、バージョン要件
- **[setup.md](docs/setup.md)** - PHP、Composer、MySQL、Dockerの環境構築手順
- **[learning_guide.md](docs/learning_guide.md)** - 学習方法、コツ、フェーズ別ガイド
- **[progress.md](docs/progress.md)** - タスクとマイルストーンを含む学習進捗管理
- **[coding_standards.md](docs/coding_standards.md)** - PSR-1/4/12標準とコーディング規約
- **[troubleshooting.md](docs/troubleshooting.md)** - よくある問題と解決方法
- **[resources.md](docs/resources.md)** - 厳選された学習リソースと参考資料

## アーキテクチャ

プロジェクトは標準的なPHPプロジェクト構造に従います：

```
php_practice/
├── docs/              # 包括的なドキュメント
├── src/               # ソースコード（PSR-4オートローディング）
│   ├── Controllers/   # コントローラー
│   ├── Models/        # モデル
│   ├── Services/      # サービス層
│   ├── Repositories/  # リポジトリ層
│   └── Utils/         # ユーティリティ
├── tests/             # PHPUnitテスト
├── public/            # 公開Webディレクトリ
├── config/            # 設定ファイル
└── composer.json      # 依存関係とオートローディング
```

## 学習カリキュラム

### Phase 1: 基礎編（2-3週間）
- 変数、演算子、制御構造
- 関数と配列
- PHP 8の機能（厳格な型、match式）

### Phase 2: 中級編（3-4週間）
- オブジェクト指向プログラミング
- 名前空間とオートローディング
- エラーハンドリングとファイル操作

### Phase 3: 応用編（4-5週間）
- PDOによるデータベース操作
- セキュリティのベストプラクティス
- フォームバリデーションとREST API開発

### Phase 4: 実践プロジェクト（3-4週間）
- Todoアプリケーション
- ブログシステム
- REST API実装

## 技術標準

- **PHPバージョン**: 8.1以上（推奨: 8.3）
- **コーディング規約**: PSR-1、PSR-4、PSR-12
- **型宣言**: 厳格な型必須（`declare(strict_types=1)`）
- **データベース**: MySQL 8.0以上 または PostgreSQL 13以上
- **テスト**: PHPUnit 10.x
- **品質ツール**: PHPStan（レベル5以上）、PHP CS Fixer

## 開発

### PHPファイルの実行

```bash
# PHPファイルを直接実行
php filename.php

# 対話型PHPシェル
php -a

# 構文チェック
php -l filename.php

# ビルトインWebサーバー
php -S localhost:8000 -t public/
```

### Composerの使用

```bash
# 依存関係のインストール
composer install

# テストの実行
composer test

# 静的解析
composer phpstan

# コードフォーマット
composer cs-fix
```

## プロジェクトステータス

- **開始日**: 2025年12月25日
- **現在のフェーズ**: Phase 0（環境構築）
- **全体進捗**: 0%

## 重要な原則

1. **モダンPHP**: PHP 8以降の機能を使用（コンストラクタプロモーション、Union型、match式）
2. **型安全性**: 常に厳格な型と型宣言を使用
3. **セキュリティ第一**: OWASPガイドラインに従い、プリペアドステートメントを使用
4. **クリーンコード**: SOLID原則とDRYを適用
5. **テストカバレッジ**: すべてのビジネスロジックにテストを記述

## プロジェクトルール

### 作業開始時

1. **ドキュメントの確認**
   - 作業を開始する前に、必ず `docs/` 配下の関連ドキュメントを確認すること
   - 特に以下のドキュメントは重要：
     - [coding_standards.md](docs/coding_standards.md) - コーディング規約
     - [learning_guide.md](docs/learning_guide.md) - 学習の進め方
     - [progress.md](docs/progress.md) - 現在の進捗状況

### 作業終了時

1. **進捗の記録**
   - 作業完了後は、必ず [docs/progress.md](docs/progress.md) に作業内容を反映すること
   - 完了したタスクのチェックボックスを更新
   - 学習時間やメトリクスを記録

2. **リポジトリへの反映**
   - GitHub CLI (`gh`) を使用してリポジトリに変更を反映すること
   - コミットメッセージは日本語で記述
   - 適切な粒度でコミットを作成

### コミュニケーション

1. **言語**
   - このプロジェクトでは基本的に日本語でやり取りを行う
   - コードのコメントも日本語で記述
   - ドキュメントも日本語で記述
   - 変数名やクラス名は英語を使用（PSR準拠）

2. **コメント規則**
   - PHPDocコメントは日本語で記述
   - インラインコメントも日本語で記述
   - コミットメッセージは日本語で記述

### 例：コメントの記述方法

```php
<?php

declare(strict_types=1);

namespace App\Models;

/**
 * ユーザーモデルクラス
 *
 * ユーザー情報の管理と操作を担当する
 */
class User
{
    /**
     * コンストラクタ
     *
     * @param int $id ユーザーID
     * @param string $name ユーザー名
     * @param string $email メールアドレス
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
    ) {}

    /**
     * ユーザー名を取得する
     *
     * @return string ユーザー名
     */
    public function getName(): string
    {
        // ユーザー名を返す
        return $this->name;
    }
}
```

## 参考資料

詳細情報については、`docs/` ディレクトリ内の包括的なドキュメントを参照してください：
- [学習ガイド](docs/learning_guide.md) - 学習の進め方
- [コーディング規約](docs/coding_standards.md) - コードスタイル要件
- [トラブルシューティング](docs/troubleshooting.md) - 問題に遭遇した場合
