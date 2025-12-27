<?php

declare(strict_types=1);

/**
 * Phase 3.3: セキュリティ - CSRF（クロスサイトリクエストフォージェリ）対策
 *
 * このファイルでは、CSRF攻撃の危険性と対策方法を学習します。
 *
 * 学習内容:
 * - CSRFとは
 * - CSRFトークンによる対策
 * - トークンの生成と検証
 * - SameSite Cookie属性
 * - カスタムヘッダーによる対策
 */

echo "=== Phase 3.3: CSRF（クロスサイトリクエストフォージェリ）対策 ===\n\n";

echo "--- 1. CSRFとは ---\n";
/**
 * CSRF（Cross-Site Request Forgery）は、ユーザーが意図しない
 * リクエストを強制的に実行させる攻撃手法です。
 *
 * 攻撃の流れ:
 * 1. ユーザーがWebサイトAにログインしている
 * 2. 攻撃者が用意した悪意のあるWebサイトBにアクセス
 * 3. サイトBから、サイトAへのリクエストが自動的に送信される
 * 4. ブラウザは、サイトAのクッキー（セッションID）を自動的に送信
 * 5. サイトAは正規のリクエストと判断し、処理を実行してしまう
 *
 * 影響:
 * - パスワード変更
 * - メールアドレス変更
 * - 送金処理
 * - データの削除
 */
echo "CSRFは、ユーザーが意図しないリクエストを強制的に実行させる攻撃です。\n";
echo "CSRFトークンを使用して、正規のフォームからのリクエストか検証します。\n\n";

echo "--- 2. CSRF攻撃の例 ---\n";
echo "❌ 脆弱なコード例:\n";
echo "// パスワード変更処理（CSRFトークンなし）\n";
echo "if (\$_POST['new_password']) {\n";
echo "    changePassword(\$_SESSION['user_id'], \$_POST['new_password']);\n";
echo "}\n\n";

echo "攻撃者が用意する悪意のあるHTML:\n";
echo "<form action=\"https://example.com/change-password\" method=\"POST\">\n";
echo "    <input type=\"hidden\" name=\"new_password\" value=\"hacked123\">\n";
echo "</form>\n";
echo "<script>document.forms[0].submit();</script>\n\n";

echo "→ ユーザーが攻撃者のサイトにアクセスすると、自動的にフォームが送信され、\n";
echo "   パスワードが変更されてしまいます。\n\n";

echo "--- 3. CSRFトークンによる対策（基本） ---\n";
/**
 * CSRFトークンは、フォームごとに一意のトークンを生成し、
 * リクエスト時にそのトークンを検証することで、CSRF攻撃を防ぎます。
 */

/**
 * CSRFトークンクラス
 */
class CsrfToken
{
    /**
     * トークンを生成してセッションに保存
     */
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 暗号学的に安全な乱数を生成
        $token = bin2hex(random_bytes(32));

        // セッションに保存
        $_SESSION['csrf_token'] = $token;

        return $token;
    }

    /**
     * トークンを検証
     */
    public static function validate(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションにトークンが存在するか
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // タイミング攻撃を防ぐため、hash_equals()を使用
        $isValid = hash_equals($_SESSION['csrf_token'], $token);

        // 使用済みトークンを削除（ワンタイムトークン）
        // 注: 実際のアプリケーションでは、トークンの有効期限を設定することも重要
        // unset($_SESSION['csrf_token']);

        return $isValid;
    }

    /**
     * トークンをHTMLの隠しフィールドとして出力
     */
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * トークンをmetaタグとして出力（AJAX用）
     */
    public static function meta(): string
    {
        $token = self::generate();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

echo "✅ CSRFトークンの生成\n";
$token = CsrfToken::generate();
echo "トークン: $token\n";
echo "セッションに保存されました\n\n";

echo "✅ フォームにCSRFトークンを埋め込む\n";
echo "<form method=\"POST\" action=\"/change-password\">\n";
echo "    " . CsrfToken::field() . "\n";
echo "    <input type=\"password\" name=\"new_password\">\n";
echo "    <button type=\"submit\">パスワード変更</button>\n";
echo "</form>\n\n";

echo "--- 4. トークンの検証 ---\n";
echo "✅ リクエスト時のトークン検証\n";

// シミュレーション: POSTリクエストを受け取った場合
$simulatedPost = [
    'csrf_token' => $_SESSION['csrf_token'] ?? '',
    'new_password' => 'newpass123'
];

echo "受信したトークン: {$simulatedPost['csrf_token']}\n";

if (CsrfToken::validate($simulatedPost['csrf_token'])) {
    echo "✓ トークンが有効です - 処理を実行します\n";
    // パスワード変更処理など
} else {
    echo "✗ トークンが無効です - リクエストを拒否します\n";
}
echo "\n";

echo "--- 5. 無効なトークンのテスト ---\n";
$invalidToken = 'invalid_token_12345';
echo "無効なトークン: $invalidToken\n";

if (CsrfToken::validate($invalidToken)) {
    echo "✓ トークンが有効です\n";
} else {
    echo "✗ トークンが無効です - CSRF攻撃を防ぎました\n";
}
echo "\n";

echo "--- 6. AJAX用のCSRFトークン ---\n";
/**
 * AJAXリクエストでは、metaタグやカスタムHTTPヘッダーで
 * トークンを送信します。
 */

echo "✅ metaタグでトークンを設定\n";
echo "<head>\n";
echo "    " . CsrfToken::meta() . "\n";
echo "</head>\n\n";

echo "JavaScript（フェッチAPI）:\n";
echo <<<'JS'
const token = document.querySelector('meta[name="csrf-token"]').content;

fetch('/api/change-password', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
    },
    body: JSON.stringify({ newPassword: 'newpass123' })
});
JS;
echo "\n\n";

echo "PHPでカスタムヘッダーを検証:\n";
echo <<<'PHP'
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!CsrfToken::validate($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}
PHP;
echo "\n\n";

echo "--- 7. フォームごとのトークン管理 ---\n";
/**
 * より高度なCSRF対策として、フォームごとに異なるトークンを生成し、
 * 有効期限を設定することができます。
 */

class AdvancedCsrfToken
{
    private const TOKEN_LIFETIME = 3600; // 1時間

    /**
     * フォーム名付きトークンを生成
     */
    public static function generateForForm(string $formName): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $expiry = time() + self::TOKEN_LIFETIME;

        // フォームごとにトークンを保存
        $_SESSION['csrf_tokens'][$formName] = [
            'token' => $token,
            'expiry' => $expiry
        ];

        return $token;
    }

    /**
     * フォーム名付きトークンを検証
     */
    public static function validateForForm(string $formName, string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // トークンが存在するか
        if (!isset($_SESSION['csrf_tokens'][$formName])) {
            return false;
        }

        $storedData = $_SESSION['csrf_tokens'][$formName];

        // 有効期限をチェック
        if (time() > $storedData['expiry']) {
            unset($_SESSION['csrf_tokens'][$formName]);
            return false;
        }

        // トークンを検証
        $isValid = hash_equals($storedData['token'], $token);

        // 使用済みトークンを削除
        if ($isValid) {
            unset($_SESSION['csrf_tokens'][$formName]);
        }

        return $isValid;
    }
}

echo "✅ フォームごとのトークン生成\n";
$loginToken = AdvancedCsrfToken::generateForForm('login');
$passwordChangeToken = AdvancedCsrfToken::generateForForm('password_change');

echo "ログインフォーム用トークン: $loginToken\n";
echo "パスワード変更フォーム用トークン: $passwordChangeToken\n\n";

echo "✅ フォームごとのトークン検証\n";
if (AdvancedCsrfToken::validateForForm('login', $loginToken)) {
    echo "✓ ログインフォームのトークンが有効です\n";
} else {
    echo "✗ ログインフォームのトークンが無効です\n";
}

// 別のフォームのトークンで検証を試みる
if (AdvancedCsrfToken::validateForForm('login', $passwordChangeToken)) {
    echo "✓ トークンが有効です\n";
} else {
    echo "✗ 異なるフォームのトークンは無効です\n";
}
echo "\n";

echo "--- 8. SameSite Cookie属性による対策 ---\n";
/**
 * SameSite属性は、クッキーを送信するタイミングを制限します。
 *
 * - Strict: 同一サイトからのリクエストのみクッキーを送信（最も厳格）
 * - Lax: 同一サイト + トップレベルナビゲーション（デフォルト）
 * - None: 全てのリクエストでクッキーを送信（Secure属性必須）
 */

echo "✅ SameSite属性の設定例\n";
echo "// セッションクッキーにSameSite属性を設定\n";
echo "session_set_cookie_params([\n";
echo "    'lifetime' => 0,\n";
echo "    'path' => '/',\n";
echo "    'domain' => '',\n";
echo "    'secure' => true,      // HTTPS通信のみ\n";
echo "    'httponly' => true,    // JavaScriptからアクセス不可\n";
echo "    'samesite' => 'Strict' // 同一サイトのみ\n";
echo "]);\n";
echo "session_start();\n\n";

echo "SameSite属性の比較:\n";
echo "  - Strict: 最も安全だが、外部サイトからのリンクで問題が発生する場合がある\n";
echo "  - Lax: バランスが取れている（推奨）\n";
echo "  - None: CSRF保護なし（必ずSecure属性とCSRFトークンを併用）\n\n";

echo "--- 9. その他のCSRF対策 ---\n";
echo "✅ 重要な操作には再認証を要求\n";
echo "  - パスワード変更、メールアドレス変更などの重要な操作には、\n";
echo "    現在のパスワードの入力を要求する\n\n";

echo "✅ Refererヘッダーのチェック（補助的な対策）\n";
echo "  - Refererヘッダーが自サイトからのリクエストか確認\n";
echo "  - ただし、Refererヘッダーは偽装可能なので、主要な対策にはしない\n\n";

echo "✅ カスタムヘッダーによる対策\n";
echo "  - AJAXリクエストでカスタムヘッダー（X-Requested-With: XMLHttpRequest）を送信\n";
echo "  - クロスオリジンではカスタムヘッダーを送信できない（Same-Origin Policy）\n\n";

// Refererチェックの例
function checkReferer(string $expectedHost): bool
{
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return false;
    }

    $refererHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    return $refererHost === $expectedHost;
}

echo "--- 10. CSRFトークンのベストプラクティス ---\n";
echo "✅ 推奨される実装:\n";
echo "  1. すべての状態変更操作（POST、PUT、DELETE）にCSRFトークンを使用\n";
echo "  2. トークンは暗号学的に安全な乱数生成器で生成（random_bytes）\n";
echo "  3. トークンの検証には hash_equals() を使用（タイミング攻撃対策）\n";
echo "  4. トークンに有効期限を設定\n";
echo "  5. SameSite=Lax または Strict を設定\n";
echo "  6. 重要な操作には再認証を要求\n";
echo "  7. HTTPSを使用（Secure属性、SameSite=Noneの場合は必須）\n\n";

echo "❌ 避けるべき方法:\n";
echo "  1. GETリクエストで状態を変更する\n";
echo "  2. トークンなしで重要な操作を許可\n";
echo "  3. 予測可能なトークン（time()、uniqid()など）\n";
echo "  4. トークンの検証に == を使用（タイミング攻撃の危険性）\n";
echo "  5. Refererヘッダーのみに依存\n\n";

echo "--- 11. 実装例：完全なフォーム処理 ---\n";

/**
 * セキュアなフォーム処理クラス
 */
class SecureFormHandler
{
    /**
     * フォームのHTMLを生成
     */
    public static function renderPasswordChangeForm(): string
    {
        $html = '<form method="POST" action="/change-password">' . "\n";
        $html .= '    ' . CsrfToken::field() . "\n";
        $html .= '    <label>現在のパスワード:</label>' . "\n";
        $html .= '    <input type="password" name="current_password" required>' . "\n";
        $html .= '    <label>新しいパスワード:</label>' . "\n";
        $html .= '    <input type="password" name="new_password" required>' . "\n";
        $html .= '    <button type="submit">変更</button>' . "\n";
        $html .= '</form>';
        return $html;
    }

    /**
     * フォームの送信を処理
     */
    public static function handlePasswordChange(): void
    {
        // CSRFトークンを検証
        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfToken::validate($token)) {
            http_response_code(403);
            echo "エラー: 不正なリクエストです\n";
            return;
        }

        // 現在のパスワードを確認（再認証）
        // $currentPassword = $_POST['current_password'] ?? '';
        // if (!verifyCurrentPassword($currentPassword)) {
        //     echo "エラー: 現在のパスワードが間違っています\n";
        //     return;
        // }

        // 新しいパスワードをバリデーション
        // $newPassword = $_POST['new_password'] ?? '';
        // if (strlen($newPassword) < 8) {
        //     echo "エラー: パスワードは8文字以上にしてください\n";
        //     return;
        // }

        // パスワードを変更
        // changePassword($newPassword);

        echo "✓ パスワードが変更されました\n";
    }
}

echo "✅ セキュアなフォームの例\n";
echo SecureFormHandler::renderPasswordChangeForm() . "\n\n";

echo "=== Phase 3.3: CSRF対策 完了 ===\n";
