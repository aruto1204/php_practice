# トラブルシューティングガイド

PHP学習プロジェクトで遭遇する可能性のある問題と解決方法をまとめたガイドです。

---

## 目次

1. [環境構築に関する問題](#環境構築に関する問題)
2. [PHP実行時の問題](#php実行時の問題)
3. [データベース接続の問題](#データベース接続の問題)
4. [Composerの問題](#composerの問題)
5. [コーディングに関する問題](#コーディングに関する問題)
6. [セキュリティに関する問題](#セキュリティに関する問題)
7. [パフォーマンスの問題](#パフォーマンスの問題)

---

## 環境構築に関する問題

### ❌ PHP が見つからない

**エラー**:
```
command not found: php
```

**解決方法**:

**macOS**:
```bash
# PATH の確認
echo $PATH

# Homebrew で PHP をインストール
brew install php@8.3

# PATH に追加（.zshrc または .bash_profile に追加）
export PATH="/usr/local/opt/php@8.3/bin:$PATH"

# 再読み込み
source ~/.zshrc
```

**Windows**:
1. PHP のインストールパスを確認（例: `C:\php`）
2. システム環境変数 PATH に追加
3. コマンドプロンプトを再起動

**Linux**:
```bash
# PHP がインストールされているか確認
which php

# インストールされていない場合
sudo apt update
sudo apt install php8.3-cli
```

---

### ❌ PHP拡張機能が見つからない

**エラー**:
```
PHP Fatal error: Uncaught Error: Call to undefined function mysqli_connect()
```

**解決方法**:

**macOS**:
```bash
# 必要な拡張機能をインストール
brew install php@8.3
pecl install xdebug

# php.ini の場所を確認
php --ini

# php.ini に拡張機能を追加
extension=mysqli
extension=pdo_mysql
```

**Ubuntu/Debian**:
```bash
# 拡張機能のインストール
sudo apt install php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl

# 拡張機能の確認
php -m
```

**Windows (XAMPP)**:
1. `C:\xampp\php\php.ini` を開く
2. 以下の行のコメントを外す:
```ini
extension=mysqli
extension=pdo_mysql
extension=mbstring
```
3. Apacheを再起動

---

### ❌ ポートが既に使用されている

**エラー**:
```
Failed to listen on localhost:8000 (reason: Address already in use)
```

**解決方法**:

**使用中のポートを確認**:
```bash
# macOS / Linux
lsof -i :8000

# Windows
netstat -ano | findstr :8000
```

**別のポートを使用**:
```bash
php -S localhost:8080
```

**プロセスを終了**:
```bash
# macOS / Linux
kill -9 [PID]

# Windows
taskkill /PID [PID] /F
```

---

## PHP実行時の問題

### ❌ Parse Error（構文エラー）

**エラー**:
```
Parse error: syntax error, unexpected '}' in /path/to/file.php on line 10
```

**原因と解決**:

1. **閉じ括弧の不一致**
```php
// ❌ 悪い例
function test() {
    if (true) {
        echo "test";
    // } が足りない
}

// ✅ 良い例
function test() {
    if (true) {
        echo "test";
    }
}
```

2. **セミコロンの欠落**
```php
// ❌ 悪い例
$name = "太郎"  // セミコロンがない
echo $name;

// ✅ 良い例
$name = "太郎";
echo $name;
```

3. **クォートの不一致**
```php
// ❌ 悪い例
$text = "Hello';

// ✅ 良い例
$text = "Hello";
```

---

### ❌ Fatal Error: Call to undefined function

**エラー**:
```
Fatal error: Uncaught Error: Call to undefined function myFunction()
```

**原因と解決**:

1. **関数が定義されていない**
```php
// 関数を呼び出す前に定義する
function myFunction() {
    return "Hello";
}

echo myFunction(); // OK
```

2. **名前空間が異なる**
```php
namespace App\Utils;

function helper() {
    return "Help";
}

// 別のファイルから呼び出す場合
use function App\Utils\helper;

echo helper(); // OK
```

3. **オートロードの問題**
```bash
# Composerのオートロードを再生成
composer dump-autoload
```

---

### ❌ Warning: Undefined array key

**エラー**:
```
Warning: Undefined array key "name" in /path/to/file.php on line 5
```

**原因と解決**:

```php
// ❌ 悪い例
$user = ['email' => 'test@example.com'];
echo $user['name']; // Warning

// ✅ 良い例1: isset() でチェック
if (isset($user['name'])) {
    echo $user['name'];
}

// ✅ 良い例2: null合体演算子
echo $user['name'] ?? 'デフォルト値';

// ✅ 良い例3: array_key_exists()
if (array_key_exists('name', $user)) {
    echo $user['name'];
}
```

---

## データベース接続の問題

### ❌ SQLSTATE[HY000] [2002] Connection refused

**エラー**:
```
PDOException: SQLSTATE[HY000] [2002] Connection refused
```

**原因と解決**:

**1. MySQL が起動していない**
```bash
# macOS
brew services start mysql

# Linux
sudo systemctl start mysql
sudo systemctl enable mysql

# Windows (XAMPP)
# XAMPP Control Panel で MySQL を起動
```

**2. ホスト名が間違っている**
```php
// ❌ 悪い例
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');

// ✅ 良い例（127.0.0.1を試す）
$pdo = new PDO('mysql:host=127.0.0.1;dbname=test', 'root', '');
```

**3. ポート番号の確認**
```php
// デフォルト以外のポートを使用している場合
$pdo = new PDO(
    'mysql:host=localhost;port=3307;dbname=test',
    'root',
    'password'
);
```

---

### ❌ SQLSTATE[HY000] [1045] Access denied

**エラー**:
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**原因と解決**:

**1. パスワードが間違っている**
```bash
# MySQL のパスワードをリセット
mysql -u root -p

# パスワードを変更
ALTER USER 'root'@'localhost' IDENTIFIED BY '新しいパスワード';
FLUSH PRIVILEGES;
```

**2. ユーザーが存在しない**
```sql
-- ユーザーを作成
CREATE USER 'phpuser'@'localhost' IDENTIFIED BY 'password';

-- 権限を付与
GRANT ALL PRIVILEGES ON php_practice.* TO 'phpuser'@'localhost';
FLUSH PRIVILEGES;
```

---

### ❌ SQLSTATE[42S02]: Base table or view not found

**エラー**:
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'test.users' doesn't exist
```

**原因と解決**:

**1. テーブルが作成されていない**
```sql
-- テーブルを作成
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**2. データベース名が間違っている**
```php
// データベース名を確認
$pdo = new PDO(
    'mysql:host=localhost;dbname=正しいDB名',
    'user',
    'password'
);
```

---

## Composerの問題

### ❌ Composer が遅い

**問題**: パッケージのインストールに時間がかかる

**解決方法**:

```bash
# IPv6 を無効化
composer config -g repo.packagist composer https://packagist.org

# prestissimo プラグインをインストール（並列ダウンロード）
composer global require hirak/prestissimo

# キャッシュをクリア
composer clear-cache

# 日本のミラーを使用
composer config -g repos.packagist composer https://packagist.jp
```

---

### ❌ Class not found after Composer install

**エラー**:
```
Fatal error: Class 'App\MyClass' not found
```

**原因と解決**:

**1. オートロードが更新されていない**
```bash
composer dump-autoload
```

**2. composer.json の autoload 設定を確認**
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

**3. 名前空間とディレクトリ構造の確認**
```
src/
  └── Models/
      └── User.php  # namespace App\Models;
```

---

## コーディングに関する問題

### ❌ セッションが動作しない

**問題**: セッションにデータを保存できない

**解決方法**:

```php
// ❌ 悪い例
$_SESSION['user'] = 'John'; // session_start() がない

// ✅ 良い例
session_start();
$_SESSION['user'] = 'John';

// ヘッダー出力前に session_start() を呼ぶ
<?php
session_start(); // 最初に呼ぶ
?>
<!DOCTYPE html>
<html>
...
```

**セッションディレクトリの権限確認**:
```bash
# セッションディレクトリの確認
php -i | grep session.save_path

# 権限の付与
sudo chmod 777 /var/lib/php/sessions
```

---

### ❌ 日本語が文字化けする

**問題**: 日本語が正しく表示されない

**解決方法**:

**1. ファイルのエンコーディング**
- ファイルをUTF-8（BOMなし）で保存

**2. HTML のmetaタグ**
```html
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タイトル</title>
</head>
```

**3. データベース接続時**
```php
$pdo = new PDO(
    'mysql:host=localhost;dbname=test;charset=utf8mb4',
    'user',
    'password'
);
```

**4. mbstring の設定**
```php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
```

---

### ❌ ファイルアップロードが失敗する

**エラー**:
```
Warning: move_uploaded_file(): Unable to move
```

**原因と解決**:

**1. ディレクトリの権限**
```bash
# アップロード先ディレクトリの権限を変更
chmod 755 /path/to/uploads
chown www-data:www-data /path/to/uploads
```

**2. php.ini の設定**
```ini
; アップロード可能なファイルサイズ
upload_max_filesize = 10M
post_max_size = 10M
```

**3. 正しいコード**
```php
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "アップロード成功";
    } else {
        echo "アップロード失敗";
    }
}
```

---

## セキュリティに関する問題

### ⚠️ SQLインジェクション脆弱性

**危険なコード**:
```php
// ❌ 絶対にやってはいけない
$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $sql);
```

**安全なコード**:
```php
// ✅ プリペアドステートメントを使用
$id = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();
```

---

### ⚠️ XSS（クロスサイトスクリプティング）脆弱性

**危険なコード**:
```php
// ❌ 危険
$name = $_POST['name'];
echo "<p>ようこそ、$name さん</p>";
```

**安全なコード**:
```php
// ✅ エスケープする
$name = $_POST['name'];
echo "<p>ようこそ、" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . " さん</p>";

// または
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
echo "<p>ようこそ、{$name} さん</p>";
```

---

### ⚠️ CSRF（クロスサイトリクエストフォージェリ）脆弱性

**安全な実装**:
```php
// トークンの生成
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// フォームにトークンを埋め込む
?>
<form method="POST">
    <input type="hidden" name="csrf_token" 
           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="text" name="data">
    <button type="submit">送信</button>
</form>
<?php

// トークンの検証
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token mismatch');
    }
    // 処理を続ける
}
```

---

## パフォーマンスの問題

### 🐌 ページの読み込みが遅い

**原因と解決**:

**1. N+1 クエリ問題**
```php
// ❌ 悪い例（N+1問題）
$users = $pdo->query('SELECT * FROM users')->fetchAll();
foreach ($users as $user) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $posts = $stmt->fetchAll();
}

// ✅ 良い例（JOIN を使用）
$sql = 'SELECT users.*, posts.* 
        FROM users 
        LEFT JOIN posts ON users.id = posts.user_id';
$result = $pdo->query($sql)->fetchAll();
```

**2. OPcache の有効化**
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

**3. 不要なクエリの削減**
```php
// データベースクエリをキャッシュ
$cacheKey = 'user_' . $userId;
if (!$user = apcu_fetch($cacheKey)) {
    $user = $pdo->query("SELECT * FROM users WHERE id = $userId")->fetch();
    apcu_store($cacheKey, $user, 3600);
}
```

---

## ヘルプを求める前のチェックリスト

問題が解決しない場合、以下を確認してから質問してください:

- [ ] エラーメッセージを完全に読んだ
- [ ] Google / Stack Overflow で検索した
- [ ] 公式ドキュメントを確認した
- [ ] エラーログを確認した（`tail -f /var/log/php/error.log`）
- [ ] 最小限のコードで再現できるか確認した
- [ ] PHP / MySQL のバージョンを確認した
- [ ] 権限の問題ではないか確認した

---

## 有用なデバッグコマンド

```bash
# PHP のバージョンと設定確認
php -v
php -i
php --ini

# 拡張機能の確認
php -m

# 構文チェック
php -l file.php

# Composer の診断
composer diagnose

# MySQL の接続確認
mysql -u root -p -e "SELECT VERSION();"

# ポート使用状況の確認
lsof -i :8000

# プロセスの確認
ps aux | grep php
```

---

## 参考リソース

- [PHP Manual - エラー処理](https://www.php.net/manual/ja/book.errorfunc.php)
- [Stack Overflow - PHP](https://stackoverflow.com/questions/tagged/php)
- [Qiita - PHP](https://qiita.com/tags/php)

---

**最終更新日**: 2025年12月25日
**問題が解決しない場合**: issueを作成するか、コミュニティで質問してください
