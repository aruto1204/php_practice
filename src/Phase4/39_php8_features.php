<?php

declare(strict_types=1);

/**
 * Phase 4.1: PHP 8 の新機能
 *
 * このファイルでは、PHP 8.0、8.1、8.2、8.3 で導入された新機能について学びます。
 *
 * 学習内容:
 * 1. Union型とMixed型
 * 2. Match式
 * 3. コンストラクタプロモーション
 * 4. 名前付き引数
 * 5. アトリビュート（属性）
 * 6. Enum（列挙型）- PHP 8.1
 * 7. Readonly プロパティ - PHP 8.1
 */

echo "=== PHP 8 の新機能 ===\n\n";

// ============================================
// 1. Union型とMixed型
// ============================================
echo "--- 1. Union型とMixed型 ---\n";

/**
 * Union型: 複数の型を許容する型宣言
 */
function processValue(int|float|string $value): int|float
{
    if (is_string($value)) {
        return (float)$value;
    }
    return $value;
}

echo "processValue(42): " . processValue(42) . "\n";
echo "processValue(3.14): " . processValue(3.14) . "\n";
echo "processValue('123.45'): " . processValue('123.45') . "\n\n";

/**
 * Mixed型: すべての型を許容（型安全性は低い）
 */
function handleMixedData(mixed $data): void
{
    match (gettype($data)) {
        'integer' => print("整数: {$data}\n"),
        'double' => print("浮動小数点: {$data}\n"),
        'string' => print("文字列: {$data}\n"),
        'array' => print("配列: " . count($data) . "要素\n"),
        'object' => print("オブジェクト: " . get_class($data) . "\n"),
        default => print("その他の型: " . gettype($data) . "\n"),
    };
}

handleMixedData(42);
handleMixedData("Hello");
handleMixedData([1, 2, 3]);
handleMixedData(new stdClass());
echo "\n";

/**
 * null許容Union型
 */
function findUser(int $id): User|null
{
    // データベースからユーザーを検索する想定
    if ($id === 1) {
        return new User(1, 'John Doe', 'john@example.com');
    }
    return null;
}

class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}

$user = findUser(1);
echo $user ? "ユーザー: {$user->name}\n" : "ユーザーが見つかりません\n";

$user = findUser(999);
echo $user ? "ユーザー: {$user->name}\n" : "ユーザーが見つかりません\n";
echo "\n";

// ============================================
// 2. Match式
// ============================================
echo "--- 2. Match式 ---\n";

/**
 * Match式の基本
 * - switch文より簡潔で安全
 * - 厳密な比較（===）を使用
 * - すべてのケースを網羅しないとエラー
 * - 値を返す式として使える
 */
function getStatusMessage(string $status): string
{
    return match ($status) {
        'draft' => '下書き',
        'published' => '公開中',
        'archived' => 'アーカイブ済み',
        'deleted' => '削除済み',
        default => '不明なステータス',
    };
}

echo "ステータス 'published': " . getStatusMessage('published') . "\n";
echo "ステータス 'draft': " . getStatusMessage('draft') . "\n";
echo "ステータス 'unknown': " . getStatusMessage('unknown') . "\n\n";

/**
 * 複数条件のMatch式
 */
function getHttpStatusText(int $code): string
{
    return match ($code) {
        200, 201, 204 => 'Success',
        400, 422 => 'Client Error',
        401, 403 => 'Authentication Error',
        404 => 'Not Found',
        500, 502, 503 => 'Server Error',
        default => 'Unknown Status',
    };
}

echo "HTTPステータス 200: " . getHttpStatusText(200) . "\n";
echo "HTTPステータス 404: " . getHttpStatusText(404) . "\n";
echo "HTTPステータス 500: " . getHttpStatusText(500) . "\n\n";

/**
 * 条件式を使ったMatch式
 */
function getPriceCategory(float $price): string
{
    return match (true) {
        $price < 1000 => '格安',
        $price < 5000 => 'お手頃',
        $price < 10000 => '通常価格',
        $price < 50000 => '高額',
        default => '超高額',
    };
}

echo "価格 500: " . getPriceCategory(500) . "\n";
echo "価格 3000: " . getPriceCategory(3000) . "\n";
echo "価格 20000: " . getPriceCategory(20000) . "\n";
echo "価格 100000: " . getPriceCategory(100000) . "\n\n";

// ============================================
// 3. コンストラクタプロモーション
// ============================================
echo "--- 3. コンストラクタプロモーション ---\n";

/**
 * 従来の書き方（PHP 7）
 */
class ProductOld
{
    private int $id;
    private string $name;
    private float $price;

    public function __construct(int $id, string $name, float $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

/**
 * コンストラクタプロモーション（PHP 8）
 * - コンストラクタの引数でプロパティを定義
 * - コードが簡潔になる
 */
class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private float $price,
        private ?string $description = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getInfo(): string
    {
        $info = "{$this->name}: ¥{$this->price}";
        if ($this->description) {
            $info .= " - {$this->description}";
        }
        return $info;
    }
}

$product = new Product(1, 'ノートPC', 120000, 'ハイスペックモデル');
echo $product->getInfo() . "\n\n";

// ============================================
// 4. 名前付き引数
// ============================================
echo "--- 4. 名前付き引数 ---\n";

/**
 * 名前付き引数により、引数の順序を気にせず呼び出せる
 */
function createUser(
    string $name,
    string $email,
    int $age = 18,
    bool $isActive = true,
    ?string $bio = null,
): array {
    return [
        'name' => $name,
        'email' => $email,
        'age' => $age,
        'isActive' => $isActive,
        'bio' => $bio,
    ];
}

// 従来の呼び出し方
$user1 = createUser('Alice', 'alice@example.com', 25, true, 'Web Developer');
echo "ユーザー1: " . json_encode($user1, JSON_UNESCAPED_UNICODE) . "\n";

// 名前付き引数による呼び出し
$user2 = createUser(
    email: 'bob@example.com',
    name: 'Bob',
    bio: 'Designer',
);
echo "ユーザー2: " . json_encode($user2, JSON_UNESCAPED_UNICODE) . "\n";

// 一部のみ名前付き引数
$user3 = createUser(
    'Charlie',
    'charlie@example.com',
    age: 30,
    bio: 'Project Manager',
);
echo "ユーザー3: " . json_encode($user3, JSON_UNESCAPED_UNICODE) . "\n\n";

// ============================================
// 5. アトリビュート（属性）
// ============================================
echo "--- 5. アトリビュート ---\n";

/**
 * アトリビュート: クラス、メソッド、プロパティにメタデータを付与
 */

#[Attribute]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET',
    ) {}
}

#[Attribute]
class Validate
{
    public function __construct(
        public array $rules,
    ) {}
}

/**
 * アトリビュートを使ったコントローラー
 */
class UserController
{
    #[Route('/users', 'GET')]
    public function index(): string
    {
        return 'ユーザー一覧';
    }

    #[Route('/users/{id}', 'GET')]
    public function show(int $id): string
    {
        return "ユーザー {$id} の詳細";
    }

    #[Route('/users', 'POST')]
    #[Validate(['name' => 'required', 'email' => 'required|email'])]
    public function store(array $data): string
    {
        return 'ユーザーを作成しました';
    }
}

// アトリビュートの取得
$reflectionClass = new ReflectionClass(UserController::class);
$methods = $reflectionClass->getMethods();

foreach ($methods as $method) {
    $attributes = $method->getAttributes(Route::class);
    foreach ($attributes as $attribute) {
        $route = $attribute->newInstance();
        echo "{$route->method} {$route->path} => {$method->getName()}\n";
    }
}
echo "\n";

// ============================================
// 6. Enum（列挙型）- PHP 8.1
// ============================================
echo "--- 6. Enum（列挙型） ---\n";

/**
 * Pure Enum: 値を持たない列挙型
 */
enum Status
{
    case Draft;
    case Published;
    case Archived;
}

/**
 * Backed Enum: 値を持つ列挙型
 */
enum StatusCode: int
{
    case Draft = 0;
    case Published = 1;
    case Archived = 2;

    public function label(): string
    {
        return match ($this) {
            self::Draft => '下書き',
            self::Published => '公開中',
            self::Archived => 'アーカイブ済み',
        };
    }

    public function canEdit(): bool
    {
        return match ($this) {
            self::Draft => true,
            self::Published => false,
            self::Archived => false,
        };
    }
}

$status = StatusCode::Published;
echo "ステータス: {$status->label()}\n";
echo "編集可能: " . ($status->canEdit() ? 'はい' : 'いいえ') . "\n";
echo "値: {$status->value}\n\n";

// Enumから値を取得
$draftStatus = StatusCode::from(0);
echo "値0のステータス: {$draftStatus->label()}\n";

// 安全な値の取得（存在しない場合はnull）
$unknownStatus = StatusCode::tryFrom(99);
echo "値99のステータス: " . ($unknownStatus ? $unknownStatus->label() : 'なし') . "\n\n";

/**
 * メソッド付きEnum
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function permissions(): array
    {
        return match ($this) {
            self::Admin => ['create', 'read', 'update', 'delete', 'manage_users'],
            self::Editor => ['create', 'read', 'update'],
            self::Viewer => ['read'],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}

$role = UserRole::Editor;
echo "ロール: {$role->value}\n";
echo "権限: " . implode(', ', $role->permissions()) . "\n";
echo "削除権限: " . ($role->hasPermission('delete') ? 'あり' : 'なし') . "\n\n";

// ============================================
// 7. Readonly プロパティ - PHP 8.1
// ============================================
echo "--- 7. Readonly プロパティ ---\n";

/**
 * Readonlyプロパティ: 初期化後は変更不可
 */
class Point
{
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}

    public function distanceFromOrigin(): float
    {
        return sqrt($this->x ** 2 + $this->y ** 2);
    }
}

$point = new Point(3.0, 4.0);
echo "座標: ({$point->x}, {$point->y})\n";
echo "原点からの距離: " . $point->distanceFromOrigin() . "\n";

// エラー: readonlyプロパティは変更できない
// $point->x = 5.0; // エラーになる
echo "\n";

/**
 * Readonlyクラス - PHP 8.2
 * すべてのプロパティがreadonlyになる
 */
readonly class Configuration
{
    public function __construct(
        public string $appName,
        public string $environment,
        public bool $debug,
        public array $database,
    ) {}
}

$config = new Configuration(
    appName: 'My App',
    environment: 'production',
    debug: false,
    database: [
        'host' => 'localhost',
        'name' => 'mydb',
    ],
);

echo "アプリ名: {$config->appName}\n";
echo "環境: {$config->environment}\n";
echo "デバッグ: " . ($config->debug ? 'ON' : 'OFF') . "\n";
echo "データベース: " . json_encode($config->database) . "\n\n";

// ============================================
// 8. その他のPHP 8新機能
// ============================================
echo "--- 8. その他の新機能 ---\n";

/**
 * Null safe演算子 (?->)
 */
class Address
{
    public function __construct(
        public readonly string $city,
        public readonly string $country,
    ) {}
}

class Customer
{
    public function __construct(
        public readonly string $name,
        public readonly ?Address $address = null,
    ) {}
}

$customer1 = new Customer('Alice', new Address('Tokyo', 'Japan'));
$customer2 = new Customer('Bob');

// 従来の書き方
$city1 = $customer1->address !== null ? $customer1->address->city : null;
echo "顧客1の都市（従来）: " . ($city1 ?? 'なし') . "\n";

// Null safe演算子
$city2 = $customer1->address?->city;
$city3 = $customer2->address?->city;
echo "顧客1の都市（null safe）: " . ($city2 ?? 'なし') . "\n";
echo "顧客2の都市（null safe）: " . ($city3 ?? 'なし') . "\n\n";

/**
 * str_contains(), str_starts_with(), str_ends_with()
 */
$text = 'Hello, PHP 8!';

echo "'{$text}' に 'PHP' が含まれる: " . (str_contains($text, 'PHP') ? 'はい' : 'いいえ') . "\n";
echo "'{$text}' が 'Hello' で始まる: " . (str_starts_with($text, 'Hello') ? 'はい' : 'いいえ') . "\n";
echo "'{$text}' が '8!' で終わる: " . (str_ends_with($text, '8!') ? 'はい' : 'いいえ') . "\n\n";

/**
 * array_is_list() - PHP 8.1
 * 配列が連続したリストかどうかをチェック
 */
$list = [1, 2, 3, 4];
$assoc = ['a' => 1, 'b' => 2];
$mixedKeys = [0 => 'a', 2 => 'b']; // キーが連続していない

echo "リスト判定:\n";
echo "  [1, 2, 3, 4]: " . (array_is_list($list) ? 'リスト' : '連想配列') . "\n";
echo "  ['a' => 1, 'b' => 2]: " . (array_is_list($assoc) ? 'リスト' : '連想配列') . "\n";
echo "  [0 => 'a', 2 => 'b']: " . (array_is_list($mixedKeys) ? 'リスト' : '連想配列') . "\n\n";

/**
 * 引数アンパック（スプレッド演算子）の配列キーサポート - PHP 8.1
 */
$array1 = ['a' => 1, 'b' => 2];
$array2 = ['b' => 3, 'c' => 4];
$merged = [...$array1, ...$array2];

echo "配列のマージ:\n";
echo "  array1: " . json_encode($array1) . "\n";
echo "  array2: " . json_encode($array2) . "\n";
echo "  merged: " . json_encode($merged) . "\n\n";

/**
 * First-class callable syntax - PHP 8.1
 */
function multiply(int $a, int $b): int
{
    return $a * $b;
}

// 従来の書き方
$callable1 = 'multiply';
echo "従来の呼び出し: multiply(3, 4) = " . $callable1(3, 4) . "\n";

// First-class callable syntax
$callable2 = multiply(...);
echo "新しい構文: multiply(3, 4) = " . $callable2(3, 4) . "\n\n";

echo "=== Phase 4.1 完了 ===\n";
echo "PHP 8の新機能を学習しました！\n";
echo "- Union型とMixed型による柔軟な型宣言\n";
echo "- Match式による安全で簡潔な分岐\n";
echo "- コンストラクタプロモーションによる簡潔なクラス定義\n";
echo "- 名前付き引数による可読性の向上\n";
echo "- アトリビュートによるメタデータの付与\n";
echo "- Enumによる型安全な定数管理\n";
echo "- Readonlyプロパティによるイミュータブルなオブジェクト\n";
