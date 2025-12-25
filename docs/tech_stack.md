# 技術スタック

## 1. プログラミング言語

### PHP
- **バージョン**: 8.1以上（推奨: 8.3）
- **選定理由**: 
  - モダンな型システムと新機能（Enum、Readonly、名前付き引数など）
  - 豊富なエコシステムとコミュニティサポート
  - Web開発に最適化された言語設計

### JavaScript
- **バージョン**: ES6+
- **用途**: フロントエンドの動的な機能実装
- **ライブラリ**: Vanilla JS（基礎学習のため）

## 2. データベース

### SQLite（メイン使用）
- **バージョン**: 3.x（PHP組み込み）
- **用途**: 学習用メインデータベース
- **選定理由**:
  - セットアップ不要（PHPに組み込み）
  - ファイルベースで管理が容易
  - SQL基礎の学習に最適
  - 後でMySQLへの移行も容易

### MySQL（将来的に使用）
- **バージョン**: 8.0以上
- **用途**: 本番環境想定のRDBMS
- **学習タイミング**: Phase 3以降
- **選定理由**:
  - 実務で広く使用されている
  - 豊富なドキュメント
  - スケーラビリティが高い

### その他のオプション
- **PostgreSQL** 13以上: より高度な機能が必要な場合
- **MariaDB**: MySQLの代替

## 3. 開発ツール

### パッケージマネージャー
- **Composer** 2.x
  - 依存関係管理
  - オートローディング
  - PSR-4準拠のプロジェクト構成

### バージョン管理
- **Git** 2.x
  - ソースコード管理
  - ブランチ戦略: Git Flow
  - コミットメッセージ規約: Conventional Commits

### エディタ/IDE
#### Visual Studio Code（推奨）
- **拡張機能**:
  - PHP Intelephense
  - PHP Debug
  - PHP CS Fixer
  - PHPDoc Generator
  - GitLens
  - Better Comments

#### PHPStorm（代替）
- PHP開発に特化した統合開発環境
- 強力なリファクタリング機能
- 組み込みのデータベースツール

## 4. 開発環境

### ローカル開発環境

#### Docker（推奨）
```yaml
services:
  - PHP 8.3 (php-fpm)
  - Nginx 1.25
  - MySQL 8.0
  - phpMyAdmin
```

#### XAMPP / MAMP（代替）
- 初心者向けのオールインワンパッケージ
- 簡単なセットアップ

#### Laravel Valet（Mac推奨）
- 軽量な開発環境
- 高速な起動

### Webサーバー
- **Nginx**: 本番環境想定
- **Apache**: 代替オプション
- **PHP Built-in Server**: 学習・テスト用

## 5. 品質管理ツール

### コード品質

#### PHPStan
- **レベル**: 5以上（最終的に8を目指す）
- **用途**: 静的解析、型チェック
- **実行**: `composer phpstan`

#### PHP CS Fixer
- **スタンダード**: PSR-12
- **用途**: コードフォーマット自動修正
- **実行**: `composer cs-fix`

#### Psalm
- **レベル**: 4以上
- **用途**: 型安全性の追加チェック
- **実行**: `composer psalm`

### テスティング

#### PHPUnit
- **バージョン**: 10.x
- **カバレッジ**: 80%以上を目標
- **種類**:
  - ユニットテスト
  - 統合テスト
  - 機能テスト

#### Mockery（オプション）
- モックオブジェクトの作成
- テストの柔軟性向上

### デバッグ

#### Xdebug
- **バージョン**: 3.x
- **機能**:
  - ステップ実行
  - ブレークポイント
  - 変数の監視
  - コードカバレッジ

## 6. ライブラリ・フレームワーク

### 基礎学習フェーズ（フレームワーク不使用）
- Pure PHP での実装
- 基本的なMVCパターンの自作

### 応用フェーズ（選択的に使用）

#### Symfony Components
- **使用コンポーネント**:
  - HttpFoundation (Request/Response)
  - Validator
  - Console
  - Dotenv

#### その他ライブラリ
- **Guzzle**: HTTP クライアント
- **Monolog**: ログ管理
- **Carbon**: 日付・時刻操作
- **Twig**: テンプレートエンジン（オプション）
- **PHP-DI**: 依存性注入コンテナ

## 7. セキュリティツール

### 脆弱性スキャン
- **Composer Audit**: 依存関係の脆弱性チェック
- **OWASP ZAP**: Webアプリケーションセキュリティスキャナー

### セキュリティライブラリ
- **paragonie/random_compat**: 暗号論的に安全な乱数生成
- **firebase/php-jwt**: JWT トークン処理

## 8. ドキュメント生成

### phpDocumentor
- API ドキュメントの自動生成
- PHPDoc からHTML生成

### Markdown
- プロジェクトドキュメント作成
- GitHub Pages 対応

## 9. CI/CD（将来的な拡張）

### GitHub Actions
- 自動テスト実行
- コード品質チェック
- 自動デプロイ

### ワークフロー例
```yaml
- Lint check (PHP CS Fixer)
- Static analysis (PHPStan)
- Unit tests (PHPUnit)
- Security scan (Composer Audit)
```

## 10. データベースツール

### マイグレーション
- **Phinx**: データベースマイグレーション管理
- バージョン管理されたスキーマ変更

### GUI ツール
- **phpMyAdmin**: Web ベース管理ツール
- **MySQL Workbench**: デスクトップアプリケーション
- **TablePlus**: モダンなデータベースクライアント

## 11. パフォーマンスモニタリング

### OPcache
- PHPのバイトコードキャッシュ
- 本番環境で有効化

### Blackfire / Xdebug Profiler
- パフォーマンスプロファイリング
- ボトルネックの特定

## 12. API開発ツール

### Postman / Insomnia
- API テストツール
- リクエストのコレクション管理

### Swagger / OpenAPI
- API ドキュメント生成
- インタラクティブなAPIエクスプローラー

## 13. フロントエンド（軽微な使用）

### CSS
- **Pure CSS / Tailwind CSS（CDN）**
- シンプルなスタイリング

### ビルドツール（必要に応じて）
- **Vite**: モダンなビルドツール
- **Webpack**: アセット管理

## 14. バージョン要件まとめ

| ツール/言語 | 最小バージョン | 推奨バージョン |
|------------|--------------|--------------|
| PHP | 8.1 | 8.3 |
| Composer | 2.0 | 2.7+ |
| MySQL | 8.0 | 8.0+ |
| Node.js | 18.x | 20.x LTS |
| Git | 2.30 | 最新版 |
| Docker | 20.x | 最新版 |

## 15. 学習段階別の使用ツール

### Phase 1: 基礎（最小構成）
- PHP 8.3
- MySQL 8.0
- Visual Studio Code
- Git

### Phase 2: 中級（品質向上）
- 上記 + PHPUnit
- PHP CS Fixer
- Xdebug
- Composer（本格使用）

### Phase 3: 応用（プロフェッショナル）
- 上記 + PHPStan
- Docker
- Symfony Components
- Monolog

### Phase 4: 実践（本番想定）
- 上記すべて + CI/CD
- セキュリティスキャン
- パフォーマンスモニタリング

## 16. 推奨学習リソース

### 公式ドキュメント
- [PHP Manual](https://www.php.net/manual/ja/)
- [Composer Documentation](https://getcomposer.org/doc/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

### オンラインプラットフォーム
- PHP: The Right Way
- Laracasts (PHP基礎)
- Symfony Casts

---

**最終更新日**: 2025年12月25日
**次回レビュー予定**: プロジェクト開始後1ヶ月
