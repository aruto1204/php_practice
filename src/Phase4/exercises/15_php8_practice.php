<?php

declare(strict_types=1);

/**
 * Phase 4.1 演習課題: PHP 8 の新機能を使ったコードの書き換え
 *
 * この演習では、PHP 7 以前のコードをPHP 8 の新機能を使って書き換えます。
 *
 * 課題:
 * 1. タスク管理システム（Union型、Enum、Match式）
 * 2. 設定管理システム（Readonlyクラス、名前付き引数）
 * 3. ルーティングシステム（アトリビュート）
 * 4. データ変換システム（Union型、Match式、Null safe演算子）
 * 5. 商品カタログシステム（総合演習）
 */

echo "=== PHP 8 演習課題 ===\n\n";

// ============================================
// 課題1: タスク管理システム
// ============================================
echo "--- 課題1: タスク管理システム ---\n";

/**
 * タスクの優先度（Enum）
 */
enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
    case Critical = 4;

    public function label(): string
    {
        return match ($this) {
            self::Low => '低',
            self::Medium => '中',
            self::High => '高',
            self::Critical => '緊急',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'green',
            self::Medium => 'blue',
            self::High => 'orange',
            self::Critical => 'red',
        };
    }
}

/**
 * タスクのステータス（Enum）
 */
enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Todo => '未着手',
            self::InProgress => '進行中',
            self::Review => 'レビュー中',
            self::Done => '完了',
            self::Cancelled => 'キャンセル',
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::Todo, self::InProgress, self::Review => true,
            self::Done, self::Cancelled => false,
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Todo => in_array($newStatus, [self::InProgress, self::Cancelled], true),
            self::InProgress => in_array($newStatus, [self::Review, self::Cancelled], true),
            self::Review => in_array($newStatus, [self::Done, self::InProgress], true),
            self::Done, self::Cancelled => false,
        };
    }
}

/**
 * タスククラス（コンストラクタプロモーション、Readonly）
 */
class Task
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $description,
        private TaskStatus $status,
        private Priority $priority,
        public readonly ?string $assignee = null,
        public readonly ?\DateTimeImmutable $dueDate = null,
    ) {}

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function changeStatus(TaskStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "ステータスを {$this->status->label()} から {$newStatus->label()} に変更できません"
            );
        }
        $this->status = $newStatus;
    }

    public function changePriority(Priority $newPriority): void
    {
        $this->priority = $newPriority;
    }

    public function isOverdue(): bool
    {
        if ($this->dueDate === null) {
            return false;
        }
        return $this->dueDate < new \DateTimeImmutable() && $this->status->isActive();
    }

    public function getInfo(): string
    {
        $info = "[{$this->id}] {$this->title}\n";
        $info .= "  ステータス: {$this->status->label()}\n";
        $info .= "  優先度: {$this->priority->label()} ({$this->priority->color()})\n";
        $info .= "  担当者: " . ($this->assignee ?? '未割り当て') . "\n";

        if ($this->dueDate) {
            $info .= "  期限: {$this->dueDate->format('Y-m-d')}";
            if ($this->isOverdue()) {
                $info .= " ⚠️ 期限切れ";
            }
            $info .= "\n";
        }

        return $info;
    }
}

/**
 * タスク管理クラス
 */
class TaskManager
{
    /** @var Task[] */
    private array $tasks = [];
    private int $nextId = 1;

    public function createTask(
        string $title,
        string $description,
        Priority $priority = Priority::Medium,
        ?string $assignee = null,
        ?string $dueDate = null,
    ): Task {
        $dueDateObj = $dueDate ? new \DateTimeImmutable($dueDate) : null;

        $task = new Task(
            id: $this->nextId++,
            title: $title,
            description: $description,
            status: TaskStatus::Todo,
            priority: $priority,
            assignee: $assignee,
            dueDate: $dueDateObj,
        );

        $this->tasks[$task->id] = $task;
        return $task;
    }

    public function findTask(int $id): Task|null
    {
        return $this->tasks[$id] ?? null;
    }

    /**
     * タスクのフィルタリング（Union型の活用）
     */
    public function filterTasks(
        TaskStatus|array|null $status = null,
        Priority|array|null $priority = null,
        bool $overdueOnly = false,
    ): array {
        $filtered = $this->tasks;

        if ($status !== null) {
            $statuses = is_array($status) ? $status : [$status];
            $filtered = array_filter(
                $filtered,
                fn(Task $task) => in_array($task->getStatus(), $statuses, true)
            );
        }

        if ($priority !== null) {
            $priorities = is_array($priority) ? $priority : [$priority];
            $filtered = array_filter(
                $filtered,
                fn(Task $task) => in_array($task->getPriority(), $priorities, true)
            );
        }

        if ($overdueOnly) {
            $filtered = array_filter($filtered, fn(Task $task) => $task->isOverdue());
        }

        return array_values($filtered);
    }

    public function getStatistics(): array
    {
        $stats = [
            'total' => count($this->tasks),
            'by_status' => [],
            'by_priority' => [],
            'overdue' => 0,
        ];

        foreach ($this->tasks as $task) {
            $status = $task->getStatus()->value;
            $priority = $task->getPriority()->value;

            $stats['by_status'][$status] = ($stats['by_status'][$status] ?? 0) + 1;
            $stats['by_priority'][$priority] = ($stats['by_priority'][$priority] ?? 0) + 1;

            if ($task->isOverdue()) {
                $stats['overdue']++;
            }
        }

        return $stats;
    }
}

// テスト
$manager = new TaskManager();

$task1 = $manager->createTask(
    title: 'データベース設計',
    description: 'ユーザーテーブルと商品テーブルの設計',
    priority: Priority::High,
    assignee: 'Alice',
    dueDate: '2025-12-30',
);

$task2 = $manager->createTask(
    title: 'API実装',
    description: 'REST APIエンドポイントの実装',
    priority: Priority::Critical,
    assignee: 'Bob',
    dueDate: '2025-12-25', // 過去の日付（期限切れ）
);

$task3 = $manager->createTask(
    title: 'ドキュメント作成',
    description: 'APIドキュメントの作成',
    priority: Priority::Low,
);

echo $task1->getInfo() . "\n";
echo $task2->getInfo() . "\n";

// ステータス変更
$task1->changeStatus(TaskStatus::InProgress);
echo "タスク1をInProgressに変更\n";
echo $task1->getInfo() . "\n";

// フィルタリング
$activeTasks = $manager->filterTasks(status: [TaskStatus::Todo, TaskStatus::InProgress]);
echo "アクティブなタスク: " . count($activeTasks) . "件\n";

$highPriorityTasks = $manager->filterTasks(priority: [Priority::High, Priority::Critical]);
echo "優先度が高いタスク: " . count($highPriorityTasks) . "件\n";

$overdueTasks = $manager->filterTasks(overdueOnly: true);
echo "期限切れタスク: " . count($overdueTasks) . "件\n";

// 統計
$stats = $manager->getStatistics();
echo "\n統計:\n";
echo "  総タスク数: {$stats['total']}\n";
echo "  期限切れ: {$stats['overdue']}\n";
echo "\n";

// ============================================
// 課題2: 設定管理システム
// ============================================
echo "--- 課題2: 設定管理システム ---\n";

/**
 * データベース設定（Readonlyクラス）
 */
readonly class DatabaseConfig
{
    public function __construct(
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public string $password,
        public string $charset = 'utf8mb4',
    ) {}

    public function getDsn(): string
    {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
    }
}

/**
 * キャッシュ設定（Readonlyクラス）
 */
readonly class CacheConfig
{
    public function __construct(
        public string $driver,
        public int $ttl = 3600,
        public string $prefix = 'app_',
    ) {}
}

/**
 * アプリケーション設定（Readonlyクラス）
 */
readonly class AppConfig
{
    public function __construct(
        public string $name,
        public string $environment,
        public bool $debug,
        public DatabaseConfig $database,
        public CacheConfig $cache,
        public array $features = [],
    ) {}

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features, true);
    }
}

// 名前付き引数を使った設定の作成
$config = new AppConfig(
    name: 'My Application',
    environment: 'production',
    debug: false,
    database: new DatabaseConfig(
        host: 'localhost',
        port: 3306,
        database: 'myapp',
        username: 'root',
        password: 'secret',
    ),
    cache: new CacheConfig(
        driver: 'redis',
        ttl: 7200,
    ),
    features: ['api', 'admin', 'analytics'],
);

echo "アプリ名: {$config->name}\n";
echo "環境: {$config->environment}\n";
echo "本番環境: " . ($config->isProduction() ? 'はい' : 'いいえ') . "\n";
echo "デバッグ: " . ($config->debug ? 'ON' : 'OFF') . "\n";
echo "DB DSN: {$config->database->getDsn()}\n";
echo "キャッシュ: {$config->cache->driver} (TTL: {$config->cache->ttl}秒)\n";
echo "API機能: " . ($config->hasFeature('api') ? '有効' : '無効') . "\n";
echo "\n";

// ============================================
// 課題3: ルーティングシステム
// ============================================
echo "--- 課題3: ルーティングシステム ---\n";

/**
 * Routeアトリビュート
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public array $middleware = [],
    ) {}
}

/**
 * Middlewareアトリビュート
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Middleware
{
    public function __construct(
        public array $middleware,
    ) {}
}

/**
 * APIコントローラー
 */
#[Middleware(['auth'])]
class ApiController
{
    #[Route('/api/users', 'GET')]
    public function index(): array
    {
        return ['users' => []];
    }

    #[Route('/api/users/{id}', 'GET')]
    public function show(int $id): array
    {
        return ['id' => $id, 'name' => 'User ' . $id];
    }

    #[Route('/api/users', 'POST')]
    #[Middleware(['validate'])]
    public function store(array $data): array
    {
        return ['message' => 'User created'];
    }

    #[Route('/api/users/{id}', 'PUT')]
    #[Route('/api/users/{id}', 'PATCH')]
    #[Middleware(['validate'])]
    public function update(int $id, array $data): array
    {
        return ['message' => "User {$id} updated"];
    }

    #[Route('/api/users/{id}', 'DELETE')]
    public function destroy(int $id): array
    {
        return ['message' => "User {$id} deleted"];
    }
}

/**
 * ルーター
 */
class Router
{
    private array $routes = [];

    public function registerController(string $controllerClass): void
    {
        $reflection = new ReflectionClass($controllerClass);

        // クラスレベルのミドルウェア
        $classMiddleware = [];
        $middlewareAttrs = $reflection->getAttributes(Middleware::class);
        foreach ($middlewareAttrs as $attr) {
            $middleware = $attr->newInstance();
            $classMiddleware = array_merge($classMiddleware, $middleware->middleware);
        }

        // メソッドレベルのルート
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttrs = $method->getAttributes(Route::class);

            foreach ($routeAttrs as $routeAttr) {
                $route = $routeAttr->newInstance();

                // メソッドレベルのミドルウェア
                $methodMiddleware = [];
                $methodMiddlewareAttrs = $method->getAttributes(Middleware::class);
                foreach ($methodMiddlewareAttrs as $attr) {
                    $middleware = $attr->newInstance();
                    $methodMiddleware = array_merge($methodMiddleware, $middleware->middleware);
                }

                $allMiddleware = array_merge($classMiddleware, $route->middleware, $methodMiddleware);

                $this->routes[] = [
                    'method' => $route->method,
                    'path' => $route->path,
                    'handler' => [$controllerClass, $method->getName()],
                    'middleware' => array_unique($allMiddleware),
                ];
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function printRoutes(): void
    {
        foreach ($this->routes as $route) {
            $middleware = empty($route['middleware']) ? '' : ' [' . implode(', ', $route['middleware']) . ']';
            echo "{$route['method']} {$route['path']} => {$route['handler'][1]}(){$middleware}\n";
        }
    }
}

$router = new Router();
$router->registerController(ApiController::class);
$router->printRoutes();
echo "\n";

// ============================================
// 課題4: データ変換システム
// ============================================
echo "--- 課題4: データ変換システム ---\n";

/**
 * データコンバーター（Union型の活用）
 */
class DataConverter
{
    /**
     * 様々な型の値を文字列に変換
     */
    public function toString(mixed $value): string
    {
        return match (true) {
            is_null($value) => '',
            is_bool($value) => $value ? 'true' : 'false',
            is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
            is_object($value) => method_exists($value, '__toString')
                ? (string)$value
                : json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string)$value,
        };
    }

    /**
     * 様々な型の値を整数に変換
     */
    public function toInt(mixed $value): int|null
    {
        return match (true) {
            is_int($value) => $value,
            is_float($value) => (int)$value,
            is_string($value) && is_numeric($value) => (int)$value,
            is_bool($value) => $value ? 1 : 0,
            default => null,
        };
    }

    /**
     * 様々な型の値を配列に変換
     */
    public function toArray(mixed $value): array
    {
        return match (true) {
            is_array($value) => $value,
            is_object($value) => (array)$value,
            is_null($value) => [],
            default => [$value],
        };
    }
}

/**
 * データバリデーター（Match式の活用）
 */
class DataValidator
{
    public function validate(string $type, mixed $value): bool
    {
        return match ($type) {
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'int' => is_int($value),
            'float' => is_float($value),
            'numeric' => is_numeric($value),
            'string' => is_string($value),
            'array' => is_array($value),
            'bool' => is_bool($value),
            default => false,
        };
    }

    public function getErrorMessage(string $type): string
    {
        return match ($type) {
            'email' => '有効なメールアドレスを入力してください',
            'url' => '有効なURLを入力してください',
            'int' => '整数を入力してください',
            'float' => '小数を入力してください',
            'numeric' => '数値を入力してください',
            'string' => '文字列を入力してください',
            'array' => '配列を指定してください',
            'bool' => '真偽値を指定してください',
            default => '不明なバリデーションタイプです',
        };
    }
}

$converter = new DataConverter();

echo "文字列変換:\n";
echo "  42 => '" . $converter->toString(42) . "'\n";
echo "  true => '" . $converter->toString(true) . "'\n";
echo "  [1, 2, 3] => '" . $converter->toString([1, 2, 3]) . "'\n";
echo "  null => '" . $converter->toString(null) . "'\n\n";

echo "整数変換:\n";
echo "  '123' => " . ($converter->toInt('123') ?? 'null') . "\n";
echo "  3.14 => " . ($converter->toInt(3.14) ?? 'null') . "\n";
echo "  true => " . ($converter->toInt(true) ?? 'null') . "\n";
echo "  'abc' => " . ($converter->toInt('abc') ?? 'null') . "\n\n";

$validator = new DataValidator();

echo "バリデーション:\n";
echo "  'test@example.com' (email): " . ($validator->validate('email', 'test@example.com') ? '✓' : '✗') . "\n";
echo "  'invalid-email' (email): " . ($validator->validate('email', 'invalid-email') ? '✓' : '✗') . "\n";
if (!$validator->validate('email', 'invalid-email')) {
    echo "    エラー: " . $validator->getErrorMessage('email') . "\n";
}
echo "\n";

// ============================================
// 課題5: 商品カタログシステム（総合演習）
// ============================================
echo "--- 課題5: 商品カタログシステム ---\n";

/**
 * 商品カテゴリー（Enum）
 */
enum ProductCategory: string
{
    case Electronics = 'electronics';
    case Books = 'books';
    case Clothing = 'clothing';
    case Food = 'food';
    case Toys = 'toys';

    public function label(): string
    {
        return match ($this) {
            self::Electronics => '電化製品',
            self::Books => '書籍',
            self::Clothing => '衣類',
            self::Food => '食品',
            self::Toys => 'おもちゃ',
        };
    }
}

/**
 * 在庫ステータス（Enum）
 */
enum StockStatus: string
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';
    case Discontinued = 'discontinued';

    public function label(): string
    {
        return match ($this) {
            self::InStock => '在庫あり',
            self::LowStock => '在庫わずか',
            self::OutOfStock => '在庫切れ',
            self::Discontinued => '販売終了',
        };
    }

    public function canPurchase(): bool
    {
        return match ($this) {
            self::InStock, self::LowStock => true,
            self::OutOfStock, self::Discontinued => false,
        };
    }
}

/**
 * 商品クラス
 */
readonly class Product
{
    public function __construct(
        public int $id,
        public string $name,
        public ProductCategory $category,
        public float $price,
        public int $stock,
        public ?string $description = null,
        public ?array $tags = null,
    ) {}

    public function getStockStatus(): StockStatus
    {
        return match (true) {
            $this->stock === 0 => StockStatus::OutOfStock,
            $this->stock <= 5 => StockStatus::LowStock,
            default => StockStatus::InStock,
        };
    }

    public function canPurchase(int $quantity = 1): bool
    {
        return $this->stock >= $quantity && $this->getStockStatus()->canPurchase();
    }

    public function getPriceWithTax(float $taxRate = 0.1): float
    {
        return round($this->price * (1 + $taxRate), 2);
    }

    public function getInfo(): string
    {
        $info = "[{$this->id}] {$this->name}\n";
        $info .= "  カテゴリー: {$this->category->label()}\n";
        $info .= "  価格: ¥" . number_format($this->price) . " (税込: ¥" . number_format($this->getPriceWithTax()) . ")\n";
        $info .= "  在庫: {$this->stock}個 ({$this->getStockStatus()->label()})\n";

        if ($this->description) {
            $info .= "  説明: {$this->description}\n";
        }

        if ($this->tags) {
            $info .= "  タグ: " . implode(', ', $this->tags) . "\n";
        }

        return $info;
    }
}

/**
 * 商品カタログ
 */
class ProductCatalog
{
    /** @var Product[] */
    private array $products = [];

    public function addProduct(Product $product): void
    {
        $this->products[$product->id] = $product;
    }

    public function findProduct(int $id): Product|null
    {
        return $this->products[$id] ?? null;
    }

    /**
     * 商品のフィルタリング（Union型の活用）
     */
    public function filterProducts(
        ProductCategory|array|null $category = null,
        float|null $minPrice = null,
        float|null $maxPrice = null,
        StockStatus|array|null $stockStatus = null,
        string|null $searchTerm = null,
    ): array {
        $filtered = $this->products;

        if ($category !== null) {
            $categories = is_array($category) ? $category : [$category];
            $filtered = array_filter(
                $filtered,
                fn(Product $p) => in_array($p->category, $categories, true)
            );
        }

        if ($minPrice !== null) {
            $filtered = array_filter($filtered, fn(Product $p) => $p->price >= $minPrice);
        }

        if ($maxPrice !== null) {
            $filtered = array_filter($filtered, fn(Product $p) => $p->price <= $maxPrice);
        }

        if ($stockStatus !== null) {
            $statuses = is_array($stockStatus) ? $stockStatus : [$stockStatus];
            $filtered = array_filter(
                $filtered,
                fn(Product $p) => in_array($p->getStockStatus(), $statuses, true)
            );
        }

        if ($searchTerm !== null) {
            $filtered = array_filter(
                $filtered,
                fn(Product $p) => str_contains(strtolower($p->name), strtolower($searchTerm))
            );
        }

        return array_values($filtered);
    }

    public function getStatistics(): array
    {
        return [
            'total' => count($this->products),
            'by_category' => $this->countByCategory(),
            'by_stock_status' => $this->countByStockStatus(),
            'total_value' => $this->calculateTotalValue(),
        ];
    }

    private function countByCategory(): array
    {
        $counts = [];
        foreach ($this->products as $product) {
            $category = $product->category->value;
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }
        return $counts;
    }

    private function countByStockStatus(): array
    {
        $counts = [];
        foreach ($this->products as $product) {
            $status = $product->getStockStatus()->value;
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        return $counts;
    }

    private function calculateTotalValue(): float
    {
        return array_reduce(
            $this->products,
            fn(float $total, Product $p) => $total + ($p->price * $p->stock),
            0.0
        );
    }
}

// テスト
$catalog = new ProductCatalog();

$catalog->addProduct(new Product(
    id: 1,
    name: 'ノートPC',
    category: ProductCategory::Electronics,
    price: 120000,
    stock: 15,
    description: 'ハイスペックノートパソコン',
    tags: ['パソコン', '高性能'],
));

$catalog->addProduct(new Product(
    id: 2,
    name: 'PHP入門書',
    category: ProductCategory::Books,
    price: 3000,
    stock: 3,
    description: 'PHP初心者向けの入門書',
    tags: ['プログラミング', 'PHP'],
));

$catalog->addProduct(new Product(
    id: 3,
    name: 'Tシャツ',
    category: ProductCategory::Clothing,
    price: 2000,
    stock: 0,
    description: 'コットン100%のTシャツ',
    tags: ['衣類', 'カジュアル'],
));

// 商品情報の表示
$product = $catalog->findProduct(1);
if ($product) {
    echo $product->getInfo() . "\n";
}

// フィルタリング
$electronics = $catalog->filterProducts(category: ProductCategory::Electronics);
echo "電化製品: " . count($electronics) . "件\n";

$lowStock = $catalog->filterProducts(stockStatus: StockStatus::LowStock);
echo "在庫わずか: " . count($lowStock) . "件\n";

$affordable = $catalog->filterProducts(maxPrice: 5000);
echo "5000円以下: " . count($affordable) . "件\n";

// 統計
$stats = $catalog->getStatistics();
echo "\n統計:\n";
echo "  総商品数: {$stats['total']}\n";
echo "  在庫総額: ¥" . number_format($stats['total_value']) . "\n";
echo "\n";

echo "=== すべての演習課題が完了しました ===\n";
echo "PHP 8の新機能を実践的に活用できました！\n";
