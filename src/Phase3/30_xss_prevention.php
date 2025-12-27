<?php

declare(strict_types=1);

/**
 * Phase 3.3: セキュリティ - XSS（クロスサイトスクリプティング）対策
 *
 * このファイルでは、XSS攻撃の危険性と対策方法を学習します。
 *
 * 学習内容:
 * - XSSとは
 * - XSSの種類（反射型、格納型、DOM型）
 * - htmlspecialchars()による対策
 * - コンテキストに応じたエスケープ
 * - Content Security Policy (CSP)
 */

echo "=== Phase 3.3: XSS（クロスサイトスクリプティング）対策 ===\n\n";

echo "--- 1. XSSとは ---\n";
/**
 * XSS（Cross-Site Scripting）は、Webアプリケーションに悪意のある
 * スクリプトを注入し、他のユーザーのブラウザで実行させる攻撃手法です。
 *
 * 影響:
 * - クッキーの盗難（セッションハイジャック）
 * - ページの改ざん
 * - フィッシング攻撃
 * - マルウェアの配布
 */
echo "XSSは、悪意のあるスクリプトを注入する攻撃です。\n";
echo "HTMLに出力する際は、必ずエスケープ処理を行います。\n\n";

echo "--- 2. XSSの種類 ---\n";
echo "1. 反射型XSS: URLパラメータなどから直接スクリプトを実行\n";
echo "2. 格納型XSS: データベースに保存されたスクリプトが実行される\n";
echo "3. DOM型XSS: JavaScriptでDOMを操作する際の脆弱性\n\n";

echo "--- 3. 危険なコード例（絶対に使用禁止！） ---\n";
/**
 * ❌ 危険: ユーザー入力をそのままHTMLに出力
 */

$userInput = '<script>alert("XSS攻撃！")</script>';

echo "❌ 危険な例: エスケープなしの出力\n";
echo "ユーザー入力: $userInput\n";
echo "→ このままHTMLに出力すると、スクリプトが実行されます\n\n";

// HTMLコンテキストでの危険な例
$userName = '<script>alert("XSS")</script>';
$dangerousHtml = "こんにちは、$userName さん";
echo "危険なHTML: $dangerousHtml\n";
echo "→ ブラウザで表示すると、JavaScriptが実行されてしまいます\n\n";

echo "--- 4. htmlspecialchars()による対策（基本） ---\n";
/**
 * ✅ 安全: htmlspecialchars()でエスケープ
 *
 * htmlspecialchars()は、特殊文字をHTMLエンティティに変換します。
 * - < → &lt;
 * - > → &gt;
 * - & → &amp;
 * - " → &quot;
 * - ' → &#039; (ENT_QUOTESフラグ使用時)
 */

echo "✅ 安全な方法: htmlspecialchars()を使用\n";
$userName = '<script>alert("XSS")</script>';
$safeHtml = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
echo "エスケープ前: $userName\n";
echo "エスケープ後: $safeHtml\n";
echo "→ スクリプトタグが無害化されました\n\n";

/**
 * htmlspecialchars()のフラグ
 */
echo "--- 5. htmlspecialchars()のフラグ ---\n";

$testString = '<a href="test">リンク</a> & "引用符" と \'シングル\'';

// ENT_COMPAT: ダブルクォートのみエスケープ（デフォルト）
echo "ENT_COMPAT:\n";
echo htmlspecialchars($testString, ENT_COMPAT, 'UTF-8') . "\n\n";

// ENT_QUOTES: ダブルクォートとシングルクォートをエスケープ（推奨）
echo "ENT_QUOTES（推奨）:\n";
echo htmlspecialchars($testString, ENT_QUOTES, 'UTF-8') . "\n\n";

// ENT_NOQUOTES: クォートをエスケープしない（非推奨）
echo "ENT_NOQUOTES（非推奨）:\n";
echo htmlspecialchars($testString, ENT_NOQUOTES, 'UTF-8') . "\n\n";

echo "--- 6. コンテキストに応じたエスケープ ---\n";
/**
 * HTMLの出力位置によって、適切なエスケープ方法が異なります。
 */

echo "✅ HTMLコンテンツ内（タグの間）\n";
$content = '<script>alert("XSS")</script>';
$escaped = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
echo "HTML: <div>$escaped</div>\n\n";

echo "✅ HTML属性内\n";
$title = '" onclick="alert(\'XSS\')" data-x="';
$escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
echo "HTML: <div title=\"$escaped\">テキスト</div>\n";
echo "→ 属性値は必ずダブルクォートで囲み、ENT_QUOTESを使用\n\n";

echo "✅ JavaScript内での出力\n";
$jsString = "'; alert('XSS'); //";
// JavaScriptではjson_encode()を使用（推奨）
$escaped = json_encode($jsString, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
echo "JavaScript: var message = $escaped;\n";
echo "→ json_encode()を使用すると安全にエスケープできます\n\n";

echo "✅ URL内での出力\n";
$url = 'javascript:alert("XSS")';
// URLエンコードだけでは不十分
$urlEncoded = urlencode($url);
echo "URLエンコードのみ: $urlEncoded\n";

// URLのバリデーションも必要
if (filter_var($url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $url)) {
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo "安全なURL: $safeUrl\n";
} else {
    echo "無効なURL: URLスキームのバリデーションに失敗しました\n";
    echo "→ javascript:スキームなどを除外します\n";
}
echo "\n";

echo "--- 7. ヘルパー関数の作成 ---\n";
/**
 * エスケープ処理を簡潔に記述するためのヘルパー関数
 */

/**
 * HTMLエスケープのヘルパー関数
 */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * JavaScript用のエスケープ関数
 */
function js(mixed $value): string
{
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}

/**
 * URL用のエスケープ関数（バリデーション付き）
 */
function url(string $url): string
{
    // ホワイトリスト方式: http/httpsのみ許可
    if (!preg_match('/^https?:\/\//', $url)) {
        return '';
    }

    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

echo "✅ ヘルパー関数の使用例\n";
$userName = '<script>alert("XSS")</script>';
$userMessage = 'こんにちは、"世界"！';
$userUrl = 'https://example.com/page?id=123&name=test';

echo "HTML: <div>" . h($userName) . "</div>\n";
echo "JavaScript: var msg = " . js($userMessage) . ";\n";
echo "URL: <a href=\"" . url($userUrl) . "\">リンク</a>\n\n";

echo "--- 8. 危険なURLスキームの防御 ---\n";
/**
 * javascript:、data:、vbscript:などの危険なURLスキームを防ぐ
 */

/**
 * 安全なURLかチェックする関数
 */
function isSafeUrl(string $url): bool
{
    // 空文字列は安全
    if ($url === '') {
        return true;
    }

    // 相対URLは許可
    if (str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
        // パストラバーサル攻撃を防ぐ
        if (str_contains($url, '..')) {
            return false;
        }
        return true;
    }

    // http/httpsのみ許可
    return preg_match('/^https?:\/\//i', $url) === 1;
}

echo "✅ URLスキームの検証\n";
$testUrls = [
    'https://example.com' => true,
    'http://example.com' => true,
    '/page/about' => true,
    './style.css' => true,
    'javascript:alert("XSS")' => false,
    'data:text/html,<script>alert("XSS")</script>' => false,
    'vbscript:msgbox("XSS")' => false,
    '../../../etc/passwd' => false,
];

foreach ($testUrls as $testUrl => $expected) {
    $isSafe = isSafeUrl($testUrl);
    $result = $isSafe === $expected ? '✓' : '✗';
    echo "$result URL: $testUrl → " . ($isSafe ? '安全' : '危険') . "\n";
}
echo "\n";

echo "--- 9. リッチテキストエディタの対策 ---\n";
/**
 * ユーザーがHTMLを入力できる場合（リッチテキストエディタなど）は、
 * HTMLパーサーライブラリを使用してホワイトリスト方式でサニタイズします。
 *
 * 推奨ライブラリ:
 * - HTML Purifier (ezyang/htmlpurifier)
 * - DOMPurify (JavaScriptライブラリ、サーバーサイドでも使用可能)
 */

echo "⚠️ リッチテキストの処理\n";
echo "ユーザーがHTMLを入力できる場合は、以下の対策が必要です:\n";
echo "  1. HTMLパーサーライブラリでサニタイズ（HTML Purifierなど）\n";
echo "  2. 許可するタグ・属性をホワイトリスト方式で定義\n";
echo "  3. JavaScriptイベント属性（onclick等）は全て削除\n";
echo "  4. <script>、<iframe>、<object>などの危険なタグは削除\n\n";

// 簡易的なホワイトリスト方式の例
function sanitizeHtml(string $html): string
{
    // 許可するタグ
    $allowedTags = '<p><br><strong><em><u><a><ul><ol><li>';

    // strip_tags()で許可されたタグ以外を削除
    $cleaned = strip_tags($html, $allowedTags);

    // aタグのhref属性をチェック（簡易版）
    // 実運用ではHTML Purifierなどのライブラリを使用すべき
    $cleaned = preg_replace_callback(
        '/<a\s+href=["\']([^"\']*)["\'][^>]*>/i',
        function ($matches) {
            $href = $matches[1];
            if (isSafeUrl($href)) {
                return '<a href="' . h($href) . '">';
            }
            return '<a>';
        },
        $cleaned
    );

    return $cleaned;
}

echo "✅ 簡易的なHTMLサニタイゼーション\n";
$userHtml = '<p>こんにちは</p><script>alert("XSS")</script><a href="javascript:alert(\'XSS\')">リンク</a>';
$sanitized = sanitizeHtml($userHtml);
echo "入力: $userHtml\n";
echo "出力: $sanitized\n";
echo "→ <script>タグと危険なhrefが削除されました\n\n";

echo "--- 10. Content Security Policy (CSP) ---\n";
/**
 * CSPは、HTTPヘッダーでスクリプトの実行ポリシーを定義し、
 * XSS攻撃を軽減するセキュリティ機能です。
 */

echo "✅ Content Security Policy の例\n";
echo "HTTPヘッダーで設定:\n";
echo "  Content-Security-Policy: default-src 'self'; script-src 'self' https://trusted.cdn.com; style-src 'self' 'unsafe-inline'\n\n";

echo "CSPの主要なディレクティブ:\n";
echo "  - default-src: デフォルトのポリシー\n";
echo "  - script-src: JavaScriptの読み込み元\n";
echo "  - style-src: CSSの読み込み元\n";
echo "  - img-src: 画像の読み込み元\n";
echo "  - connect-src: AJAX、WebSocketの接続先\n";
echo "  - frame-src: <iframe>の読み込み元\n\n";

echo "CSPの値:\n";
echo "  - 'self': 同じオリジンのみ許可\n";
echo "  - 'none': 全て禁止\n";
echo "  - 'unsafe-inline': インラインスクリプト/スタイルを許可（非推奨）\n";
echo "  - 'unsafe-eval': eval()を許可（非推奨）\n";
echo "  - https://example.com: 特定のドメインを許可\n\n";

// PHPでCSPヘッダーを設定する例（実際のWebアプリケーション用）
$cspHeader = "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'";
echo "PHPでの設定例:\n";
echo "header(\"Content-Security-Policy: $cspHeader\");\n\n";

echo "--- 11. まとめ：XSS対策のベストプラクティス ---\n";
echo "✅ 推奨される対策:\n";
echo "  1. 出力時に必ずhtmlspecialchars()でエスケープ\n";
echo "  2. ENT_QUOTESフラグを使用してシングルクォートもエスケープ\n";
echo "  3. コンテキストに応じたエスケープ（HTML、JavaScript、URL）\n";
echo "  4. URLはホワイトリスト方式でバリデーション\n";
echo "  5. リッチテキストはHTMLパーサーでサニタイズ\n";
echo "  6. Content Security Policy (CSP)を設定\n";
echo "  7. HTTPOnlyフラグでクッキーをJavaScriptから保護\n\n";

echo "❌ 避けるべき方法:\n";
echo "  1. ユーザー入力をエスケープせずにHTMLに出力\n";
echo "  2. strip_tags()のみに依存（属性内のXSSを防げない）\n";
echo "  3. 正規表現でのHTMLサニタイズ（複雑で漏れが発生しやすい）\n";
echo "  4. ブラックリスト方式（<script>だけ削除など）\n\n";

echo "=== Phase 3.3: XSS対策 完了 ===\n";
