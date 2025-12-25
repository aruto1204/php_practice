# 環境構築ガイド

このドキュメントでは、PHP学習プロジェクトの開発環境をセットアップする手順を説明します。

---

## 目次

1. [前提条件](#前提条件)
2. [PHP のインストール](#php-のインストール)
3. [Composer のインストール](#composer-のインストール)
4. [MySQL のインストール](#mysql-のインストール)
5. [エディタのセットアップ](#エディタのセットアップ)
6. [Git の設定](#git-の設定)
7. [プロジェクトの初期化](#プロジェクトの初期化)
8. [動作確認](#動作確認)
9. [Docker を使った環境構築（オプション）](#docker-を使った環境構築オプション)

---

## 前提条件

### 必要なもの
- macOS / Windows / Linux のいずれか
- インターネット接続
- ターミナル/コマンドプロンプトの基本的な知識

### 推奨スペック
- メモリ: 8GB 以上
- ストレージ: 10GB 以上の空き容量

---

## PHP のインストール

### macOS

#### Homebrew を使用する方法（推奨）

```bash
# Homebrew のインストール（未インストールの場合）
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# PHP 8.3 のインストール
brew install php@8.3

# PHP のバージョン確認
php -v
```

### Windows

#### XAMPP を使用する方法（初心者推奨）

1. [XAMPP 公式サイト](https://www.apachefriends.org/jp/index.html) からダウンロード
2. インストーラーを実行
3. PHP を含むコンポーネントを選択
4. インストール完了後、環境変数 PATH に追加:
   - `C:\xampp\php` を PATH に追加

#### Chocolatey を使用する方法

```powershell
# PowerShell を管理者権限で実行
choco install php -y

# PHP のバージョン確認
php -v
```

### Linux (Ubuntu/Debian)

```bash
# リポジトリの追加
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# PHP 8.3 のインストール
sudo apt install php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-zip

# PHP のバージョン確認
php -v
```

### 必要な PHP 拡張機能

以下の拡張機能が有効になっていることを確認してください:

```bash
# 拡張機能の確認
php -m
```

必須拡張機能:
- `mbstring` - マルチバイト文字列処理
- `pdo_mysql` - MySQL データベース接続
- `curl` - HTTP リクエスト
- `xml` - XML 処理
- `json` - JSON 処理
- `zip` - ZIP アーカイブ処理

---

## Composer のインストール

Composer は PHP の依存関係管理ツールです。

### macOS / Linux

```bash
# Composer のダウンロードとインストール
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# バージョン確認
composer --version
```

### Windows

1. [Composer 公式サイト](https://getcomposer.org/download/) から Windows インストーラーをダウンロード
2. インストーラーを実行
3. PHP のパスを設定（自動検出される場合が多い）

### グローバル設定

```bash
# Composer の高速化
composer global require hirak/prestissimo

# 日本のミラーを使用（高速化）
composer config -g repos.packagist composer https://packagist.jp
```

---

## MySQL のインストール

### macOS

```bash
# Homebrew を使用
brew install mysql@8.0

# MySQL の起動
brew services start mysql@8.0

# 初期設定
mysql_secure_installation
```

### Windows (XAMPP)

XAMPP をインストールしている場合、MySQL は既に含まれています。

1. XAMPP Control Panel を起動
2. MySQL の「Start」ボタンをクリック

### Linux (Ubuntu/Debian)

```bash
# MySQL のインストール
sudo apt install mysql-server

# MySQL の起動
sudo systemctl start mysql
sudo systemctl enable mysql

# 初期設定
sudo mysql_secure_installation
```

### MySQL の初期設定

```bash
# MySQL にログイン
mysql -u root -p

# データベースの作成
CREATE DATABASE php_practice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# ユーザーの作成
CREATE USER 'phpuser'@'localhost' IDENTIFIED BY 'your_secure_password';

# 権限の付与
GRANT ALL PRIVILEGES ON php_practice.* TO 'phpuser'@'localhost';

# 権限の反映
FLUSH PRIVILEGES;

# 終了
EXIT;
```

### phpMyAdmin のインストール（オプション）

```bash
# macOS
brew install phpmyadmin

# Ubuntu/Debian
sudo apt install phpmyadmin
```

---

## エディタのセットアップ

### Visual Studio Code（推奨）

#### 1. インストール

[VS Code 公式サイト](https://code.visualstudio.com/) からダウンロードしてインストール

#### 2. 必須拡張機能のインストール

以下の拡張機能をインストールしてください:

```
- PHP Intelephense (bmewburn.vscode-intelephense-client)
- PHP Debug (xdebug.php-debug)
- PHP CS Fixer (junstyle.php-cs-fixer)
- PHPDoc Generator (neilbrayfield.php-docblocker)
- GitLens (eamodio.gitlens)
```

#### 3. VS Code の設定

`settings.json` に以下を追加:

```json
{
  "php.suggest.basic": false,
  "php.validate.executablePath": "/usr/local/bin/php",
  "intelephense.environment.phpVersion": "8.3.0",
  "files.associations": {
    "*.php": "php"
  },
  "editor.formatOnSave": true,
  "[php]": {
    "editor.defaultFormatter": "junstyle.php-cs-fixer"
  },
  "php-cs-fixer.executablePath": "${extensionPath}/php-cs-fixer.phar",
  "php-cs-fixer.rules": "@PSR12"
}
```

### PHPStorm（代替）

1. [JetBrains 公式サイト](https://www.jetbrains.com/phpstorm/) からダウンロード
2. 学生の場合、無料ライセンスが利用可能
3. インストール後、PHP インタープリタを設定

---

## Git の設定

### Git のインストール

#### macOS
```bash
brew install git
```

#### Windows
[Git 公式サイト](https://git-scm.com/downloads) からダウンロードしてインストール

#### Linux
```bash
sudo apt install git
```

### Git の初期設定

```bash
# ユーザー名とメールアドレスの設定
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"

# エディタの設定（VS Code を使用する場合）
git config --global core.editor "code --wait"

# デフォルトブランチ名の設定
git config --global init.defaultBranch main

# 改行コードの設定（macOS/Linux）
git config --global core.autocrlf input

# 改行コードの設定（Windows）
git config --global core.autocrlf true
```

---

## プロジェクトの初期化

### 1. プロジェクトディレクトリの作成

```bash
# プロジェクトディレクトリへ移動
cd /path/to/php_practice

# Git リポジトリの初期化
git init

# .gitignore の作成
cat > .gitignore << 'EOF'
# Composer
/vendor/
composer.lock

# IDE
.vscode/
.idea/

# OS
.DS_Store
Thumbs.db

# Environment
.env
.env.local

# Logs
*.log

# Cache
/cache/
/tmp/

# Database
*.sqlite
*.db
EOF
```

### 2. Composer の初期化

```bash
# composer.json の作成
composer init

# 基本パッケージのインストール
composer require --dev phpunit/phpunit
composer require --dev phpstan/phpstan
composer require --dev friendsofphp/php-cs-fixer
```

### 3. composer.json の設定

```json
{
  "name": "yourname/php_practice",
  "description": "PHP学習プロジェクト",
  "type": "project",
  "require": {
    "php": ">=8.1"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "phpstan/phpstan": "^1.10",
    "friendsofphp/php-cs-fixer": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Tests\\": "tests/"
    }
  },
  "scripts": {
    "test": "phpunit",
    "phpstan": "phpstan analyse src tests --level=5",
    "cs-fix": "php-cs-fixer fix src",
    "cs-check": "php-cs-fixer fix src --dry-run --diff"
  },
  "config": {
    "optimize-autoloader": true,
    "sort-packages": true
  }
}
```

### 4. ディレクトリ構造の作成

```bash
# 基本ディレクトリの作成
mkdir -p src
mkdir -p tests
mkdir -p public
mkdir -p config
mkdir -p logs

# index.php の作成
cat > public/index.php << 'EOF'
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "Hello, PHP World!" . PHP_EOL;
echo "PHP Version: " . PHP_VERSION . PHP_EOL;
EOF
```

---

## 動作確認

### 1. PHP の動作確認

```bash
# PHP バージョンの確認
php -v

# 拡張機能の確認
php -m | grep -E "pdo|mysql|mbstring"

# テストスクリプトの実行
php public/index.php
```

期待される出力:
```
Hello, PHP World!
PHP Version: 8.3.x
```

### 2. Composer の動作確認

```bash
# Composer バージョン確認
composer --version

# 依存関係のインストール
composer install

# オートローディングの動作確認
composer dump-autoload
```

### 3. MySQL の動作確認

```bash
# MySQL への接続テスト
mysql -u phpuser -p php_practice

# 接続が成功したら
SHOW DATABASES;
EXIT;
```

### 4. Webサーバーの起動

```bash
# PHP ビルトインサーバーの起動
cd public
php -S localhost:8000

# ブラウザで http://localhost:8000 を開く
```

---

## Docker を使った環境構築（オプション）

Docker を使用すると、環境を統一して構築できます。

### 1. Docker と Docker Compose のインストール

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) をダウンロードしてインストール

### 2. docker-compose.yml の作成

```yaml
version: '3.8'

services:
  php:
    image: php:8.3-fpm
    container_name: php_practice_php
    volumes:
      - ./:/var/www/html
    working_dir: /var/www/html
    networks:
      - php_practice

  nginx:
    image: nginx:alpine
    container_name: php_practice_nginx
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - php_practice

  mysql:
    image: mysql:8.0
    container_name: php_practice_mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: php_practice
      MYSQL_USER: phpuser
      MYSQL_PASSWORD: password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - php_practice

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: php_practice_phpmyadmin
    environment:
      PMA_HOST: mysql
      PMA_USER: phpuser
      PMA_PASSWORD: password
    ports:
      - "8081:80"
    depends_on:
      - mysql
    networks:
      - php_practice

networks:
  php_practice:
    driver: bridge

volumes:
  mysql_data:
```

### 3. Docker の起動

```bash
# コンテナの起動
docker-compose up -d

# コンテナの確認
docker-compose ps

# ログの確認
docker-compose logs -f

# コンテナの停止
docker-compose down
```

### 4. Docker での動作確認

- Webサイト: http://localhost:8080
- phpMyAdmin: http://localhost:8081

---

## トラブルシューティング

### PHP が見つからない

```bash
# macOS
export PATH="/usr/local/bin:$PATH"

# Windows（環境変数に追加）
C:\php
C:\xampp\php
```

### Composer が遅い

```bash
# IPv6 を無効化
composer config -g repo.packagist composer https://packagist.org

# キャッシュをクリア
composer clear-cache
```

### MySQL に接続できない

```bash
# MySQL が起動しているか確認
# macOS
brew services list

# Linux
sudo systemctl status mysql

# ポートの確認
netstat -an | grep 3306
```

### Permission Denied エラー

```bash
# ディレクトリの権限を変更
chmod -R 755 /path/to/php_practice
chown -R $USER:$USER /path/to/php_practice
```

---

## 次のステップ

環境構築が完了したら、以下に進んでください:

1. [学習ガイド](learning_guide.md) を読む
2. [進捗管理](progress.md) を確認
3. Phase 1: 基礎編の学習を開始

---

## 参考リソース

- [PHP 公式マニュアル](https://www.php.net/manual/ja/)
- [Composer 公式ドキュメント](https://getcomposer.org/doc/)
- [MySQL 公式ドキュメント](https://dev.mysql.com/doc/)
- [Docker 公式ドキュメント](https://docs.docker.com/)

---

**最終更新日**: 2025年12月25日
