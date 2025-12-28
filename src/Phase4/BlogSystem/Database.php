<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem;

use PDO;
use PDOException;

/**
 * データベース接続クラス
 *
 * Singletonパターンでデータベース接続を管理
 */
class Database
{
    private static ?PDO $pdo = null;

    /**
     * プライベートコンストラクタ（Singleton）
     */
    private function __construct()
    {
    }

    /**
     * PDO接続を取得
     *
     * @return PDO PDO接続
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            try {
                $dbPath = __DIR__ . '/../../../database/blog.db';
                $dbDir = dirname($dbPath);

                // ディレクトリが存在しない場合は作成
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }

                self::$pdo = new PDO(
                    'sqlite:' . $dbPath,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

                // 外部キー制約を有効化
                self::$pdo->exec('PRAGMA foreign_keys = ON');
            } catch (PDOException $e) {
                throw new PDOException('データベース接続エラー: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    /**
     * テーブルを初期化
     */
    public static function initializeTables(): void
    {
        $pdo = self::getConnection();

        // usersテーブル
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(100) NOT NULL,
                bio TEXT,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            )
        ');

        // categoriesテーブル
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) UNIQUE NOT NULL,
                slug VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                created_at DATETIME NOT NULL
            )
        ');

        // tagsテーブル
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) UNIQUE NOT NULL,
                slug VARCHAR(50) UNIQUE NOT NULL,
                created_at DATETIME NOT NULL
            )
        ');

        // postsテーブル
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                category_id INTEGER,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(200) UNIQUE NOT NULL,
                content TEXT NOT NULL,
                excerpt VARCHAR(500),
                status VARCHAR(20) NOT NULL DEFAULT "draft",
                published_at DATETIME,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ');

        // post_tagsテーブル
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS post_tags (
                post_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                PRIMARY KEY (post_id, tag_id),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            )
        ');

        // commentsテーブル
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                user_id INTEGER,
                author_name VARCHAR(100) NOT NULL,
                author_email VARCHAR(100) NOT NULL,
                content TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT "pending",
                created_at DATETIME NOT NULL,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ');
    }

    /**
     * テーブルをクリア（テスト用）
     */
    public static function clearTables(): void
    {
        $pdo = self::getConnection();

        $pdo->exec('DELETE FROM comments');
        $pdo->exec('DELETE FROM post_tags');
        $pdo->exec('DELETE FROM posts');
        $pdo->exec('DELETE FROM tags');
        $pdo->exec('DELETE FROM categories');
        $pdo->exec('DELETE FROM users');

        // AUTO_INCREMENTをリセット
        $pdo->exec('DELETE FROM sqlite_sequence');
    }
}
