# ブログシステム設計ドキュメント

## 概要

このブログシステムは、MVCパターンを採用した実践的なWebアプリケーションです。
ユーザー認証、記事管理、コメント機能、カテゴリー・タグ機能、検索機能を実装します。

## アーキテクチャ

### レイヤー構成

```
┌─────────────────────────────────────┐
│          Presentation Layer         │
│  (Controllers + Views + Router)     │
├─────────────────────────────────────┤
│         Application Layer           │
│         (Services)                  │
├─────────────────────────────────────┤
│          Domain Layer               │
│        (Entities)                   │
├─────────────────────────────────────┤
│       Infrastructure Layer          │
│      (Repositories + Database)      │
└─────────────────────────────────────┘
```

### MVCパターン

- **Model**: エンティティとリポジトリ
- **View**: HTMLテンプレート
- **Controller**: リクエスト処理とレスポンス生成

## データベース設計

### テーブル構成

#### users（ユーザー）
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    bio TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);
```

#### posts（記事）
```sql
CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    category_id INTEGER,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt VARCHAR(500),
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at DATETIME,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

#### categories（カテゴリー）
```sql
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL
);
```

#### tags（タグ）
```sql
CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    created_at DATETIME NOT NULL
);
```

#### post_tags（記事とタグの多対多リレーション）
```sql
CREATE TABLE post_tags (
    post_id INTEGER NOT NULL,
    tag_id INTEGER NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

#### comments（コメント）
```sql
CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## 機能要件

### 1. ユーザー管理
- ユーザー登録（バリデーション付き）
- ログイン/ログアウト
- プロフィール編集
- パスワード変更

### 2. 記事管理
- 記事の作成・編集・削除（自分の記事のみ）
- 記事のステータス管理（下書き/公開）
- カテゴリーの設定
- タグの追加・削除
- スラッグ（URL用）の自動生成
- 抜粋の自動生成

### 3. コメント機能
- コメントの投稿（ログインユーザーとゲスト）
- コメントの承認/却下（記事作成者のみ）
- コメントの削除（記事作成者とコメント投稿者）

### 4. カテゴリー・タグ管理
- カテゴリーのCRUD操作
- タグのCRUD操作
- カテゴリー別記事一覧
- タグ別記事一覧

### 5. 検索機能
- タイトル・本文での全文検索
- カテゴリー・タグでの絞り込み
- 複合検索（AND検索）

## セキュリティ要件

### 1. 認証・認可
- セッションベースの認証
- パスワードハッシュ化（password_hash）
- セッション固定化攻撃対策（session_regenerate_id）
- ロールベースのアクセス制御

### 2. XSS対策
- すべての出力でhtmlspecialcharsによるエスケープ
- ヘルパー関数h()の使用

### 3. CSRF対策
- すべてのフォームにCSRFトークン
- トークンの検証ミドルウェア

### 4. SQLインジェクション対策
- プリペアドステートメントの使用
- 入力バリデーション

### 5. その他
- セッションタイムアウト
- 入力サニタイゼーション
- セキュアなCookie設定

## ディレクトリ構造

```
src/Phase4/BlogSystem/
├── Entities/
│   ├── User.php
│   ├── Post.php
│   ├── Comment.php
│   ├── Category.php
│   └── Tag.php
├── Repositories/
│   ├── UserRepository.php
│   ├── PostRepository.php
│   ├── CommentRepository.php
│   ├── CategoryRepository.php
│   └── TagRepository.php
├── Services/
│   ├── AuthService.php
│   ├── PostService.php
│   ├── CommentService.php
│   └── SearchService.php
├── Controllers/
│   ├── AuthController.php
│   ├── PostController.php
│   ├── CommentController.php
│   └── CategoryController.php
├── Views/
│   ├── layout.php
│   ├── auth/
│   ├── posts/
│   ├── comments/
│   └── categories/
├── Middleware/
│   ├── AuthMiddleware.php
│   └── CsrfMiddleware.php
├── Router.php
├── Database.php
└── helpers.php
```

## 主要クラス

### エンティティ
- `User`: ユーザー情報
- `Post`: 記事情報
- `Comment`: コメント情報
- `Category`: カテゴリー情報
- `Tag`: タグ情報

### リポジトリ
- `UserRepository`: ユーザーデータアクセス
- `PostRepository`: 記事データアクセス（JOIN操作含む）
- `CommentRepository`: コメントデータアクセス
- `CategoryRepository`: カテゴリーデータアクセス
- `TagRepository`: タグデータアクセス

### サービス
- `AuthService`: 認証処理（登録、ログイン、セッション管理）
- `PostService`: 記事管理ビジネスロジック
- `CommentService`: コメント管理ビジネスロジック
- `SearchService`: 検索ロジック

### コントローラー
- `AuthController`: 認証関連（登録、ログイン、ログアウト）
- `PostController`: 記事CRUD操作
- `CommentController`: コメント操作
- `CategoryController`: カテゴリー・タグ管理

### その他
- `Router`: URLルーティング
- `Database`: データベース接続
- `helpers.php`: ヘルパー関数（h、csrf_token、redirect など）

## 学習ポイント

1. **MVCパターン**: 関心の分離とコードの整理
2. **認証・認可**: セッション管理とアクセス制御
3. **リレーショナルDB**: JOIN、多対多、外部キー制約
4. **セキュリティ**: OWASP Top 10への対策
5. **検索機能**: 全文検索とフィルタリング
6. **ルーティング**: URLパターンとRESTful設計
7. **テンプレートエンジン**: シンプルなPHPテンプレート
