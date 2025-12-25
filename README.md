# PHP学習プロジェクト

基礎から実践まで体系的に学ぶPHP 8.3+のカリキュラム

## 📋 プロジェクト概要

このプロジェクトは、PHPの基礎構文から実践的なスキルまで、体系的に学習できる包括的な学習カリキュラムです。モダンなPHP（PHP 8.1以上）のベストプラクティスに従い、4つのフェーズで段階的にスキルアップできます。

## 🎯 学習目標

- **Phase 1: 基礎編**（2-3週間） - 変数、制御構造、関数、配列
- **Phase 2: 中級編**（3-4週間） - OOP、名前空間、エラーハンドリング
- **Phase 3: 応用編**（4-5週間） - データベース操作、セキュリティ、REST API
- **Phase 4: 実践プロジェクト**（3-4週間） - Todoアプリ、ブログシステム、REST API

## 🚀 クイックスタート

### 必要な環境

- PHP 8.1以上（推奨: 8.3+）
- Composer 2.x
- SQLite 3.x（PHP組み込み）
- Git
- MySQL 8.0以上（オプション・Phase 3以降）

### インストール

```bash
# リポジトリをクローン
git clone https://github.com/aruto1204/php_practice.git
cd php_practice

# Composerの依存関係をインストール
php composer.phar install

# Hello Worldプログラムを実行
php public/index.php
```

### 実行結果

```
==================================
  PHP学習プロジェクト
==================================

Hello, PHP World!

【PHP環境情報】
PHPバージョン: 8.4.5
Zendエンジン: 4.4.5
OSタイプ: Darwin

【拡張機能の確認】
  mbstring: ✓ 有効
  pdo: ✓ 有効
  json: ✓ 有効
  curl: ✓ 有効
  xml: ✓ 有効

環境構築が正常に完了しました！
==================================
```

## 📚 ドキュメント

詳細なドキュメントは `docs/` ディレクトリにあります：

- **[README.md](docs/README.md)** - ドキュメント目次
- **[requirements.md](docs/requirements.md)** - プロジェクト要件定義
- **[tech_stack.md](docs/tech_stack.md)** - 技術スタック
- **[setup.md](docs/setup.md)** - 環境構築ガイド
- **[learning_guide.md](docs/learning_guide.md)** - 学習方法
- **[progress.md](docs/progress.md)** - 進捗管理
- **[coding_standards.md](docs/coding_standards.md)** - コーディング規約
- **[troubleshooting.md](docs/troubleshooting.md)** - トラブルシューティング
- **[resources.md](docs/resources.md)** - 参考リソース

## 🏗️ プロジェクト構造

```
php_practice/
├── docs/              # ドキュメント
├── src/               # ソースコード（PSR-4）
├── tests/             # テストコード
├── public/            # 公開ディレクトリ
├── config/            # 設定ファイル
├── composer.json      # 依存関係定義
└── CLAUDE.md          # AI支援ガイド
```

## 💻 開発

### Composerコマンド

```bash
# テストの実行
php composer.phar test

# 静的解析
php composer.phar phpstan

# コードフォーマット
php composer.phar cs-fix

# フォーマットチェック
php composer.phar cs-check
```

### PHPビルトインサーバー

```bash
# Webサーバーを起動
php -S localhost:8000 -t public/

# ブラウザでアクセス
# http://localhost:8000
```

### データベーステスト

```bash
# データベース接続テストを実行
php public/db_test.php
```

## 📖 学習の進め方

1. **[環境構築ガイド](docs/setup.md)** に従って開発環境をセットアップ
2. **[学習ガイド](docs/learning_guide.md)** で学習方法を確認
3. **[進捗管理](docs/progress.md)** でPhase 1から学習を開始
4. 各フェーズで実践的なコードを書きながら学習
5. **[コーディング規約](docs/coding_standards.md)** に従ってコードを記述

## 🎓 技術標準

- **PHPバージョン**: 8.1以上（推奨: 8.3+）
- **コーディング規約**: PSR-1、PSR-4、PSR-12
- **型宣言**: 厳格な型必須（`declare(strict_types=1)`）
- **テスト**: PHPUnit 10.x
- **品質ツール**: PHPStan（レベル5以上）、PHP CS Fixer

## 🔑 重要な原則

1. **モダンPHP**: PHP 8以降の機能を積極的に使用
2. **型安全性**: 常に厳格な型と型宣言を使用
3. **セキュリティ第一**: OWASPガイドラインに従う
4. **クリーンコード**: SOLID原則とDRYを適用
5. **テストカバレッジ**: ビジネスロジックにテストを記述

## 📈 進捗状況

現在のステータス: **Phase 0 - 環境構築完了！（100%） 🎉**

- ✅ PHP 8.4.5のインストール確認
- ✅ Composer 2.9.2のインストール
- ✅ SQLiteデータベースのセットアップ
- ✅ プロジェクト構造の作成
- ✅ Hello Worldプログラムの実行
- ✅ データベース接続テストの実行

**次のステップ**: Phase 1（基礎編）の学習開始

詳細は [docs/progress.md](docs/progress.md) を参照してください。

## 🤝 貢献

このプロジェクトは個人学習用ですが、改善提案は歓迎します。

## 📝 ライセンス

MIT License

## 🔗 参考リソース

- [PHP公式マニュアル](https://www.php.net/manual/ja/)
- [PSR Standards](https://www.php-fig.org/psr/)
- [PHP: The Right Way](https://phptherightway.com/)

---

**プロジェクト開始日**: 2025年12月25日
**最終更新日**: 2025年12月25日

Happy Coding! 🚀
