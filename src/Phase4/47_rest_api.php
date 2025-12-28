<?php

declare(strict_types=1);

/**
 * Phase 4.6: REST API実装
 *
 * 【学習内容】
 * - RESTful APIの設計原則
 * - JWT認証の実装
 * - レート制限の実装
 * - CORS対応
 * - エラーハンドリング
 * - ページネーション
 *
 * 【アーキテクチャ】
 * 1. Entity層: User, Product, Order, OrderItem
 * 2. Repository層: CRUD操作とデータアクセス
 * 3. Service層: JWT認証サービス
 * 4. Middleware層: CORS, 認証, レート制限
 * 5. Controller層: APIエンドポイント
 * 6. Router層: URIルーティング
 *
 * 【実装機能】
 * - ユーザー管理API（登録、ログイン、プロフィール）
 * - 商品管理API（CRUD、検索、カテゴリー）
 * - 注文管理API（作成、ステータス更新、履歴）
 * - JWT認証（アクセストークン、リフレッシュトークン）
 * - レート制限（100リクエスト/時間）
 * - CORS対応
 * - 統一的なエラーハンドリング
 *
 * 【セキュリティ】
 * - パスワードハッシュ化（ARGON2ID）
 * - JWT署名検証
 * - レート制限
 * - CORS設定
 * - SQLインジェクション対策（プリペアドステートメント）
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Phase4\RestApi\Database;
use Phase4\RestApi\Repositories\{UserRepository, ProductRepository, OrderRepository};
use Phase4\RestApi\Entities\{User, Product, Order, OrderItem, OrderStatus};
use Phase4\RestApi\Services\JwtService;
use Phase4\RestApi\Helpers\{ApiResponse, Request};
use Phase4\RestApi\Middleware\{CorsMiddleware, AuthMiddleware, RateLimitMiddleware};
use Phase4\RestApi\Router;

// エラーハンドリング
set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage());
    ApiResponse::serverError('サーバー内部エラーが発生しました');
});

// セキュリティヘッダーを設定
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// ミドルウェアを実行
CorsMiddleware::handle();
RateLimitMiddleware::handle();

// データベース接続とリポジトリの初期化
$pdo = Database::getConnection();
$userRepository = new UserRepository($pdo);
$productRepository = new ProductRepository($pdo);
$orderRepository = new OrderRepository($pdo);
$jwtService = new JwtService();

// ルーターの初期化
$router = new Router();

// ========================================
// 認証API
// ========================================

// ユーザー登録
$router->post('/api/v1/auth/register', function () use ($userRepository, $jwtService) {
    $data = Request::json();

    // バリデーション
    $errors = [];
    if (!isset($data['username']) || strlen($data['username']) < 3) {
        $errors['username'][] = 'ユーザー名は3文字以上で指定してください';
    }
    if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'][] = '有効なメールアドレスを指定してください';
    }
    if (!isset($data['password']) || strlen($data['password']) < 8) {
        $errors['password'][] = 'パスワードは8文字以上で指定してください';
    }
    if (!isset($data['full_name']) || strlen($data['full_name']) === 0) {
        $errors['full_name'][] = 'フルネームを指定してください';
    }

    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }

    // 重複チェック
    if ($userRepository->existsByUsername($data['username'])) {
        ApiResponse::error('RESOURCE_ALREADY_EXISTS', 'このユーザー名は既に使用されています', 400);
    }
    if ($userRepository->existsByEmail($data['email'])) {
        ApiResponse::error('RESOURCE_ALREADY_EXISTS', 'このメールアドレスは既に使用されています', 400);
    }

    try {
        // ユーザー作成
        $user = User::create(
            $data['username'],
            $data['email'],
            $data['password'],
            $data['full_name']
        );
        $user = $userRepository->create($user);

        // トークン生成
        $accessToken = $jwtService->generateAccessToken(
            $user->getId(),
            $user->getUsername(),
            $user->isAdmin()
        );
        $refreshToken = $jwtService->generateRefreshToken($user->getId());

        ApiResponse::success([
            'user' => $user->toArray(),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ], 'ユーザー登録が完了しました', 201);
    } catch (\Exception $e) {
        ApiResponse::error('REGISTRATION_FAILED', $e->getMessage(), 400);
    }
});

// ログイン
$router->post('/api/v1/auth/login', function () use ($userRepository, $jwtService) {
    $data = Request::json();

    if (!isset($data['username']) || !isset($data['password'])) {
        ApiResponse::validationError([
            'username' => ['ユーザー名を指定してください'],
            'password' => ['パスワードを指定してください'],
        ]);
    }

    $user = $userRepository->findByUsername($data['username']);
    if ($user === null || !$user->verifyPassword($data['password'])) {
        ApiResponse::unauthorized('ユーザー名またはパスワードが正しくありません');
    }

    // トークン生成
    $accessToken = $jwtService->generateAccessToken(
        $user->getId(),
        $user->getUsername(),
        $user->isAdmin()
    );
    $refreshToken = $jwtService->generateRefreshToken($user->getId());

    ApiResponse::success([
        'user' => $user->toArray(),
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
    ], 'ログインしました');
});

// トークンリフレッシュ
$router->post('/api/v1/auth/refresh', function () use ($jwtService, $userRepository) {
    $data = Request::json();

    if (!isset($data['refresh_token'])) {
        ApiResponse::validationError(['refresh_token' => ['リフレッシュトークンを指定してください']]);
    }

    try {
        $payload = $jwtService->decode($data['refresh_token']);

        // トークンタイプをチェック
        if (($payload['type'] ?? null) !== 'refresh') {
            ApiResponse::unauthorized('無効なトークンタイプです');
        }

        $userId = $payload['user_id'] ?? null;
        if ($userId === null) {
            ApiResponse::unauthorized('無効なトークンです');
        }

        $user = $userRepository->findById($userId);
        if ($user === null) {
            ApiResponse::unauthorized('ユーザーが見つかりません');
        }

        // 新しいアクセストークンを生成
        $accessToken = $jwtService->generateAccessToken(
            $user->getId(),
            $user->getUsername(),
            $user->isAdmin()
        );

        ApiResponse::success([
            'access_token' => $accessToken,
        ], 'トークンをリフレッシュしました');
    } catch (\Exception $e) {
        ApiResponse::unauthorized($e->getMessage());
    }
});

// 現在のユーザー情報取得
$router->get('/api/v1/auth/me', function () use ($jwtService, $userRepository) {
    $payload = AuthMiddleware::handle($jwtService);
    $user = $userRepository->findById($payload['user_id']);

    if ($user === null) {
        ApiResponse::notFound('ユーザーが見つかりません');
    }

    ApiResponse::success($user->toArray());
});

// ========================================
// 商品API（公開）
// ========================================

// 商品一覧取得
$router->get('/api/v1/products', function () use ($productRepository) {
    $page = (int) (Request::query('page') ?? 1);
    $perPage = (int) (Request::query('per_page') ?? 20);
    $category = Request::query('category');
    $search = Request::query('search');

    $offset = ($page - 1) * $perPage;

    if ($category !== null) {
        $products = $productRepository->findByCategory($category, $perPage, $offset);
        $total = count($productRepository->findByCategory($category, 1000, 0));
    } elseif ($search !== null) {
        $products = $productRepository->search($search, $perPage, $offset);
        $total = count($productRepository->search($search, 1000, 0));
    } else {
        $products = $productRepository->findAll($perPage, $offset, true);
        $total = $productRepository->count(true);
    }

    $data = array_map(fn(Product $p) => $p->toArray(), $products);
    ApiResponse::paginated($data, $page, $perPage, $total);
});

// 商品詳細取得
$router->get('/api/v1/products/{id}', function (string $id) use ($productRepository) {
    $product = $productRepository->findById((int) $id);

    if ($product === null || !$product->isActive()) {
        ApiResponse::notFound('商品が見つかりません');
    }

    ApiResponse::success($product->toArray());
});

// 商品作成（管理者のみ）
$router->post('/api/v1/products', function () use ($jwtService, $productRepository) {
    AuthMiddleware::handle($jwtService, true); // 管理者権限が必要

    $data = Request::json();

    // バリデーション（簡略化）
    $errors = [];
    if (!isset($data['name']) || strlen($data['name']) === 0) {
        $errors['name'][] = '商品名を指定してください';
    }
    if (!isset($data['price']) || $data['price'] < 0) {
        $errors['price'][] = '価格は0以上で指定してください';
    }

    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }

    try {
        $product = Product::create(
            $data['name'],
            $data['description'] ?? '',
            (float) $data['price'],
            (int) ($data['stock'] ?? 0),
            $data['category'] ?? 'その他',
            $data['image_url'] ?? null
        );
        $product = $productRepository->create($product);

        ApiResponse::success($product->toArray(), '商品を作成しました', 201);
    } catch (\Exception $e) {
        ApiResponse::error('CREATION_FAILED', $e->getMessage(), 400);
    }
});

// 商品更新（管理者のみ）
$router->put('/api/v1/products/{id}', function (string $id) use ($jwtService, $productRepository) {
    AuthMiddleware::handle($jwtService, true);

    $product = $productRepository->findById((int) $id);
    if ($product === null) {
        ApiResponse::notFound('商品が見つかりません');
    }

    $data = Request::json();

    try {
        $product->update(
            $data['name'] ?? null,
            $data['description'] ?? null,
            isset($data['price']) ? (float) $data['price'] : null,
            isset($data['stock']) ? (int) $data['stock'] : null,
            $data['category'] ?? null,
            $data['image_url'] ?? null
        );
        $productRepository->update($product);

        ApiResponse::success($product->toArray(), '商品を更新しました');
    } catch (\Exception $e) {
        ApiResponse::error('UPDATE_FAILED', $e->getMessage(), 400);
    }
});

// 商品削除（管理者のみ）
$router->delete('/api/v1/products/{id}', function (string $id) use ($jwtService, $productRepository) {
    AuthMiddleware::handle($jwtService, true);

    $deleted = $productRepository->delete((int) $id);

    if (!$deleted) {
        ApiResponse::notFound('商品が見つかりません');
    }

    ApiResponse::success(null, '商品を削除しました', 204);
});

// ========================================
// 注文API（認証が必要）
// ========================================

// 注文一覧取得
$router->get('/api/v1/orders', function () use ($jwtService, $orderRepository) {
    $payload = AuthMiddleware::handle($jwtService);

    $page = (int) (Request::query('page') ?? 1);
    $perPage = (int) (Request::query('per_page') ?? 20);
    $offset = ($page - 1) * $perPage;

    // 管理者はすべての注文、一般ユーザーは自分の注文のみ
    if ($payload['is_admin']) {
        $orders = $orderRepository->findAll($perPage, $offset);
        $total = $orderRepository->count();
    } else {
        $orders = $orderRepository->findByUserId($payload['user_id'], $perPage, $offset);
        $total = $orderRepository->count($payload['user_id']);
    }

    $data = array_map(fn(Order $o) => $o->toArray(true), $orders);
    ApiResponse::paginated($data, $page, $perPage, $total);
});

// 注文詳細取得
$router->get('/api/v1/orders/{id}', function (string $id) use ($jwtService, $orderRepository) {
    $payload = AuthMiddleware::handle($jwtService);

    $order = $orderRepository->findById((int) $id);

    if ($order === null) {
        ApiResponse::notFound('注文が見つかりません');
    }

    // 権限チェック（自分の注文または管理者のみ）
    if ($order->getUserId() !== $payload['user_id'] && !$payload['is_admin']) {
        ApiResponse::forbidden('この注文にアクセスする権限がありません');
    }

    ApiResponse::success($order->toArray(true));
});

// 注文作成
$router->post('/api/v1/orders', function () use ($jwtService, $orderRepository, $productRepository) {
    $payload = AuthMiddleware::handle($jwtService);
    $data = Request::json();

    // バリデーション
    if (!isset($data['shipping_address']) || !isset($data['items']) || empty($data['items'])) {
        ApiResponse::validationError([
            'shipping_address' => ['配送先住所を指定してください'],
            'items' => ['注文アイテムを指定してください'],
        ]);
    }

    try {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        // 注文アイテムを作成
        $orderItems = [];
        foreach ($data['items'] as $item) {
            $product = $productRepository->findById($item['product_id']);

            if ($product === null) {
                $pdo->rollBack();
                ApiResponse::notFound('商品が見つかりません: ID=' . $item['product_id']);
            }

            if (!$product->hasStock($item['quantity'])) {
                $pdo->rollBack();
                ApiResponse::error('INSUFFICIENT_STOCK', '在庫が不足しています: ' . $product->getName(), 400);
            }

            // 在庫を減らす
            $product->reduceStock($item['quantity']);
            $productRepository->update($product);

            $orderItems[] = OrderItem::fromProduct($product, $item['quantity']);
        }

        // 注文を作成
        $order = Order::create($payload['user_id'], $data['shipping_address'], $orderItems);
        $order = $orderRepository->create($order);

        $pdo->commit();

        ApiResponse::success($order->toArray(true), '注文を作成しました', 201);
    } catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        ApiResponse::error('ORDER_FAILED', $e->getMessage(), 400);
    }
});

// 注文ステータス更新
$router->put('/api/v1/orders/{id}', function (string $id) use ($jwtService, $orderRepository) {
    $payload = AuthMiddleware::handle($jwtService);
    $data = Request::json();

    $order = $orderRepository->findById((int) $id);

    if ($order === null) {
        ApiResponse::notFound('注文が見つかりません');
    }

    // 権限チェック（自分の注文または管理者のみ）
    if ($order->getUserId() !== $payload['user_id'] && !$payload['is_admin']) {
        ApiResponse::forbidden('この注文にアクセスする権限がありません');
    }

    if (!isset($data['status'])) {
        ApiResponse::validationError(['status' => ['ステータスを指定してください']]);
    }

    try {
        $newStatus = OrderStatus::from($data['status']);
        $order->updateStatus($newStatus);
        $orderRepository->update($order);

        ApiResponse::success($order->toArray(true), '注文ステータスを更新しました');
    } catch (\Exception $e) {
        ApiResponse::error('UPDATE_FAILED', $e->getMessage(), 400);
    }
});

// ルーターを実行
$router->dispatch();
