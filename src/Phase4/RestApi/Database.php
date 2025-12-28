<?php

declare(strict_types=1);

namespace Phase4\RestApi;

use PDO;

/**
 * データベース接続クラス（Singletonパターン）
 *
 * REST APIシステム全体で単一のデータベース接続を共有
 */
class Database
{
    private static ?PDO $pdo = null;

    /**
     * コンストラクタをprivateにしてインスタンス化を防ぐ
     */
    private function __construct()
    {
    }

    /**
     * データベース接続を取得
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../../../database/rest_api.db';
            $dbDir = dirname($dbPath);

            // データベースディレクトリが存在しない場合は作成
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            self::$pdo = new PDO('sqlite:' . $dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // 外部キー制約を有効化
            self::$pdo->exec('PRAGMA foreign_keys = ON');

            // テーブルを初期化
            self::initializeTables();
        }

        return self::$pdo;
    }

    /**
     * テーブルの初期化
     */
    private static function initializeTables(): void
    {
        self::$pdo->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                full_name TEXT NOT NULL,
                is_admin INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )
        ');

        self::$pdo->exec('
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT NOT NULL,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0,
                category TEXT NOT NULL,
                image_url TEXT,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )
        ');

        self::$pdo->exec('
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                status TEXT NOT NULL,
                total_amount REAL NOT NULL,
                shipping_address TEXT NOT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');

        self::$pdo->exec('
            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price REAL NOT NULL,
                subtotal REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ');

        // インデックスの作成
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)');
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_products_category ON products(category)');
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_products_is_active ON products(is_active)');
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)');
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)');
        self::$pdo->exec('CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id)');
    }

    /**
     * すべてのテーブルをクリア（テスト用）
     */
    public static function clearAllTables(): void
    {
        $pdo = self::getConnection();
        $pdo->exec('DELETE FROM order_items');
        $pdo->exec('DELETE FROM orders');
        $pdo->exec('DELETE FROM products');
        $pdo->exec('DELETE FROM users');
    }

    /**
     * 接続をリセット（テスト用）
     */
    public static function resetConnection(): void
    {
        self::$pdo = null;
    }
}
