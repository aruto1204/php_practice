<?php

declare(strict_types=1);

/**
 * Phase 4.2 演習課題: デザインパターンの実装
 *
 * この演習では、各デザインパターンを使った実践的なシステムを実装します。
 *
 * 課題:
 * 1. ロギングシステム（Singleton）
 * 2. レポート生成システム（Factory + Strategy）
 * 3. イベント管理システム（Observer）
 * 4. ブログシステム（MVC）
 * 5. Eコマースシステム（総合演習）
 */

echo "=== デザインパターン演習課題 ===\n\n";

// ============================================
// 課題1: ロギングシステム（Singleton）
// ============================================
echo "--- 課題1: ロギングシステム ---\n";

/**
 * ログレベル
 */
enum LogLevel: string
{
    case Debug = 'DEBUG';
    case Info = 'INFO';
    case Warning = 'WARNING';
    case Error = 'ERROR';
    case Critical = 'CRITICAL';

    public function getPrefix(): string
    {
        return match ($this) {
            self::Debug => '🐛',
            self::Info => 'ℹ️',
            self::Warning => '⚠️',
            self::Error => '❌',
            self::Critical => '🔥',
        };
    }
}

/**
 * ロガー（Singleton）
 */
class Logger
{
    private static ?Logger $instance = null;
    private array $logs = [];
    private LogLevel $minLevel = LogLevel::Debug;

    private function __construct() {}
    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function setMinLevel(LogLevel $level): void
    {
        $this->minLevel = $level;
    }

    public function log(LogLevel $level, string $message, array $context = []): void
    {
        // 最小レベル以上のログのみ記録
        $levels = [
            LogLevel::Debug->value => 1,
            LogLevel::Info->value => 2,
            LogLevel::Warning->value => 3,
            LogLevel::Error->value => 4,
            LogLevel::Critical->value => 5,
        ];

        if ($levels[$level->value] < $levels[$this->minLevel->value]) {
            return;
        }

        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->logs[] = $log;
        $this->outputLog($log);
    }

    private function outputLog(array $log): void
    {
        $contextStr = !empty($log['context']) ? ' | ' . json_encode($log['context'], JSON_UNESCAPED_UNICODE) : '';
        echo "{$log['level']->getPrefix()} [{$log['timestamp']}] {$log['level']->value}: {$log['message']}{$contextStr}\n";
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical, $message, $context);
    }

    public function getLogs(?LogLevel $level = null): array
    {
        if ($level === null) {
            return $this->logs;
        }

        return array_filter(
            $this->logs,
            fn(array $log) => $log['level'] === $level
        );
    }

    public function getLogCount(): int
    {
        return count($this->logs);
    }
}

// テスト
$logger = Logger::getInstance();
$logger2 = Logger::getInstance();

echo "logger === logger2: " . ($logger === $logger2 ? 'true' : 'false') . "\n\n";

$logger->debug('デバッグメッセージ', ['variable' => 'value']);
$logger->info('アプリケーション起動');
$logger->warning('メモリ使用率が高くなっています', ['usage' => '85%']);
$logger->error('データベース接続エラー', ['host' => 'localhost', 'error' => 'Connection refused']);
$logger->critical('システムクラッシュ');

echo "\n総ログ数: " . $logger->getLogCount() . "\n";
echo "エラーログ数: " . count($logger->getLogs(LogLevel::Error)) . "\n\n";

// ============================================
// 課題2: レポート生成システム（Factory + Strategy）
// ============================================
echo "--- 課題2: レポート生成システム ---\n";

/**
 * レポートフォーマットインターフェース（Strategy）
 */
interface ReportFormatter
{
    public function format(array $data): string;
}

/**
 * JSONフォーマッター
 */
class JsonFormatter implements ReportFormatter
{
    public function format(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

/**
 * CSVフォーマッター
 */
class CsvFormatter implements ReportFormatter
{
    public function format(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = '';

        // ヘッダー
        $headers = array_keys($data[0]);
        $output .= implode(',', $headers) . "\n";

        // データ行
        foreach ($data as $row) {
            $values = array_map(
                fn($value) => '"' . str_replace('"', '""', (string)$value) . '"',
                array_values($row)
            );
            $output .= implode(',', $values) . "\n";
        }

        return $output;
    }
}

/**
 * HTMLテーブルフォーマッター
 */
class HtmlTableFormatter implements ReportFormatter
{
    public function format(array $data): string
    {
        if (empty($data)) {
            return '<table></table>';
        }

        $output = "<table border='1'>\n";

        // ヘッダー
        $headers = array_keys($data[0]);
        $output .= "  <thead>\n    <tr>\n";
        foreach ($headers as $header) {
            $output .= "      <th>" . htmlspecialchars($header) . "</th>\n";
        }
        $output .= "    </tr>\n  </thead>\n";

        // データ行
        $output .= "  <tbody>\n";
        foreach ($data as $row) {
            $output .= "    <tr>\n";
            foreach ($row as $value) {
                $output .= "      <td>" . htmlspecialchars((string)$value) . "</td>\n";
            }
            $output .= "    </tr>\n";
        }
        $output .= "  </tbody>\n";
        $output .= "</table>";

        return $output;
    }
}

/**
 * レポートフォーマッターファクトリー
 */
class ReportFormatterFactory
{
    public static function create(string $format): ReportFormatter
    {
        return match ($format) {
            'json' => new JsonFormatter(),
            'csv' => new CsvFormatter(),
            'html' => new HtmlTableFormatter(),
            default => throw new \InvalidArgumentException("Unknown format: {$format}"),
        };
    }
}

/**
 * レポートジェネレーター
 */
class ReportGenerator
{
    public function __construct(
        private ReportFormatter $formatter,
    ) {}

    public function setFormatter(ReportFormatter $formatter): void
    {
        $this->formatter = $formatter;
    }

    public function generate(array $data): string
    {
        return $this->formatter->format($data);
    }
}

// テスト
$salesData = [
    ['id' => 1, 'product' => 'ノートPC', 'amount' => 120000, 'quantity' => 5],
    ['id' => 2, 'product' => 'マウス', 'amount' => 3000, 'quantity' => 20],
    ['id' => 3, 'product' => 'キーボード', 'amount' => 8000, 'quantity' => 10],
];

// JSONフォーマット
echo "【JSONフォーマット】\n";
$jsonFormatter = ReportFormatterFactory::create('json');
$reportGen = new ReportGenerator($jsonFormatter);
echo $reportGen->generate($salesData) . "\n\n";

// CSVフォーマット
echo "【CSVフォーマット】\n";
$csvFormatter = ReportFormatterFactory::create('csv');
$reportGen->setFormatter($csvFormatter);
echo $reportGen->generate($salesData) . "\n";

// HTMLフォーマット
echo "【HTMLフォーマット】\n";
$htmlFormatter = ReportFormatterFactory::create('html');
$reportGen->setFormatter($htmlFormatter);
echo $reportGen->generate($salesData) . "\n\n";

// ============================================
// 課題3: イベント管理システム（Observer）
// ============================================
echo "--- 課題3: イベント管理システム ---\n";

/**
 * イベントリスナーインターフェース
 */
interface EventListener
{
    public function handle(string $eventName, mixed $data): void;
}

/**
 * イベントディスパッチャー
 */
class EventDispatcher
{
    private array $listeners = [];

    public function addListener(string $eventName, EventListener $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = $listener;
    }

    public function removeListener(string $eventName, EventListener $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        $key = array_search($listener, $this->listeners[$eventName], true);
        if ($key !== false) {
            unset($this->listeners[$eventName][$key]);
        }
    }

    public function dispatch(string $eventName, mixed $data = null): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            $listener->handle($eventName, $data);
        }
    }
}

/**
 * ユーザー登録リスナー
 */
class UserRegistrationListener implements EventListener
{
    public function handle(string $eventName, mixed $data): void
    {
        echo "  [UserRegistrationListener] 新規ユーザー登録: {$data['username']} ({$data['email']})\n";
        echo "    ウェルカムメールを送信しました\n";
    }
}

/**
 * 通知リスナー
 */
class NotificationListener implements EventListener
{
    public function handle(string $eventName, mixed $data): void
    {
        echo "  [NotificationListener] 通知を送信: {$eventName}\n";
    }
}

/**
 * 監査ログリスナー
 */
class AuditLogListener implements EventListener
{
    private array $auditLog = [];

    public function handle(string $eventName, mixed $data): void
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $eventName,
            'data' => $data,
        ];
        $this->auditLog[] = $entry;
        echo "  [AuditLogListener] 監査ログに記録: {$eventName}\n";
    }

    public function getAuditLog(): array
    {
        return $this->auditLog;
    }
}

/**
 * ユーザーサービス
 */
class UserService
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    public function registerUser(string $username, string $email): void
    {
        // ユーザー登録処理
        echo "ユーザー登録処理: {$username}\n";

        // イベント発火
        $this->eventDispatcher->dispatch('user.registered', [
            'username' => $username,
            'email' => $email,
            'registered_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteUser(string $username): void
    {
        // ユーザー削除処理
        echo "ユーザー削除処理: {$username}\n";

        // イベント発火
        $this->eventDispatcher->dispatch('user.deleted', [
            'username' => $username,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

// テスト
$dispatcher = new EventDispatcher();

// リスナー登録
$registrationListener = new UserRegistrationListener();
$notificationListener = new NotificationListener();
$auditLogListener = new AuditLogListener();

$dispatcher->addListener('user.registered', $registrationListener);
$dispatcher->addListener('user.registered', $notificationListener);
$dispatcher->addListener('user.registered', $auditLogListener);
$dispatcher->addListener('user.deleted', $notificationListener);
$dispatcher->addListener('user.deleted', $auditLogListener);

$userService = new UserService($dispatcher);

// ユーザー登録
$userService->registerUser('alice', 'alice@example.com');
echo "\n";

// ユーザー削除
$userService->deleteUser('bob');
echo "\n";

// ============================================
// 課題4: ブログシステム（MVC）
// ============================================
echo "--- 課題4: ブログシステム ---\n";

/**
 * 記事モデル
 */
class Article
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly string $author,
        public readonly \DateTimeImmutable $publishedAt,
        public readonly array $tags = [],
    ) {}
}

/**
 * ブログモデル
 */
class BlogModel
{
    private array $articles = [];
    private int $nextId = 1;

    public function createArticle(
        string $title,
        string $content,
        string $author,
        array $tags = []
    ): Article {
        $article = new Article(
            id: $this->nextId++,
            title: $title,
            content: $content,
            author: $author,
            publishedAt: new \DateTimeImmutable(),
            tags: $tags,
        );

        $this->articles[$article->id] = $article;
        return $article;
    }

    public function getAllArticles(): array
    {
        return array_values($this->articles);
    }

    public function getArticle(int $id): ?Article
    {
        return $this->articles[$id] ?? null;
    }

    public function getArticlesByTag(string $tag): array
    {
        return array_filter(
            $this->articles,
            fn(Article $article) => in_array($tag, $article->tags, true)
        );
    }

    public function deleteArticle(int $id): bool
    {
        if (isset($this->articles[$id])) {
            unset($this->articles[$id]);
            return true;
        }
        return false;
    }
}

/**
 * ブログビュー
 */
class BlogView
{
    public function renderArticleList(array $articles): void
    {
        echo "=== 記事一覧 ===\n";
        if (empty($articles)) {
            echo "  記事がありません\n";
            return;
        }

        foreach ($articles as $article) {
            echo "  [{$article->id}] {$article->title}\n";
            echo "      著者: {$article->author} | ";
            echo "公開日: {$article->publishedAt->format('Y-m-d')}\n";
            if (!empty($article->tags)) {
                echo "      タグ: " . implode(', ', $article->tags) . "\n";
            }
        }
    }

    public function renderArticle(Article $article): void
    {
        echo "=== 記事詳細 ===\n";
        echo "  ID: {$article->id}\n";
        echo "  タイトル: {$article->title}\n";
        echo "  著者: {$article->author}\n";
        echo "  公開日: {$article->publishedAt->format('Y-m-d H:i:s')}\n";
        if (!empty($article->tags)) {
            echo "  タグ: " . implode(', ', $article->tags) . "\n";
        }
        echo "  内容:\n";
        echo "    " . str_replace("\n", "\n    ", $article->content) . "\n";
    }

    public function renderMessage(string $message): void
    {
        echo "  ✓ {$message}\n";
    }

    public function renderError(string $error): void
    {
        echo "  ✗ エラー: {$error}\n";
    }
}

/**
 * ブログコントローラー
 */
class BlogController
{
    public function __construct(
        private readonly BlogModel $model,
        private readonly BlogView $view,
    ) {}

    public function index(): void
    {
        $articles = $this->model->getAllArticles();
        $this->view->renderArticleList($articles);
    }

    public function show(int $id): void
    {
        $article = $this->model->getArticle($id);
        if ($article === null) {
            $this->view->renderError("記事ID {$id} が見つかりません");
            return;
        }
        $this->view->renderArticle($article);
    }

    public function create(string $title, string $content, string $author, array $tags = []): void
    {
        $article = $this->model->createArticle($title, $content, $author, $tags);
        $this->view->renderMessage("記事「{$title}」を作成しました（ID: {$article->id}）");
    }

    public function filterByTag(string $tag): void
    {
        $articles = $this->model->getArticlesByTag($tag);
        echo "タグ「{$tag}」でフィルタリング:\n";
        $this->view->renderArticleList(array_values($articles));
    }

    public function delete(int $id): void
    {
        if ($this->model->deleteArticle($id)) {
            $this->view->renderMessage("記事ID {$id} を削除しました");
        } else {
            $this->view->renderError("記事ID {$id} が見つかりません");
        }
    }
}

// テスト
$blogModel = new BlogModel();
$blogView = new BlogView();
$blogController = new BlogController($blogModel, $blogView);

// 記事作成
$blogController->create(
    'PHP 8の新機能',
    "PHP 8では多くの新機能が追加されました。\nUnion型、Match式、Enumなどが使えます。",
    'Alice',
    ['PHP', 'プログラミング']
);
$blogController->create(
    'デザインパターン入門',
    "デザインパターンはソフトウェア設計の典型的な解決策です。",
    'Bob',
    ['デザインパターン', 'プログラミング']
);
$blogController->create(
    'データベース最適化',
    "データベースクエリの最適化について解説します。",
    'Charlie',
    ['データベース', 'SQL']
);
echo "\n";

// 一覧表示
$blogController->index();
echo "\n";

// 詳細表示
$blogController->show(1);
echo "\n";

// タグでフィルタリング
$blogController->filterByTag('プログラミング');
echo "\n";

echo "=== すべての演習課題が完了しました ===\n";
echo "デザインパターンを実践的に活用できました！\n";
