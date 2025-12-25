# コーディング規約

このプロジェクトで使用するPHPコーディング規約をまとめたドキュメントです。

---

## 基本方針

本プロジェクトでは、以下の標準規約に準拠します：

- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloader
- **PSR-12**: Extended Coding Style

加えて、モダンPHPのベストプラクティスを採用します。

---

## 1. ファイルとディレクトリ

### 1.1 ファイル名

```php
// クラスファイル: PascalCase
User.php
UserRepository.php
BlogPostController.php

// その他のファイル: snake_case
config.php
database_connection.php
helper_functions.php
```

### 1.2 ディレクトリ構造

```
src/
├── Controllers/      # コントローラー
├── Models/          # モデル
├── Services/        # ビジネスロジック
├── Repositories/    # データアクセス層
├── Validators/      # バリデーション
└── Utils/           # ユーティリティ

public/              # 公開ディレクトリ
├── css/
├── js/
├── images/
└── index.php

config/              # 設定ファイル
tests/               # テストコード
docs/                # ドキュメント
```

### 1.3 ファイルの構造

```php
<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use Exception;

/**
 * User モデルクラス
 */
class User
{
    // クラスの内容
}
```

---

## 2. 命名規則

### 2.1 クラス名

```php
// PascalCase（各単語の先頭を大文字）
class User {}
class BlogPost {}
class UserRepository {}
class HttpClient {}
```

### 2.2 メソッド名

```php
class User
{
    // camelCase（最初の単語は小文字、以降は大文字）
    public function getName(): string {}
    public function setEmail(string $email): void {}
    public function isActive(): bool {}
    public function hasPermission(string $permission): bool {}
}
```

### 2.3 変数名

```php
// camelCase
$userName = "太郎";
$isActive = true;
$totalCount = 100;

// 定数は UPPER_CASE
const MAX_LOGIN_ATTEMPTS = 5;
const DATABASE_HOST = 'localhost';

// プライベートプロパティは camelCase
class User
{
    private string $firstName;
    private string $lastName;
}
```

### 2.4 真偽値（Boolean）

```php
// is, has, can で始める
$isActive = true;
$hasPermission = false;
$canEdit = true;
$shouldNotify = false;

// メソッドも同様
public function isAdmin(): bool {}
public function hasAccess(): bool {}
public function canDelete(): bool {}
```

---

## 3. 型宣言

### 3.1 厳格な型宣言

すべてのPHPファイルの先頭に記述:

```php
<?php

declare(strict_types=1);
```

### 3.2 パラメータと戻り値の型宣言

```php
// ✅ 良い例: 型を明示
function calculateTotal(float $price, int $quantity): float
{
    return $price * $quantity;
}

// ✅ 良い例: Union型（PHP 8.0+）
function processValue(int|float $value): string
{
    return (string) $value;
}

// ✅ 良い例: Nullable型
function findUser(?int $id): ?User
{
    if ($id === null) {
        return null;
    }
    // ユーザー検索処理
}

// ❌ 悪い例: 型宣言なし
function calculate($a, $b)
{
    return $a + $b;
}
```

### 3.3 プロパティの型宣言

```php
class User
{
    // ✅ 良い例
    private int $id;
    private string $name;
    private ?string $email = null;
    private array $roles = [];
    
    // PHP 8.1+: readonly プロパティ
    public readonly int $id;
    public readonly string $createdAt;
}
```

---

## 4. クラス設計

### 4.1 コンストラクタプロモーション（PHP 8.0+）

```php
// ✅ 良い例: コンストラクタプロモーション
class User
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
    ) {}
}

// 従来の書き方（PHP 7.x）
class User
{
    private int $id;
    private string $name;
    private string $email;
    
    public function __construct(int $id, string $name, string $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }
}
```

### 4.2 アクセス修飾子

```php
class User
{
    // public: どこからでもアクセス可能（必要最小限に）
    public function getName(): string {}
    
    // protected: 継承先からアクセス可能
    protected function validateEmail(string $email): bool {}
    
    // private: このクラス内のみアクセス可能（推奨）
    private function hashPassword(string $password): string {}
}
```

### 4.3 単一責任の原則

```php
// ❌ 悪い例: 複数の責任を持つ
class User
{
    public function save(): void {}
    public function sendEmail(): void {}
    public function generateReport(): void {}
}

// ✅ 良い例: 責任を分離
class User
{
    public function save(): void {}
}

class EmailService
{
    public function sendToUser(User $user): void {}
}

class ReportGenerator
{
    public function generateForUser(User $user): void {}
}
```

---

## 5. メソッド設計

### 5.1 メソッドの長さ

- 1メソッドは20-30行以内を目安に
- 複雑な処理は分割する

```php
// ❌ 悪い例: 長すぎるメソッド
public function processUser(): void
{
    // 50行以上の処理...
}

// ✅ 良い例: 適切に分割
public function processUser(): void
{
    $this->validateUser();
    $this->saveUser();
    $this->sendNotification();
}

private function validateUser(): void {}
private function saveUser(): void {}
private function sendNotification(): void {}
```

### 5.2 早期リターン

```php
// ❌ 悪い例: ネストが深い
public function canEdit(User $user): bool
{
    if ($user->isActive()) {
        if ($user->hasPermission('edit')) {
            if ($user->isVerified()) {
                return true;
            }
        }
    }
    return false;
}

// ✅ 良い例: 早期リターン
public function canEdit(User $user): bool
{
    if (!$user->isActive()) {
        return false;
    }
    
    if (!$user->hasPermission('edit')) {
        return false;
    }
    
    if (!$user->isVerified()) {
        return false;
    }
    
    return true;
}
```

---

## 6. 配列とコレクション

### 6.1 配列の宣言

```php
// ✅ 良い例: 短い配列構文
$fruits = ['apple', 'banana', 'orange'];
$user = [
    'id' => 1,
    'name' => '太郎',
    'email' => 'taro@example.com',
];

// ❌ 悪い例: 古い構文
$fruits = array('apple', 'banana', 'orange');
```

### 6.2 配列の操作

```php
// map, filter, reduce を活用
$numbers = [1, 2, 3, 4, 5];

// map: 各要素を変換
$doubled = array_map(fn($n) => $n * 2, $numbers);

// filter: 条件に合う要素を抽出
$even = array_filter($numbers, fn($n) => $n % 2 === 0);

// reduce: 集計
$sum = array_reduce($numbers, fn($carry, $n) => $carry + $n, 0);
```

---

## 7. 制御構造

### 7.1 if/else

```php
// ✅ 良い例: 適切なインデント
if ($condition) {
    // 処理
} elseif ($otherCondition) {
    // 処理
} else {
    // 処理
}

// ✅ 良い例: 三項演算子（簡単な場合）
$status = $isActive ? 'active' : 'inactive';

// ✅ 良い例: null合体演算子
$name = $_POST['name'] ?? 'ゲスト';
```

### 7.2 match式（PHP 8.0+）

```php
// ✅ 良い例: match（厳密な比較）
$result = match ($status) {
    'draft' => '下書き',
    'published' => '公開済み',
    'archived' => 'アーカイブ',
    default => '不明',
};

// 従来の switch
switch ($status) {
    case 'draft':
        $result = '下書き';
        break;
    case 'published':
        $result = '公開済み';
        break;
    default:
        $result = '不明';
}
```

### 7.3 ループ

```php
// foreach: 配列の反復
foreach ($users as $user) {
    echo $user->getName();
}

// foreach: キーと値
foreach ($data as $key => $value) {
    echo "$key: $value";
}

// for: カウンターが必要な場合
for ($i = 0; $i < count($items); $i++) {
    // 処理
}
```

---

## 8. エラーハンドリング

### 8.1 例外の使用

```php
// ✅ 良い例: 例外をスロー
class UserRepository
{
    public function find(int $id): User
    {
        $user = $this->fetchFromDatabase($id);
        
        if ($user === null) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }
        
        return $user;
    }
}

// ✅ 良い例: 例外をキャッチ
try {
    $user = $userRepository->find($id);
} catch (UserNotFoundException $e) {
    // 適切なエラー処理
    error_log($e->getMessage());
    return null;
}
```

### 8.2 カスタム例外

```php
// アプリケーション固有の例外を作成
namespace App\Exceptions;

class UserNotFoundException extends \Exception
{
    public function __construct(int $userId)
    {
        parent::__construct("User with ID {$userId} not found");
    }
}
```

---

## 9. データベース

### 9.1 PDOの使用

```php
// ✅ 良い例: プリペアドステートメント
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

// ❌ 悪い例: SQLインジェクションの危険性
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($sql);
```

### 9.2 トランザクション

```php
try {
    $pdo->beginTransaction();
    
    // 複数のクエリ実行
    $pdo->exec("INSERT INTO ...");
    $pdo->exec("UPDATE ...");
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## 10. セキュリティ

### 10.1 入力のエスケープ

```php
// ✅ 良い例: HTML出力時のエスケープ
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
echo "<p>{$name}</p>";

// ✅ 良い例: URL出力時のエスケープ
$url = urlencode($_GET['redirect']);
echo "<a href='/redirect?url={$url}'>リンク</a>";
```

### 10.2 パスワード処理

```php
// ✅ 良い例: パスワードのハッシュ化
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ✅ 良い例: パスワードの検証
if (password_verify($inputPassword, $hashedPassword)) {
    // ログイン成功
}
```

### 10.3 CSRF対策

```php
// トークン生成
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// フォームに埋め込み
<input type="hidden" name="csrf_token" 
       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

// トークン検証
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

---

## 11. コメントとドキュメント

### 11.1 PHPDocコメント

```php
/**
 * ユーザーを検索する
 *
 * @param int $id ユーザーID
 * @return User|null ユーザーオブジェクト、見つからない場合はnull
 * @throws DatabaseException データベースエラーが発生した場合
 */
public function findUser(int $id): ?User
{
    // 実装
}
```

### 11.2 インラインコメント

```php
// ✅ 良い例: 複雑な処理の説明
// ユーザーの最終ログイン日時を更新
// セッションタイムアウトを延長するため
$user->updateLastLoginAt(new DateTime());

// ❌ 悪い例: 自明なコメント
// 名前を取得
$name = $user->getName();
```

---

## 12. テスト

### 12.1 テストメソッドの命名

```php
class UserTest extends TestCase
{
    // test_メソッド名_期待される結果
    public function test_create_user_with_valid_data_succeeds(): void
    {
        // Arrange
        $data = ['name' => 'John', 'email' => 'john@example.com'];
        
        // Act
        $user = User::create($data);
        
        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->getName());
    }
}
```

---

## 13. コードフォーマット

### 13.1 インデント

- スペース4つを使用（タブは使用しない）
- ネストは最大3-4レベルまで

### 13.2 行の長さ

- 1行は120文字以内を推奨
- 必要に応じて改行

```php
// ✅ 良い例: 適切な改行
$result = $this->userRepository->findByEmailAndStatus(
    $email,
    $status,
    $includeDeleted
);
```

### 13.3 空行

```php
class User
{
    private int $id;
    private string $name;
    
    // プロパティとメソッドの間に1行
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
    
    // メソッド間に1行
    public function getName(): string
    {
        return $this->name;
    }
}
```

---

## 14. PHP CS Fixer 設定

`.php-cs-fixer.php`:

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'strict_param' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
```

---

## 15. チェックリスト

コードレビュー時の確認事項:

- [ ] `declare(strict_types=1)` を記述している
- [ ] すべての関数・メソッドに型宣言がある
- [ ] PSR-12に準拠している
- [ ] セキュリティ対策が実装されている
- [ ] エラーハンドリングが適切
- [ ] テストコードがある
- [ ] PHPDocコメントがある
- [ ] 命名規則に従っている
- [ ] 単一責任の原則を守っている
- [ ] マジックナンバーを定数化している

---

## 参考資料

- [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)
- [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHP: The Right Way](https://phptherightway.com/)

---

**最終更新日**: 2025年12月25日
**適用開始日**: 2025年12月25日
