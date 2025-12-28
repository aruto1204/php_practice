<?php

declare(strict_types=1);

/**
 * Phase 4.2: デザインパターン
 *
 * このファイルでは、よく使われるデザインパターンについて学びます。
 *
 * 学習内容:
 * 1. Singleton パターン
 * 2. Factory パターン
 * 3. Strategy パターン
 * 4. Observer パターン
 * 5. MVC パターン
 */

echo "=== デザインパターン ===\n\n";

// ============================================
// 1. Singleton パターン
// ============================================
echo "--- 1. Singleton パターン ---\n";
echo "目的: クラスのインスタンスが1つしか存在しないことを保証する\n";
echo "用途: データベース接続、ログ管理、設定管理など\n\n";

/**
 * Singletonパターンの実装
 */
class Database
{
    private static ?Database $instance = null;
    private string $connection;

    /**
     * コンストラクタをprivateにして外部からのインスタンス化を防ぐ
     */
    private function __construct()
    {
        $this->connection = "Database Connection " . uniqid();
        echo "データベース接続を作成: {$this->connection}\n";
    }

    /**
     * クローンを禁止
     */
    private function __clone() {}

    /**
     * シリアライズを禁止
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * インスタンスを取得（存在しない場合は作成）
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query(string $sql): void
    {
        echo "クエリ実行 [{$this->connection}]: {$sql}\n";
    }

    public function getConnectionId(): string
    {
        return $this->connection;
    }
}

// テスト
$db1 = Database::getInstance();
$db2 = Database::getInstance();

echo "db1 === db2: " . ($db1 === $db2 ? 'true' : 'false') . " (同じインスタンス)\n";
echo "db1 ID: {$db1->getConnectionId()}\n";
echo "db2 ID: {$db2->getConnectionId()}\n";

$db1->query("SELECT * FROM users");
$db2->query("SELECT * FROM products");
echo "\n";

// ============================================
// 2. Factory パターン
// ============================================
echo "--- 2. Factory パターン ---\n";
echo "目的: オブジェクトの生成ロジックをカプセル化する\n";
echo "用途: 条件に応じて異なるクラスのインスタンスを生成\n\n";

/**
 * 通知インターフェース
 */
interface Notification
{
    public function send(string $recipient, string $message): void;
}

/**
 * メール通知
 */
class EmailNotification implements Notification
{
    public function send(string $recipient, string $message): void
    {
        echo "📧 メール送信: To={$recipient}, Message={$message}\n";
    }
}

/**
 * SMS通知
 */
class SmsNotification implements Notification
{
    public function send(string $recipient, string $message): void
    {
        echo "📱 SMS送信: To={$recipient}, Message={$message}\n";
    }
}

/**
 * プッシュ通知
 */
class PushNotification implements Notification
{
    public function send(string $recipient, string $message): void
    {
        echo "🔔 プッシュ通知: To={$recipient}, Message={$message}\n";
    }
}

/**
 * 通知ファクトリー（Simple Factory）
 */
class NotificationFactory
{
    public static function create(string $type): Notification
    {
        return match ($type) {
            'email' => new EmailNotification(),
            'sms' => new SmsNotification(),
            'push' => new PushNotification(),
            default => throw new \InvalidArgumentException("Unknown notification type: {$type}"),
        };
    }
}

// テスト
$emailNotification = NotificationFactory::create('email');
$emailNotification->send('user@example.com', 'こんにちは！');

$smsNotification = NotificationFactory::create('sms');
$smsNotification->send('090-1234-5678', '確認コード: 123456');

$pushNotification = NotificationFactory::create('push');
$pushNotification->send('user123', '新しいメッセージがあります');
echo "\n";

/**
 * Factory Methodパターン
 */
abstract class NotificationService
{
    /**
     * ファクトリーメソッド（サブクラスで実装）
     */
    abstract protected function createNotification(): Notification;

    public function notify(string $recipient, string $message): void
    {
        $notification = $this->createNotification();
        $notification->send($recipient, $message);
    }
}

class EmailService extends NotificationService
{
    protected function createNotification(): Notification
    {
        return new EmailNotification();
    }
}

class SmsService extends NotificationService
{
    protected function createNotification(): Notification
    {
        return new SmsNotification();
    }
}

echo "Factory Methodパターン:\n";
$emailService = new EmailService();
$emailService->notify('admin@example.com', '管理者通知');

$smsService = new SmsService();
$smsService->notify('090-9999-9999', '緊急通知');
echo "\n";

// ============================================
// 3. Strategy パターン
// ============================================
echo "--- 3. Strategy パターン ---\n";
echo "目的: アルゴリズムをカプセル化し、実行時に切り替え可能にする\n";
echo "用途: 支払い方法、ソート方法、圧縮方法など\n\n";

/**
 * 支払い戦略インターフェース
 */
interface PaymentStrategy
{
    public function pay(float $amount): void;
}

/**
 * クレジットカード決済
 */
class CreditCardPayment implements PaymentStrategy
{
    public function __construct(
        private readonly string $cardNumber,
        private readonly string $cvv,
    ) {}

    public function pay(float $amount): void
    {
        echo "💳 クレジットカード決済: ¥" . number_format($amount) . "\n";
        echo "   カード番号: " . substr($this->cardNumber, -4) . "で支払い\n";
    }
}

/**
 * PayPal決済
 */
class PayPalPayment implements PaymentStrategy
{
    public function __construct(
        private readonly string $email,
    ) {}

    public function pay(float $amount): void
    {
        echo "🅿️ PayPal決済: ¥" . number_format($amount) . "\n";
        echo "   アカウント: {$this->email}で支払い\n";
    }
}

/**
 * 銀行振込
 */
class BankTransferPayment implements PaymentStrategy
{
    public function __construct(
        private readonly string $bankName,
        private readonly string $accountNumber,
    ) {}

    public function pay(float $amount): void
    {
        echo "🏦 銀行振込: ¥" . number_format($amount) . "\n";
        echo "   {$this->bankName} 口座番号: {$this->accountNumber}へ振込\n";
    }
}

/**
 * ショッピングカート
 */
class ShoppingCart
{
    private array $items = [];
    private ?PaymentStrategy $paymentStrategy = null;

    public function addItem(string $name, float $price): void
    {
        $this->items[] = ['name' => $name, 'price' => $price];
    }

    public function setPaymentStrategy(PaymentStrategy $strategy): void
    {
        $this->paymentStrategy = $strategy;
    }

    public function getTotalAmount(): float
    {
        return array_reduce(
            $this->items,
            fn(float $total, array $item) => $total + $item['price'],
            0.0
        );
    }

    public function checkout(): void
    {
        if ($this->paymentStrategy === null) {
            throw new \RuntimeException("支払い方法が設定されていません");
        }

        $total = $this->getTotalAmount();
        echo "カート内容: " . count($this->items) . "点\n";
        echo "合計金額: ¥" . number_format($total) . "\n";

        $this->paymentStrategy->pay($total);
    }
}

// テスト
$cart = new ShoppingCart();
$cart->addItem('ノートPC', 120000);
$cart->addItem('マウス', 3000);
$cart->addItem('キーボード', 8000);

echo "支払い方法1: クレジットカード\n";
$cart->setPaymentStrategy(new CreditCardPayment('1234-5678-9012-3456', '123'));
$cart->checkout();
echo "\n";

echo "支払い方法2: PayPal\n";
$cart->setPaymentStrategy(new PayPalPayment('user@example.com'));
$cart->checkout();
echo "\n";

echo "支払い方法3: 銀行振込\n";
$cart->setPaymentStrategy(new BankTransferPayment('三菱UFJ銀行', '1234567'));
$cart->checkout();
echo "\n";

// ============================================
// 4. Observer パターン
// ============================================
echo "--- 4. Observer パターン ---\n";
echo "目的: オブジェクトの状態変化を他のオブジェクトに通知する\n";
echo "用途: イベントハンドリング、リアルタイム通知、データバインディングなど\n\n";

/**
 * オブザーバーインターフェース
 */
interface Observer
{
    public function update(string $event, mixed $data): void;
}

/**
 * サブジェクト（監視対象）インターフェース
 */
interface Subject
{
    public function attach(Observer $observer): void;
    public function detach(Observer $observer): void;
    public function notify(string $event, mixed $data): void;
}

/**
 * ユーザーアカウント（Subject）
 */
class UserAccount implements Subject
{
    private array $observers = [];

    public function __construct(
        private string $username,
        private string $email,
    ) {}

    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer): void
    {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    public function notify(string $event, mixed $data): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }

    public function login(): void
    {
        echo "🔐 {$this->username} がログインしました\n";
        $this->notify('login', [
            'username' => $this->username,
            'email' => $this->email,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateProfile(string $newEmail): void
    {
        $oldEmail = $this->email;
        $this->email = $newEmail;
        echo "✏️ {$this->username} がプロフィールを更新しました\n";
        $this->notify('profile_updated', [
            'username' => $this->username,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ]);
    }

    public function logout(): void
    {
        echo "👋 {$this->username} がログアウトしました\n";
        $this->notify('logout', [
            'username' => $this->username,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}

/**
 * ログオブザーバー
 */
class LogObserver implements Observer
{
    public function update(string $event, mixed $data): void
    {
        $log = "[LOG] イベント: {$event} | データ: " . json_encode($data, JSON_UNESCAPED_UNICODE);
        echo "  {$log}\n";
    }
}

/**
 * メール通知オブザーバー
 */
class EmailObserver implements Observer
{
    public function update(string $event, mixed $data): void
    {
        if ($event === 'login') {
            echo "  [EMAIL] {$data['email']} にログイン通知を送信\n";
        } elseif ($event === 'profile_updated') {
            echo "  [EMAIL] {$data['new_email']} にプロフィール更新通知を送信\n";
        }
    }
}

/**
 * 統計オブザーバー
 */
class AnalyticsObserver implements Observer
{
    private array $stats = [];

    public function update(string $event, mixed $data): void
    {
        $this->stats[$event] = ($this->stats[$event] ?? 0) + 1;
        echo "  [ANALYTICS] {$event} イベントを記録 (合計: {$this->stats[$event]}回)\n";
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}

// テスト
$user = new UserAccount('alice', 'alice@example.com');

// オブザーバーを登録
$logObserver = new LogObserver();
$emailObserver = new EmailObserver();
$analyticsObserver = new AnalyticsObserver();

$user->attach($logObserver);
$user->attach($emailObserver);
$user->attach($analyticsObserver);

// イベント発火
$user->login();
echo "\n";

$user->updateProfile('alice.new@example.com');
echo "\n";

$user->logout();
echo "\n";

// ============================================
// 5. MVC パターン
// ============================================
echo "--- 5. MVC パターン ---\n";
echo "目的: アプリケーションをModel、View、Controllerに分離する\n";
echo "用途: Webアプリケーション、GUIアプリケーションなど\n\n";

/**
 * Model: データとビジネスロジック
 */
class TodoModel
{
    private array $todos = [];
    private int $nextId = 1;

    public function addTodo(string $title): array
    {
        $todo = [
            'id' => $this->nextId++,
            'title' => $title,
            'completed' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->todos[] = $todo;
        return $todo;
    }

    public function getTodos(): array
    {
        return $this->todos;
    }

    public function getTodo(int $id): ?array
    {
        foreach ($this->todos as $todo) {
            if ($todo['id'] === $id) {
                return $todo;
            }
        }
        return null;
    }

    public function completeTodo(int $id): bool
    {
        foreach ($this->todos as &$todo) {
            if ($todo['id'] === $id) {
                $todo['completed'] = true;
                return true;
            }
        }
        return false;
    }

    public function deleteTodo(int $id): bool
    {
        foreach ($this->todos as $key => $todo) {
            if ($todo['id'] === $id) {
                unset($this->todos[$key]);
                $this->todos = array_values($this->todos);
                return true;
            }
        }
        return false;
    }
}

/**
 * View: 表示ロジック
 */
class TodoView
{
    public function renderList(array $todos): void
    {
        echo "=== Todo リスト ===\n";
        if (empty($todos)) {
            echo "  Todoはありません\n";
            return;
        }

        foreach ($todos as $todo) {
            $status = $todo['completed'] ? '✓' : ' ';
            echo "  [{$status}] {$todo['id']}. {$todo['title']}\n";
        }
    }

    public function renderTodo(array $todo): void
    {
        echo "=== Todo 詳細 ===\n";
        echo "  ID: {$todo['id']}\n";
        echo "  タイトル: {$todo['title']}\n";
        echo "  ステータス: " . ($todo['completed'] ? '完了' : '未完了') . "\n";
        echo "  作成日時: {$todo['created_at']}\n";
    }

    public function renderMessage(string $message): void
    {
        echo "  {$message}\n";
    }

    public function renderError(string $error): void
    {
        echo "  ❌ エラー: {$error}\n";
    }
}

/**
 * Controller: ModelとViewの仲介
 */
class TodoController
{
    public function __construct(
        private readonly TodoModel $model,
        private readonly TodoView $view,
    ) {}

    public function index(): void
    {
        $todos = $this->model->getTodos();
        $this->view->renderList($todos);
    }

    public function show(int $id): void
    {
        $todo = $this->model->getTodo($id);
        if ($todo === null) {
            $this->view->renderError("ID {$id} のTodoが見つかりません");
            return;
        }
        $this->view->renderTodo($todo);
    }

    public function create(string $title): void
    {
        $todo = $this->model->addTodo($title);
        $this->view->renderMessage("Todo「{$title}」を作成しました");
    }

    public function complete(int $id): void
    {
        if ($this->model->completeTodo($id)) {
            $this->view->renderMessage("ID {$id} のTodoを完了しました");
        } else {
            $this->view->renderError("ID {$id} のTodoが見つかりません");
        }
    }

    public function delete(int $id): void
    {
        if ($this->model->deleteTodo($id)) {
            $this->view->renderMessage("ID {$id} のTodoを削除しました");
        } else {
            $this->view->renderError("ID {$id} のTodoが見つかりません");
        }
    }
}

// テスト
$model = new TodoModel();
$view = new TodoView();
$controller = new TodoController($model, $view);

// Todoの作成
$controller->create('データベース設計');
$controller->create('API実装');
$controller->create('テスト作成');
echo "\n";

// 一覧表示
$controller->index();
echo "\n";

// 詳細表示
$controller->show(1);
echo "\n";

// 完了
$controller->complete(1);
echo "\n";

// 一覧表示（更新後）
$controller->index();
echo "\n";

// 削除
$controller->delete(2);
echo "\n";

// 一覧表示（削除後）
$controller->index();
echo "\n";

echo "=== Phase 4.2 完了 ===\n";
echo "デザインパターンを学習しました！\n";
echo "- Singletonパターン: インスタンスを1つに制限\n";
echo "- Factoryパターン: オブジェクト生成のカプセル化\n";
echo "- Strategyパターン: アルゴリズムの切り替え\n";
echo "- Observerパターン: イベント駆動の通知\n";
echo "- MVCパターン: 関心の分離とアーキテクチャ\n";
