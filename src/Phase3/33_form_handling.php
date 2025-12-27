<?php

declare(strict_types=1);

/**
 * Phase 3.4: フォーム処理とバリデーション - フォームデータの取得
 *
 * このファイルでは、フォームデータの安全な取得方法を学習します。
 *
 * 学習内容:
 * - $_GET、$_POST、$_REQUESTの使い分け
 * - フォームデータの取得とサニタイゼーション
 * - filter_input()の使用
 * - マルチバイト文字の扱い
 * - 配列データの処理
 */

echo "=== Phase 3.4: フォームデータの取得 ===\n\n";

echo "--- 1. スーパーグローバル変数の概要 ---\n";
/**
 * フォームデータの取得に使用するスーパーグローバル変数:
 *
 * $_GET    : URLクエリパラメータ（例: ?name=value）
 * $_POST   : POSTリクエストのボディデータ
 * $_REQUEST: $_GET、$_POST、$_COOKIEの統合（非推奨）
 * $_FILES  : アップロードされたファイル情報
 */

echo "✅ スーパーグローバル変数の種類:\n";
echo "  - \$_GET: URLパラメータ（検索、フィルタ、ページング）\n";
echo "  - \$_POST: フォーム送信（データ作成・更新・削除）\n";
echo "  - \$_REQUEST: 統合（セキュリティ上非推奨）\n";
echo "  - \$_FILES: ファイルアップロード\n\n";

echo "--- 2. GETリクエストとPOSTリクエストの使い分け ---\n";
echo "✅ GETメソッド（$_GET）:\n";
echo "  - 用途: データの取得・検索・フィルタリング\n";
echo "  - 特徴: URLに含まれる、ブックマーク可能、冪等性あり\n";
echo "  - 例: 検索、ページング、カテゴリフィルタ\n\n";

echo "✅ POSTメソッド（$_POST）:\n";
echo "  - 用途: データの作成・更新・削除\n";
echo "  - 特徴: URLに含まれない、大量データ可能、冪等性なし\n";
echo "  - 例: ログイン、ユーザー登録、コメント投稿\n\n";

echo "--- 3. フォームデータの基本的な取得 ---\n";

// シミュレーション: GETリクエスト
$_GET = [
    'search' => 'PHP',
    'page' => '2',
    'category' => 'programming'
];

echo "✅ GETデータの取得例:\n";
echo "URL: ?search=PHP&page=2&category=programming\n\n";

// 基本的な取得（危険）
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$category = $_GET['category'] ?? '';

echo "取得した値:\n";
echo "  - 検索ワード: $search\n";
echo "  - ページ: $page\n";
echo "  - カテゴリ: $category\n\n";

// シミュレーション: POSTリクエスト
$_POST = [
    'username' => 'testuser',
    'email' => 'test@example.com',
    'password' => 'SecurePass123!',
    'age' => '25',
    'terms' => 'on'
];

echo "✅ POSTデータの取得例:\n";
echo "フォーム送信データ\n\n";

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$age = $_POST['age'] ?? '';

echo "取得した値:\n";
echo "  - ユーザー名: $username\n";
echo "  - メール: $email\n";
echo "  - 年齢: $age\n\n";

echo "--- 4. filter_input()による安全なデータ取得 ---\n";
/**
 * filter_input()は、スーパーグローバル変数から直接データを取得し、
 * フィルタリングを同時に行うことができます。
 *
 * 利点:
 * - スーパーグローバル変数の直接参照を避ける
 * - バリデーションとサニタイゼーションを同時に実行
 * - より安全なコード
 */

echo "✅ filter_input()の基本的な使用:\n\n";

/**
 * フィルタヘルパークラス
 */
class InputFilter
{
    /**
     * 文字列を取得（サニタイズ）
     */
    public static function getString(int $type, string $name, string $default = ''): string
    {
        $value = filter_input($type, $name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return $value !== null && $value !== false ? trim($value) : $default;
    }

    /**
     * 整数を取得（バリデーション）
     */
    public static function getInt(int $type, string $name, int $default = 0, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        $options = [
            'options' => [
                'default' => $default,
                'min_range' => $min,
                'max_range' => $max
            ]
        ];

        $value = filter_input($type, $name, FILTER_VALIDATE_INT, $options);
        return $value !== false ? $value : $default;
    }

    /**
     * メールアドレスを取得（バリデーション）
     */
    public static function getEmail(int $type, string $name): ?string
    {
        $value = filter_input($type, $name, FILTER_VALIDATE_EMAIL);
        return $value !== false ? $value : null;
    }

    /**
     * URLを取得（バリデーション）
     */
    public static function getUrl(int $type, string $name): ?string
    {
        $value = filter_input($type, $name, FILTER_VALIDATE_URL);
        return $value !== false ? $value : null;
    }

    /**
     * 真偽値を取得
     */
    public static function getBool(int $type, string $name, bool $default = false): bool
    {
        $value = filter_input($type, $name, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $value !== null ? $value : $default;
    }
}

// 使用例（シミュレーション用に$_GETを直接使用）
echo "検索ワード（文字列）: ";
$searchTerm = filter_var($_GET['search'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
echo "$searchTerm\n";

echo "ページ番号（整数）: ";
$pageNum = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1]
]);
echo "$pageNum\n\n";

echo "--- 5. 配列データの処理 ---\n";
/**
 * フォームから複数の値（チェックボックス、複数選択など）を
 * 受け取る場合の処理方法
 */

// シミュレーション: 配列データ
$_POST['interests'] = ['programming', 'design', 'music'];
$_POST['skills'] = [
    'php' => 'advanced',
    'javascript' => 'intermediate',
    'python' => 'beginner'
];

echo "✅ チェックボックス（配列）の処理:\n";
$interests = $_POST['interests'] ?? [];

// 配列であることを確認
if (is_array($interests)) {
    echo "選択された興味:\n";
    foreach ($interests as $interest) {
        // 各値をサニタイズ
        $safe = htmlspecialchars($interest, ENT_QUOTES, 'UTF-8');
        echo "  - $safe\n";
    }
} else {
    echo "無効なデータ形式\n";
}
echo "\n";

echo "✅ 連想配列の処理:\n";
$skills = $_POST['skills'] ?? [];

if (is_array($skills)) {
    echo "スキルレベル:\n";
    foreach ($skills as $skill => $level) {
        $safeSkill = htmlspecialchars($skill, ENT_QUOTES, 'UTF-8');
        $safeLevel = htmlspecialchars($level, ENT_QUOTES, 'UTF-8');
        echo "  - $safeSkill: $safeLevel\n";
    }
}
echo "\n";

echo "--- 6. filter_input_array()による一括処理 ---\n";
/**
 * 複数のフィールドを一度にフィルタリング
 */

echo "✅ filter_input_array()の使用例:\n";

// フィルタ定義
$filters = [
    'search' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    'page' => [
        'filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 1, 'default' => 1]
    ],
    'category' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
];

// シミュレーション用（実際はfilter_input_array(INPUT_GET, $filters)を使用）
$filteredData = [];
foreach ($filters as $key => $filter) {
    if (isset($_GET[$key])) {
        if (is_array($filter)) {
            $filteredData[$key] = filter_var($_GET[$key], $filter['filter'], ['options' => $filter['options']]);
        } else {
            $filteredData[$key] = filter_var($_GET[$key], $filter);
        }
    }
}

echo "フィルタ済みデータ:\n";
print_r($filteredData);
echo "\n";

echo "--- 7. マルチバイト文字（日本語）の扱い ---\n";

// シミュレーション: 日本語を含むデータ
$_POST['comment'] = '  これはテストコメントです。  ';
$_POST['name'] = '山田　太郎';

/**
 * マルチバイト対応のトリム関数
 */
if (!function_exists('mb_trim')) {
    function mb_trim(string $str): string
    {
        return preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $str);
    }
}

echo "✅ マルチバイト文字の処理:\n";

$comment = $_POST['comment'] ?? '';
$name = $_POST['name'] ?? '';

// トリム（マルチバイト対応）
$comment = mb_trim($comment);
$name = mb_trim($name);

// 文字数チェック（マルチバイト対応）
$commentLength = mb_strlen($comment);
$nameLength = mb_strlen($name);

echo "コメント: $comment (長さ: {$commentLength}文字)\n";
echo "名前: $name (長さ: {$nameLength}文字)\n\n";

echo "--- 8. フォームリクエストクラスの実装 ---\n";

/**
 * フォームリクエストを扱うクラス
 */
class FormRequest
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * GETリクエストから作成
     */
    public static function fromGet(): self
    {
        return new self($_GET);
    }

    /**
     * POSTリクエストから作成
     */
    public static function fromPost(): self
    {
        return new self($_POST);
    }

    /**
     * 値を取得（デフォルト値付き）
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * 文字列を取得（サニタイズ）
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        if (!is_string($value)) {
            return $default;
        }
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * 整数を取得（バリデーション）
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : $default;
    }

    /**
     * メールアドレスを取得（バリデーション）
     */
    public function getEmail(string $key): ?string
    {
        $value = $this->get($key);
        if ($value === null) {
            return null;
        }
        $filtered = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $filtered !== false ? $filtered : null;
    }

    /**
     * 配列を取得
     */
    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);
        return is_array($value) ? $value : $default;
    }

    /**
     * 真偽値を取得
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        if ($value === null) {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * 特定のキーのみを取得
     */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $result[$key] = $this->data[$key];
            }
        }
        return $result;
    }

    /**
     * 特定のキーを除外
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->data, array_flip($keys));
    }

    /**
     * すべてのデータを取得
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * キーが存在するか
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
}

echo "✅ FormRequestクラスの使用例:\n";

$request = new FormRequest($_POST);

echo "ユーザー名: " . $request->getString('username') . "\n";
echo "メール: " . ($request->getEmail('email') ?? '無効') . "\n";
echo "年齢: " . $request->getInt('age') . "\n";
echo "利用規約: " . ($request->getBool('terms') ? '同意' : '未同意') . "\n\n";

// 特定のフィールドのみ取得
echo "ユーザー情報のみ:\n";
$userInfo = $request->only(['username', 'email', 'age']);
print_r($userInfo);
echo "\n";

echo "--- 9. セキュリティのベストプラクティス ---\n";
echo "✅ フォームデータ取得の推奨事項:\n";
echo "  1. filter_input()を使用してデータを取得\n";
echo "  2. 常にデフォルト値を設定（Null合体演算子 ?? を活用）\n";
echo "  3. データ型を明示的にバリデーション\n";
echo "  4. 出力時に必ずエスケープ処理\n";
echo "  5. \$_REQUESTは使用しない（どこから来たか不明確）\n";
echo "  6. マルチバイト文字はmb_*関数を使用\n";
echo "  7. 配列データは型チェックを必ず行う\n\n";

echo "❌ 避けるべき方法:\n";
echo "  1. スーパーグローバル変数を直接使用\n";
echo "  2. デフォルト値なしでデータを取得\n";
echo "  3. 型チェックなしで処理\n";
echo "  4. エスケープなしで出力\n\n";

echo "--- 10. まとめ ---\n";
echo "フォームデータの安全な取得方法:\n";
echo "  ✓ filter_input()でバリデーションとサニタイゼーションを同時実行\n";
echo "  ✓ FormRequestクラスで型安全なデータアクセス\n";
echo "  ✓ 配列データは必ず型チェック\n";
echo "  ✓ マルチバイト文字に対応\n";
echo "  ✓ セキュリティを常に意識\n\n";

echo "=== Phase 3.4: フォームデータの取得 完了 ===\n";
