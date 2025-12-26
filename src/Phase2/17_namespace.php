<?php

declare(strict_types=1);

/**
 * Phase 2.3: 名前空間の基礎
 *
 * このファイルでは、PHPの名前空間について学習します。
 * 名前空間は、クラス、関数、定数を論理的にグループ化し、名前の衝突を防ぐための仕組みです。
 *
 * 学習内容:
 * 1. 名前空間の定義
 * 2. 名前空間の階層構造
 * 3. use文とエイリアス
 * 4. グローバル名前空間
 * 5. 名前解決のルール
 * 6. 関数と定数の名前空間
 */

echo "=== Phase 2.3: 名前空間の基礎 ===\n\n";

// ============================================================
// 1. 名前空間の基礎
// ============================================================

echo "--- 1. 名前空間の基礎 ---\n\n";

/**
 * 名前空間の定義
 *
 * namespace キーワードを使って名前空間を定義します。
 * 名前空間は、ファイルの先頭（declare文の後）に記述します。
 */

// 例1: シンプルな名前空間
// namespace App;

/**
 * 名前空間の必要性
 *
 * なぜ名前空間が必要なのか？
 * 1. 名前の衝突を防ぐ（異なるライブラリが同じクラス名を使う場合）
 * 2. コードの整理と可読性の向上
 * 3. モジュール化とコンポーネント分割
 */

echo "名前空間の主な目的:\n";
echo "- 異なるコンポーネント間でのクラス名の衝突を防ぐ\n";
echo "- コードを論理的に整理する\n";
echo "- 大規模プロジェクトでのコードの保守性を向上させる\n\n";

// ============================================================
// 2. 名前空間を使ったクラスの定義（サンプル）
// ============================================================

echo "--- 2. 名前空間を使ったクラスの定義 ---\n\n";

/**
 * 名前空間付きクラスのシミュレーション
 *
 * 実際の名前空間付きクラスは別ファイルで定義されますが、
 * ここでは概念を示すためにコード例を記述します。
 */

// ファイル: src/App/Models/User.php
/*
<?php
namespace App\Models;

class User
{
    public function __construct(
        private string $name,
        private string $email,
    ) {}

    public function getInfo(): string
    {
        return "User: {$this->name} ({$this->email})";
    }
}
*/

// ファイル: src/App/Controllers/UserController.php
/*
<?php
namespace App\Controllers;

use App\Models\User;

class UserController
{
    public function createUser(string $name, string $email): User
    {
        return new User($name, $email);
    }
}
*/

echo "【コード例】\n";
echo "名前空間 App\\Models に User クラスを定義\n";
echo "名前空間 App\\Controllers に UserController クラスを定義\n";
echo "UserController は use文で User クラスをインポート\n\n";

// ============================================================
// 3. use文とエイリアス
// ============================================================

echo "--- 3. use文とエイリアス ---\n\n";

/**
 * use文の基本
 *
 * use文は、名前空間付きのクラスを現在のスコープで使えるようにします。
 * これにより、完全修飾名（FQCN: Fully Qualified Class Name）を毎回書く必要がなくなります。
 */

// 例: use文の使い方
/*
use App\Models\User;                    // シンプルなインポート
use App\Services\UserService;           // 別の名前空間からインポート
use App\Models\User as UserModel;       // エイリアスを使ったインポート
use App\Repositories\{                  // グループ化されたインポート
    UserRepository,
    PostRepository,
    CommentRepository
};
*/

echo "【use文の種類】\n";
echo "1. 基本的なインポート: use App\\Models\\User;\n";
echo "2. エイリアス: use App\\Models\\User as UserModel;\n";
echo "3. グループ化: use App\\Models\\{User, Post, Comment};\n";
echo "4. 関数のインポート: use function App\\Helpers\\formatDate;\n";
echo "5. 定数のインポート: use const App\\Constants\\MAX_USERS;\n\n";

/**
 * エイリアスの使用例
 *
 * 異なる名前空間に同じクラス名がある場合、エイリアスを使って区別します。
 */

// 例: エイリアスで名前の衝突を回避
/*
use App\Models\User as AppUser;
use External\Library\User as LibraryUser;

$appUser = new AppUser('Taro', 'taro@example.com');
$libUser = new LibraryUser('Hanako', 'hanako@example.com');
*/

echo "【エイリアスの活用】\n";
echo "異なるライブラリに同じUser クラスがある場合:\n";
echo "- use App\\Models\\User as AppUser;\n";
echo "- use External\\Library\\User as LibraryUser;\n\n";

// ============================================================
// 4. 名前空間の階層構造
// ============================================================

echo "--- 4. 名前空間の階層構造 ---\n\n";

/**
 * 名前空間は階層的に整理できます
 *
 * プロジェクトの構造を反映した階層的な名前空間を使うことで、
 * コードの整理と検索が容易になります。
 */

echo "【一般的な名前空間の階層】\n";
echo "App\\                      # アプリケーションのルート\n";
echo "├── Models\\               # データモデル\n";
echo "│   ├── User\n";
echo "│   ├── Post\n";
echo "│   └── Comment\n";
echo "├── Controllers\\          # コントローラー\n";
echo "│   ├── UserController\n";
echo "│   └── PostController\n";
echo "├── Services\\             # ビジネスロジック\n";
echo "│   ├── AuthService\n";
echo "│   └── EmailService\n";
echo "├── Repositories\\         # データアクセス層\n";
echo "│   ├── UserRepository\n";
echo "│   └── PostRepository\n";
echo "└── Utils\\                # ユーティリティ\n";
echo "    ├── StringHelper\n";
echo "    └── DateHelper\n\n";

// ============================================================
// 5. 名前解決のルール
// ============================================================

echo "--- 5. 名前解決のルール ---\n\n";

/**
 * 名前解決の3つのルール
 *
 * 1. 相対名（Relative name）
 *    - 現在の名前空間からの相対パス
 *    - 例: new User() → 現在の名前空間の User
 *
 * 2. 修飾名（Qualified name）
 *    - サブ名前空間を含む名前
 *    - 例: new Models\User() → 現在の名前空間\Models\User
 *
 * 3. 完全修飾名（Fully qualified name）
 *    - 先頭にバックスラッシュを付けた絶対パス
 *    - 例: new \App\Models\User() → グローバルからの完全パス
 */

echo "【名前解決の種類】\n\n";

echo "1. 相対名（Relative）\n";
echo "   現在の名前空間: App\\Controllers\n";
echo "   コード: new User()\n";
echo "   解決: App\\Controllers\\User\n\n";

echo "2. 修飾名（Qualified）\n";
echo "   現在の名前空間: App\\Controllers\n";
echo "   コード: new Models\\User()\n";
echo "   解決: App\\Controllers\\Models\\User\n\n";

echo "3. 完全修飾名（Fully Qualified）\n";
echo "   現在の名前空間: 任意\n";
echo "   コード: new \\App\\Models\\User()\n";
echo "   解決: App\\Models\\User（常に同じ）\n\n";

// ============================================================
// 6. グローバル名前空間
// ============================================================

echo "--- 6. グローバル名前空間 ---\n\n";

/**
 * グローバル名前空間
 *
 * 名前空間が指定されていないコードは、グローバル名前空間に属します。
 * PHPの組み込みクラス（DateTime、PDOなど）もグローバル名前空間にあります。
 */

// 名前空間内からグローバルクラスを使う場合は、先頭に \ を付けます

// 例: 名前空間内でグローバルクラスを使用
/*
namespace App\Services;

class DateService
{
    public function getCurrentDate(): \DateTime
    {
        return new \DateTime();  // グローバルの DateTime クラス
    }

    public function getDbConnection(): \PDO
    {
        return new \PDO(/* ... */);  // グローバルの PDO クラス
    }
}
*/

echo "【グローバル名前空間のアクセス】\n";
echo "名前空間内で組み込みクラスを使う場合:\n";
echo "- new \\DateTime()  # 先頭に \\ を付ける\n";
echo "- new \\PDO()       # 先頭に \\ を付ける\n";
echo "- new \\Exception() # 先頭に \\ を付ける\n\n";

echo "または、use文でインポート:\n";
echo "- use DateTime;\n";
echo "- new DateTime()   # 普通に使える\n\n";

// ============================================================
// 7. 関数と定数の名前空間
// ============================================================

echo "--- 7. 関数と定数の名前空間 ---\n\n";

/**
 * 関数と定数も名前空間を持つことができます
 */

// 例: 名前空間付き関数
/*
namespace App\Helpers;

function formatDate(\DateTime $date): string
{
    return $date->format('Y-m-d H:i:s');
}

function sanitizeString(string $input): string
{
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}
*/

// 例: 名前空間付き定数
/*
namespace App\Constants;

const MAX_USERS = 100;
const DEFAULT_LANGUAGE = 'ja';
const API_VERSION = '1.0.0';
*/

// 使用例
/*
namespace App\Controllers;

use function App\Helpers\formatDate;
use const App\Constants\MAX_USERS;

class UserController
{
    public function showUsers(): void
    {
        $now = new \DateTime();
        echo formatDate($now);
        echo "最大ユーザー数: " . MAX_USERS;
    }
}
*/

echo "【関数の名前空間】\n";
echo "定義: namespace App\\Helpers; function formatDate(...) {}\n";
echo "使用: use function App\\Helpers\\formatDate;\n\n";

echo "【定数の名前空間】\n";
echo "定義: namespace App\\Constants; const MAX_USERS = 100;\n";
echo "使用: use const App\\Constants\\MAX_USERS;\n\n";

// ============================================================
// 8. 実践例: 名前空間を使ったプロジェクト構成
// ============================================================

echo "--- 8. 実践例: 名前空間を使ったプロジェクト構成 ---\n\n";

/**
 * 典型的なプロジェクトでの名前空間の使い方
 */

echo "【プロジェクト構成例】\n\n";

echo "ファイル構成:\n";
echo "src/\n";
echo "├── Models/\n";
echo "│   └── User.php          # namespace App\\Models;\n";
echo "├── Repositories/\n";
echo "│   └── UserRepository.php # namespace App\\Repositories;\n";
echo "├── Services/\n";
echo "│   └── UserService.php   # namespace App\\Services;\n";
echo "└── Controllers/\n";
echo "    └── UserController.php # namespace App\\Controllers;\n\n";

echo "依存関係の流れ:\n";
echo "UserController\n";
echo "    ↓ (uses)\n";
echo "UserService\n";
echo "    ↓ (uses)\n";
echo "UserRepository\n";
echo "    ↓ (uses)\n";
echo "User Model\n\n";

// ============================================================
// 9. ベストプラクティス
// ============================================================

echo "--- 9. ベストプラクティス ---\n\n";

echo "【名前空間のベストプラクティス】\n\n";

echo "1. PSR-4 オートローディング標準に従う\n";
echo "   - 名前空間とディレクトリ構造を一致させる\n";
echo "   - 例: App\\Models\\User → src/Models/User.php\n\n";

echo "2. 意味のある名前空間を使う\n";
echo "   ✅ 良い例: App\\Services\\EmailService\n";
echo "   ❌ 悪い例: App\\Stuff\\Thing\n\n";

echo "3. 名前空間は単数形を使う\n";
echo "   ✅ 良い例: App\\Model\\User\n";
echo "   ❌ 悪い例: App\\Models\\User（複数形も一般的ですが、統一が重要）\n\n";

echo "4. use文はアルファベット順に並べる\n";
echo "   use App\\Models\\Post;\n";
echo "   use App\\Models\\User;\n";
echo "   use App\\Services\\AuthService;\n\n";

echo "5. エイリアスは必要な場合のみ使う\n";
echo "   - 名前の衝突がある場合\n";
echo "   - 長い名前を短くしたい場合\n\n";

echo "6. グローバルクラスは明示的に \\ を付けるか use する\n";
echo "   ✅ new \\DateTime()\n";
echo "   ✅ use DateTime; new DateTime();\n";
echo "   ❌ new DateTime() # 名前空間内では曖昧\n\n";

// ============================================================
// 10. よくある間違い
// ============================================================

echo "--- 10. よくある間違い ---\n\n";

echo "【よくある間違いと解決方法】\n\n";

echo "❌ 間違い1: namespace宣言の前にコードを書く\n";
echo "<?php\n";
echo "echo 'Hello';  // これはNG\n";
echo "namespace App;\n\n";

echo "✅ 正しい:\n";
echo "<?php\n";
echo "declare(strict_types=1);\n";
echo "namespace App;\n\n";

echo "---\n\n";

echo "❌ 間違い2: use文をクラス定義の後に書く\n";
echo "<?php\n";
echo "namespace App;\n";
echo "class User {}\n";
echo "use App\\Models\\Post;  // これはNG\n\n";

echo "✅ 正しい:\n";
echo "<?php\n";
echo "namespace App;\n";
echo "use App\\Models\\Post;\n";
echo "class User {}\n\n";

echo "---\n\n";

echo "❌ 間違い3: グローバルクラスを忘れる\n";
echo "namespace App\\Services;\n";
echo "new DateTime();  // App\\Services\\DateTime を探してしまう\n\n";

echo "✅ 正しい:\n";
echo "namespace App\\Services;\n";
echo "new \\DateTime();  // グローバルの DateTime\n\n";

// ============================================================
// まとめ
// ============================================================

echo "\n=== まとめ ===\n\n";

echo "名前空間の重要なポイント:\n";
echo "1. 名前空間は namespace キーワードで定義する\n";
echo "2. use文でクラス、関数、定数をインポートできる\n";
echo "3. エイリアス（as）で名前の衝突を回避できる\n";
echo "4. 名前解決には3つのルールがある（相対、修飾、完全修飾）\n";
echo "5. グローバルクラスは先頭に \\ を付けるか use する\n";
echo "6. PSR-4標準に従い、名前空間とディレクトリ構造を一致させる\n\n";

echo "次のステップ:\n";
echo "- 18_autoloading.php で Composer オートローディングを学習\n";
echo "- exercises/08_namespace_practice.php で実践的な演習に挑戦\n\n";

echo "=== 学習完了 ===\n";
