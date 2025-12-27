<?php

declare(strict_types=1);

/**
 * Phase 2.6: セッションとクッキー - 実践演習
 *
 * セッションとクッキーを使った実践的なシステムの実装
 */

echo "=== Phase 2.6: セッションとクッキー - 実践演習 ===\n\n";

// セッション開始
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// =====================================
// 演習1: ログインシステム
// =====================================

echo "--- 演習1: ログインシステム ---\n";

/**
 * ユーザー認証例外
 */
class AuthenticationException extends Exception
{
}

/**
 * ユーザークラス
 */
class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role,
    ) {
    }
}

/**
 * 認証サービス
 */
class AuthService
{
    private const SESSION_KEY = 'auth_user';
    private const REMEMBER_ME_COOKIE = 'remember_me';
    private const REMEMBER_ME_LIFETIME = 2592000; // 30日

    /**
     * ユーザーデータベース（デモ用）
     */
    private array $users = [
        'admin' => [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => 'admin',
        ],
        'user1' => [
            'id' => 2,
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => 'user',
        ],
    ];

    /**
     * ログイン
     *
     * @param string $username ユーザー名
     * @param string $password パスワード
     * @param bool $rememberMe ログイン状態を保持
     * @return User ユーザー情報
     * @throws AuthenticationException 認証失敗
     */
    public function login(string $username, string $password, bool $rememberMe = false): User
    {
        // ユーザーの検索
        if (!isset($this->users[$username])) {
            throw new AuthenticationException('ユーザー名またはパスワードが正しくありません');
        }

        $userData = $this->users[$username];

        // パスワードの検証
        if (!password_verify($password, $userData['password'])) {
            throw new AuthenticationException('ユーザー名またはパスワードが正しくありません');
        }

        $user = new User(
            $userData['id'],
            $userData['username'],
            $userData['email'],
            $userData['role']
        );

        // セッションにユーザー情報を保存
        $this->setUserSession($user);

        // セッション固定化攻撃対策
        session_regenerate_id(true);

        // Remember Me
        if ($rememberMe) {
            $this->setRememberMeCookie($user->id);
        }

        return $user;
    }

    /**
     * ログアウト
     *
     * @return void
     */
    public function logout(): void
    {
        // セッションからユーザー情報を削除
        unset($_SESSION[self::SESSION_KEY]);

        // Remember Meクッキーを削除
        $this->deleteRememberMeCookie();

        // セッションを破棄
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * 現在のユーザーを取得
     *
     * @return User|null ユーザー情報
     */
    public function getCurrentUser(): ?User
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            $data = $_SESSION[self::SESSION_KEY];
            return new User(
                $data['id'],
                $data['username'],
                $data['email'],
                $data['role']
            );
        }

        // Remember Meクッキーをチェック
        return $this->checkRememberMe();
    }

    /**
     * ログイン済みかチェック
     *
     * @return bool ログイン済みの場合true
     */
    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * 権限チェック
     *
     * @param string $role 必要な権限
     * @return bool 権限がある場合true
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->role === $role;
    }

    /**
     * セッションにユーザー情報を保存
     *
     * @param User $user ユーザー
     * @return void
     */
    private function setUserSession(User $user): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    /**
     * Remember Meクッキーを設定
     *
     * @param int $userId ユーザーID
     * @return void
     */
    private function setRememberMeCookie(int $userId): void
    {
        $token = bin2hex(random_bytes(32));

        // 実際のアプリケーションでは、トークンをデータベースに保存
        // - トークンのハッシュ値
        // - ユーザーID
        // - 有効期限

        // デモ用に $_COOKIE に設定
        $_COOKIE[self::REMEMBER_ME_COOKIE] = base64_encode(json_encode([
            'user_id' => $userId,
            'token' => $token,
        ]));
    }

    /**
     * Remember Meクッキーを削除
     *
     * @return void
     */
    private function deleteRememberMeCookie(): void
    {
        unset($_COOKIE[self::REMEMBER_ME_COOKIE]);
    }

    /**
     * Remember Meクッキーをチェック
     *
     * @return User|null ユーザー情報
     */
    private function checkRememberMe(): ?User
    {
        if (!isset($_COOKIE[self::REMEMBER_ME_COOKIE])) {
            return null;
        }

        $data = json_decode(base64_decode($_COOKIE[self::REMEMBER_ME_COOKIE]), true);

        if (!is_array($data) || !isset($data['user_id'])) {
            return null;
        }

        // 実際のアプリケーションでは、データベースからトークンを検証

        // デモ用にユーザーを検索
        foreach ($this->users as $userData) {
            if ($userData['id'] === $data['user_id']) {
                $user = new User(
                    $userData['id'],
                    $userData['username'],
                    $userData['email'],
                    $userData['role']
                );

                // セッションにユーザー情報を保存
                $this->setUserSession($user);

                return $user;
            }
        }

        return null;
    }
}

// 使用例
$auth = new AuthService();

try {
    // ログイン（Remember Me有効）
    $user = $auth->login('admin', 'password', true);
    echo "ログイン成功: {$user->username} ({$user->role})\n";

    // ログイン状態の確認
    if ($auth->isLoggedIn()) {
        $currentUser = $auth->getCurrentUser();
        echo "現在のユーザー: {$currentUser->username}\n";
    }

    // 権限チェック
    if ($auth->hasRole('admin')) {
        echo "管理者権限があります\n";
    }

    // ログアウト
    // $auth->logout();
    // echo "ログアウトしました\n";
} catch (AuthenticationException $e) {
    echo "認証エラー: {$e->getMessage()}\n";
}

echo "\n";

// =====================================
// 演習2: ショッピングカートシステム
// =====================================

echo "--- 演習2: ショッピングカートシステム ---\n";

/**
 * 商品クラス
 */
class Product
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
    ) {
    }
}

/**
 * カート商品クラス
 */
class CartItem
{
    public function __construct(
        public readonly Product $product,
        public int $quantity,
    ) {
    }

    /**
     * 小計を計算
     *
     * @return float 小計
     */
    public function getSubtotal(): float
    {
        return $this->product->price * $this->quantity;
    }
}

/**
 * ショッピングカートサービス
 */
class ShoppingCartService
{
    private const SESSION_KEY = 'shopping_cart';
    private const COOKIE_KEY = 'cart_data';
    private const COOKIE_LIFETIME = 604800; // 7日

    /**
     * カートに商品を追加
     *
     * @param Product $product 商品
     * @param int $quantity 数量
     * @return void
     */
    public function addItem(Product $product, int $quantity = 1): void
    {
        $cart = $this->getCart();

        // 既に同じ商品がある場合は数量を追加
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        $this->saveCart($cart);
    }

    /**
     * カート商品の数量を更新
     *
     * @param int $productId 商品ID
     * @param int $quantity 数量
     * @return void
     */
    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
            } else {
                unset($cart[$productId]);
            }
            $this->saveCart($cart);
        }
    }

    /**
     * カートから商品を削除
     *
     * @param int $productId 商品ID
     * @return void
     */
    public function removeItem(int $productId): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }
    }

    /**
     * カートをクリア
     *
     * @return void
     */
    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        unset($_COOKIE[self::COOKIE_KEY]);
    }

    /**
     * カート商品一覧を取得
     *
     * @return CartItem[] カート商品配列
     */
    public function getItems(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $data) {
            $product = new Product($data['id'], $data['name'], $data['price']);
            $items[] = new CartItem($product, $data['quantity']);
        }

        return $items;
    }

    /**
     * カート商品数を取得
     *
     * @return int 商品数
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * カート合計金額を取得
     *
     * @return float 合計金額
     */
    public function getTotal(): float
    {
        $items = $this->getItems();
        return array_sum(array_map(fn($item) => $item->getSubtotal(), $items));
    }

    /**
     * カートデータを取得
     *
     * @return array カートデータ
     */
    private function getCart(): array
    {
        // セッションから取得
        if (isset($_SESSION[self::SESSION_KEY])) {
            return $_SESSION[self::SESSION_KEY];
        }

        // クッキーから取得（非ログイン時）
        if (isset($_COOKIE[self::COOKIE_KEY])) {
            $cart = json_decode($_COOKIE[self::COOKIE_KEY], true);
            if (is_array($cart)) {
                return $cart;
            }
        }

        return [];
    }

    /**
     * カートデータを保存
     *
     * @param array $cart カートデータ
     * @return void
     */
    private function saveCart(array $cart): void
    {
        // セッションに保存
        $_SESSION[self::SESSION_KEY] = $cart;

        // クッキーにも保存（非ログイン時のため）
        $_COOKIE[self::COOKIE_KEY] = json_encode($cart, JSON_UNESCAPED_UNICODE);
    }
}

// 使用例
$cart = new ShoppingCartService();

// 商品をカートに追加
$product1 = new Product(1, 'ノートPC', 150000);
$product2 = new Product(2, 'マウス', 3000);
$product3 = new Product(3, 'キーボード', 8000);

$cart->addItem($product1, 1);
$cart->addItem($product2, 2);
$cart->addItem($product3, 1);

echo "カート商品数: {$cart->getItemCount()}個\n";
echo "カート合計: " . number_format($cart->getTotal()) . "円\n";

echo "\nカート内容:\n";
foreach ($cart->getItems() as $item) {
    echo sprintf(
        "  - %s x%d: %s円\n",
        $item->product->name,
        $item->quantity,
        number_format($item->getSubtotal())
    );
}

// 数量更新
$cart->updateQuantity(2, 3);
echo "\nマウスの数量を3個に更新しました\n";
echo "カート合計: " . number_format($cart->getTotal()) . "円\n";

// 商品削除
$cart->removeItem(3);
echo "\nキーボードを削除しました\n";
echo "カート商品数: {$cart->getItemCount()}個\n";

echo "\n";

// =====================================
// 演習3: ユーザー設定管理システム
// =====================================

echo "--- 演習3: ユーザー設定管理システム ---\n";

/**
 * ユーザー設定サービス
 */
class UserPreferencesService
{
    private const SESSION_KEY = 'user_preferences';
    private const COOKIE_KEY = 'preferences';
    private const COOKIE_LIFETIME = 31536000; // 1年

    /**
     * デフォルト設定
     */
    private array $defaults = [
        'theme' => 'light',
        'language' => 'ja',
        'timezone' => 'Asia/Tokyo',
        'items_per_page' => 20,
        'notifications' => [
            'email' => true,
            'push' => false,
            'sms' => false,
        ],
        'display' => [
            'show_images' => true,
            'compact_view' => false,
        ],
    ];

    /**
     * 設定を取得
     *
     * @param string|null $key キー（nullの場合は全設定）
     * @param mixed $default デフォルト値
     * @return mixed 設定値
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        $preferences = $this->load();

        if ($key === null) {
            return $preferences;
        }

        // ドット記法をサポート（例: 'notifications.email'）
        $keys = explode('.', $key);
        $value = $preferences;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default ?? $this->getDefault($key);
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * 設定を更新
     *
     * @param string $key キー
     * @param mixed $value 値
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $preferences = $this->load();

        // ドット記法をサポート
        $keys = explode('.', $key);
        $current = &$preferences;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }

        $this->save($preferences);
    }

    /**
     * 複数の設定を一括更新
     *
     * @param array $preferences 設定配列
     * @return void
     */
    public function setMultiple(array $preferences): void
    {
        $current = $this->load();
        $merged = array_replace_recursive($current, $preferences);
        $this->save($merged);
    }

    /**
     * 設定をリセット
     *
     * @return void
     */
    public function reset(): void
    {
        $this->save($this->defaults);
    }

    /**
     * 設定を読み込み
     *
     * @return array 設定配列
     */
    private function load(): array
    {
        // セッションから読み込み
        if (isset($_SESSION[self::SESSION_KEY])) {
            return $_SESSION[self::SESSION_KEY];
        }

        // クッキーから読み込み
        if (isset($_COOKIE[self::COOKIE_KEY])) {
            $preferences = json_decode($_COOKIE[self::COOKIE_KEY], true);
            if (is_array($preferences)) {
                // セッションにもキャッシュ
                $_SESSION[self::SESSION_KEY] = $preferences;
                return $preferences;
            }
        }

        // デフォルト設定
        return $this->defaults;
    }

    /**
     * 設定を保存
     *
     * @param array $preferences 設定配列
     * @return void
     */
    private function save(array $preferences): void
    {
        // セッションに保存
        $_SESSION[self::SESSION_KEY] = $preferences;

        // クッキーにも保存（永続化）
        $_COOKIE[self::COOKIE_KEY] = json_encode($preferences, JSON_UNESCAPED_UNICODE);
    }

    /**
     * デフォルト値を取得
     *
     * @param string $key キー
     * @return mixed デフォルト値
     */
    private function getDefault(string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $this->defaults;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

// 使用例
$prefs = new UserPreferencesService();

// 設定の取得
echo "現在のテーマ: {$prefs->get('theme')}\n";
echo "言語: {$prefs->get('language')}\n";
echo "タイムゾーン: {$prefs->get('timezone')}\n";
echo "メール通知: " . ($prefs->get('notifications.email') ? '有効' : '無効') . "\n";

// 設定の更新
$prefs->set('theme', 'dark');
$prefs->set('notifications.push', true);
echo "\nテーマをダークモードに変更しました\n";
echo "プッシュ通知を有効にしました\n";

// 一括更新
$prefs->setMultiple([
    'language' => 'en',
    'items_per_page' => 50,
    'display' => [
        'compact_view' => true,
    ],
]);
echo "\n複数の設定を更新しました\n";

// 全設定の表示
echo "\n現在の設定:\n";
print_r($prefs->get());

echo "\n";

// =====================================
// 演習4: CSRFトークン管理
// =====================================

echo "--- 演習4: CSRFトークン管理 ---\n";

/**
 * CSRFトークンサービス
 */
class CsrfTokenService
{
    private const SESSION_KEY = 'csrf_tokens';
    private const TOKEN_LENGTH = 32;
    private const MAX_TOKENS = 10; // 保持する最大トークン数

    /**
     * トークンを生成
     *
     * @param string $formName フォーム名
     * @return string トークン
     */
    public function generateToken(string $formName = 'default'): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // トークンを保存
        $_SESSION[self::SESSION_KEY][$formName] = [
            'token' => $token,
            'timestamp' => time(),
        ];

        // 古いトークンを削除（最大数を超えた場合）
        $this->cleanupOldTokens();

        return $token;
    }

    /**
     * トークンを検証
     *
     * @param string $token トークン
     * @param string $formName フォーム名
     * @param int $maxAge 最大有効時間（秒）
     * @return bool 検証成功の場合true
     */
    public function verifyToken(string $token, string $formName = 'default', int $maxAge = 3600): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY][$formName])) {
            return false;
        }

        $stored = $_SESSION[self::SESSION_KEY][$formName];

        // タイムスタンプチェック
        if (time() - $stored['timestamp'] > $maxAge) {
            unset($_SESSION[self::SESSION_KEY][$formName]);
            return false;
        }

        // トークンチェック（タイミング攻撃対策でhash_equals使用）
        if (!hash_equals($stored['token'], $token)) {
            return false;
        }

        // 使用済みトークンを削除（ワンタイムトークン）
        unset($_SESSION[self::SESSION_KEY][$formName]);

        return true;
    }

    /**
     * トークンフィールドのHTMLを生成
     *
     * @param string $formName フォーム名
     * @return string HTML
     */
    public function getTokenField(string $formName = 'default'): string
    {
        $token = $this->generateToken($formName);
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * 古いトークンを削除
     *
     * @return void
     */
    private function cleanupOldTokens(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $tokens = $_SESSION[self::SESSION_KEY];

        // トークン数が最大を超えた場合、古いものから削除
        if (count($tokens) > self::MAX_TOKENS) {
            uasort($tokens, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
            $_SESSION[self::SESSION_KEY] = array_slice($tokens, -self::MAX_TOKENS, null, true);
        }
    }
}

// 使用例
$csrf = new CsrfTokenService();

// トークン生成
$loginToken = $csrf->generateToken('login_form');
$profileToken = $csrf->generateToken('profile_form');

echo "ログインフォーム用トークン: {$loginToken}\n";
echo "プロフィールフォーム用トークン: {$profileToken}\n";

// フォームHTMLの生成
echo "\nフォームHTML:\n";
echo $csrf->getTokenField('contact_form') . "\n";

// トークン検証
if ($csrf->verifyToken($loginToken, 'login_form')) {
    echo "\nログインフォームのトークンは有効です\n";
} else {
    echo "\nトークン検証失敗（既に使用済み）\n";
}

// 2回目の検証は失敗（ワンタイムトークン）
if (!$csrf->verifyToken($loginToken, 'login_form')) {
    echo "ワンタイムトークンのため、2回目の検証は失敗しました\n";
}

echo "\n";

// =====================================
// まとめ
// =====================================

echo "--- まとめ ---\n";

echo "
【実装したシステム】

1. ログインシステム
   - ユーザー認証
   - Remember Me機能
   - セッション管理
   - 権限チェック

2. ショッピングカートシステム
   - カート商品管理
   - セッション＋クッキーの併用
   - 非ログイン時のカート保持

3. ユーザー設定管理システム
   - 設定の永続化
   - ドット記法のサポート
   - デフォルト値の管理

4. CSRFトークン管理
   - トークン生成・検証
   - ワンタイムトークン
   - タイミング攻撃対策

【学んだこと】

- セッションとクッキーの使い分け
- セキュアな実装方法
- 実務で使える設計パターン
- CSRF、XSS、セッションフィクセーション対策
";

echo "\n=== Phase 2.6: セッションとクッキー - 実践演習完了 ===\n";
