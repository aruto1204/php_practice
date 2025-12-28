# Phase 4.6: REST API実装 - 設計ドキュメント

## 📌 プロジェクト概要

本プロジェクトは、RESTful APIの設計原則に基づいた、完全なECサイト向けREST APIを実装します。
ユーザー管理、商品管理、注文管理の3つのリソースを中心に、JWT認証、レート制限、包括的なエラーハンドリングを備えた本格的なAPIシステムです。

## 🎯 学習目標

- RESTful APIの設計原則を理解する
- JWT認証の実装方法を習得する
- APIセキュリティのベストプラクティスを学ぶ
- レート制限の実装方法を理解する
- 包括的なエラーハンドリングを実装する
- OpenAPI（Swagger）によるドキュメント作成を学ぶ

## 🏗️ アーキテクチャ設計

```
┌──────────────────────────────────────────────────────────┐
│                     APIエンドポイント                      │
├──────────────────────────────────────────────────────────┤
│                     ミドルウェア層                         │
│  • CORS          • 認証(JWT)     • レート制限             │
│  • ロギング      • エラーハンドラ                         │
├──────────────────────────────────────────────────────────┤
│                     コントローラー層                       │
│  • AuthController    • UserController                    │
│  • ProductController • OrderController                   │
├──────────────────────────────────────────────────────────┤
│                     サービス層                            │
│  • AuthService       • UserService                       │
│  • ProductService    • OrderService                      │
├──────────────────────────────────────────────────────────┤
│                     リポジトリ層                          │
│  • UserRepository    • ProductRepository                 │
│  • OrderRepository   • OrderItemRepository               │
├──────────────────────────────────────────────────────────┤
│                     エンティティ層                        │
│  • User             • Product                            │
│  • Order            • OrderItem                          │
├──────────────────────────────────────────────────────────┤
│                     データベース層                         │
│  SQLite（users, products, orders, order_items）          │
└──────────────────────────────────────────────────────────┘
```

## 📊 データベース設計

### テーブル: users
| カラム名 | 型 | 制約 | 説明 |
|---------|---|------|------|
| id | INTEGER | PRIMARY KEY | ユーザーID |
| username | TEXT | NOT NULL, UNIQUE | ユーザー名 |
| email | TEXT | NOT NULL, UNIQUE | メールアドレス |
| password_hash | TEXT | NOT NULL | パスワードハッシュ |
| full_name | TEXT | NOT NULL | フルネーム |
| is_admin | INTEGER | DEFAULT 0 | 管理者フラグ(0/1) |
| created_at | TEXT | NOT NULL | 作成日時 |
| updated_at | TEXT | NOT NULL | 更新日時 |

### テーブル: products
| カラム名 | 型 | 制約 | 説明 |
|---------|---|------|------|
| id | INTEGER | PRIMARY KEY | 商品ID |
| name | TEXT | NOT NULL | 商品名 |
| description | TEXT | NOT NULL | 商品説明 |
| price | REAL | NOT NULL | 価格 |
| stock | INTEGER | NOT NULL, DEFAULT 0 | 在庫数 |
| category | TEXT | NOT NULL | カテゴリー |
| image_url | TEXT | NULL | 画像URL |
| is_active | INTEGER | DEFAULT 1 | 有効フラグ(0/1) |
| created_at | TEXT | NOT NULL | 作成日時 |
| updated_at | TEXT | NOT NULL | 更新日時 |

### テーブル: orders
| カラム名 | 型 | 制約 | 説明 |
|---------|---|------|------|
| id | INTEGER | PRIMARY KEY | 注文ID |
| user_id | INTEGER | NOT NULL, FK(users) | ユーザーID |
| status | TEXT | NOT NULL | ステータス(pending/processing/completed/cancelled) |
| total_amount | REAL | NOT NULL | 合計金額 |
| shipping_address | TEXT | NOT NULL | 配送先住所 |
| created_at | TEXT | NOT NULL | 作成日時 |
| updated_at | TEXT | NOT NULL | 更新日時 |

### テーブル: order_items
| カラム名 | 型 | 制約 | 説明 |
|---------|---|------|------|
| id | INTEGER | PRIMARY KEY | 注文アイテムID |
| order_id | INTEGER | NOT NULL, FK(orders) | 注文ID |
| product_id | INTEGER | NOT NULL, FK(products) | 商品ID |
| quantity | INTEGER | NOT NULL | 数量 |
| price | REAL | NOT NULL | 単価 |
| subtotal | REAL | NOT NULL | 小計 |

## 🔌 APIエンドポイント仕様

### 認証API

| メソッド | エンドポイント | 説明 | 認証 |
|---------|--------------|------|------|
| POST | /api/v1/auth/register | ユーザー登録 | 不要 |
| POST | /api/v1/auth/login | ログイン | 不要 |
| POST | /api/v1/auth/refresh | トークンリフレッシュ | 不要 |
| POST | /api/v1/auth/logout | ログアウト | 必要 |
| GET | /api/v1/auth/me | 現在のユーザー情報取得 | 必要 |

### ユーザー管理API

| メソッド | エンドポイント | 説明 | 認証 |
|---------|--------------|------|------|
| GET | /api/v1/users | ユーザー一覧取得 | 必要(管理者) |
| GET | /api/v1/users/{id} | ユーザー詳細取得 | 必要 |
| PUT | /api/v1/users/{id} | ユーザー更新 | 必要 |
| DELETE | /api/v1/users/{id} | ユーザー削除 | 必要(管理者) |

### 商品管理API

| メソッド | エンドポイント | 説明 | 認証 |
|---------|--------------|------|------|
| GET | /api/v1/products | 商品一覧取得 | 不要 |
| GET | /api/v1/products/{id} | 商品詳細取得 | 不要 |
| POST | /api/v1/products | 商品作成 | 必要(管理者) |
| PUT | /api/v1/products/{id} | 商品更新 | 必要(管理者) |
| DELETE | /api/v1/products/{id} | 商品削除 | 必要(管理者) |

### 注文管理API

| メソッド | エンドポイント | 説明 | 認証 |
|---------|--------------|------|------|
| GET | /api/v1/orders | 注文一覧取得 | 必要 |
| GET | /api/v1/orders/{id} | 注文詳細取得 | 必要 |
| POST | /api/v1/orders | 注文作成 | 必要 |
| PUT | /api/v1/orders/{id} | 注文ステータス更新 | 必要 |
| DELETE | /api/v1/orders/{id} | 注文キャンセル | 必要 |

## 🔐 認証とセキュリティ

### JWT（JSON Web Token）

- **アクセストークン**: 有効期限15分
- **リフレッシュトークン**: 有効期限7日間
- **アルゴリズム**: HS256 (HMAC-SHA256)
- **ペイロード**: user_id, username, is_admin, exp, iat

### レート制限

- **デフォルト**: 100リクエスト/時間
- **認証**: 60リクエスト/分
- **実装**: IPアドレスベースまたはユーザーIDベース
- **ヘッダー**:
  - `X-RateLimit-Limit`: リミット値
  - `X-RateLimit-Remaining`: 残りリクエスト数
  - `X-RateLimit-Reset`: リセット時刻

### セキュリティヘッダー

```php
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Content-Security-Policy: default-src 'self'
```

## 📝 レスポンス形式

### 成功レスポンス

```json
{
  "success": true,
  "data": {
    // リソースデータ
  },
  "message": "Success message",
  "meta": {
    "timestamp": "2025-12-28T10:00:00Z",
    "api_version": "v1"
  }
}
```

### エラーレスポンス

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "email": ["Email is required", "Email must be valid"]
    }
  },
  "meta": {
    "timestamp": "2025-12-28T10:00:00Z",
    "api_version": "v1"
  }
}
```

### ページネーション

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5,
    "timestamp": "2025-12-28T10:00:00Z"
  }
}
```

## 🔢 HTTPステータスコード

| コード | 説明 | 使用例 |
|-------|------|--------|
| 200 | OK | リソースの取得・更新成功 |
| 201 | Created | リソースの作成成功 |
| 204 | No Content | リソースの削除成功 |
| 400 | Bad Request | リクエストの形式が不正 |
| 401 | Unauthorized | 認証が必要 |
| 403 | Forbidden | 権限がない |
| 404 | Not Found | リソースが存在しない |
| 422 | Unprocessable Entity | バリデーションエラー |
| 429 | Too Many Requests | レート制限超過 |
| 500 | Internal Server Error | サーバーエラー |

## 📖 エラーコード一覧

| コード | 説明 |
|-------|------|
| VALIDATION_ERROR | バリデーションエラー |
| AUTHENTICATION_FAILED | 認証失敗 |
| AUTHORIZATION_FAILED | 認可失敗 |
| RESOURCE_NOT_FOUND | リソースが見つからない |
| RESOURCE_ALREADY_EXISTS | リソースが既に存在する |
| RATE_LIMIT_EXCEEDED | レート制限超過 |
| INSUFFICIENT_STOCK | 在庫不足 |
| INVALID_ORDER_STATUS | 無効な注文ステータス |
| INTERNAL_SERVER_ERROR | サーバー内部エラー |

## 🧪 テスト戦略

### ユニットテスト
- エンティティのバリデーション
- サービス層のビジネスロジック
- リポジトリ層のCRUD操作

### 統合テスト
- APIエンドポイントの動作
- 認証・認可フロー
- エラーハンドリング

### テストカバレッジ
- 目標: 80%以上

## 🚀 実装の進め方

1. ✅ エンティティ層の実装（User、Product、Order、OrderItem）
2. ✅ リポジトリ層の実装（各エンティティのCRUD操作）
3. ✅ JWT認証サービスの実装
4. ✅ ミドルウェアの実装（CORS、認証、レート制限）
5. ✅ コントローラーの実装（各リソースのエンドポイント）
6. ✅ ルーティングの実装
7. ✅ エラーハンドリングの実装
8. ✅ OpenAPIドキュメントの作成
9. ✅ テストコードの作成

## 📚 参考資料

- [REST API Design Best Practices](https://restfulapi.net/)
- [JWT Handbook](https://auth0.com/resources/ebooks/jwt-handbook)
- [OpenAPI Specification](https://swagger.io/specification/)
- [PHP PSR-7 HTTP Message Interface](https://www.php-fig.org/psr/psr-7/)

---

**作成日**: 2025年12月28日
**最終更新日**: 2025年12月28日
