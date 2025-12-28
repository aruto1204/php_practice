<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem;

/**
 * HTMLエスケープヘルパー
 *
 * @param string|null $string エスケープする文字列
 * @return string エスケープされた文字列
 */
function h(?string $string): string
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * JavaScriptエスケープヘルパー
 *
 * @param string|null $string エスケープする文字列
 * @return string エスケープされた文字列
 */
function js(?string $string): string
{
    if ($string === null) {
        return '';
    }
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * URLエスケープヘルパー
 *
 * @param string|null $url エスケープするURL
 * @return string エスケープされたURL
 */
function url(?string $url): string
{
    if ($url === null) {
        return '';
    }
    return urlencode($url);
}

/**
 * CSRFトークンを生成
 *
 * @return string CSRFトークン
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークンを検証
 *
 * @param string $token 検証するトークン
 * @return bool 検証結果
 */
function verify_csrf_token(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * リダイレクト
 *
 * @param string $url リダイレクト先URL
 * @param int $statusCode HTTPステータスコード
 */
function redirect(string $url, int $statusCode = 302): never
{
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * フラッシュメッセージをセット
 *
 * @param string $key メッセージのキー
 * @param string $message メッセージ
 */
function set_flash(string $key, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'][$key] = $message;
}

/**
 * フラッシュメッセージを取得
 *
 * @param string $key メッセージのキー
 * @return string|null メッセージ
 */
function get_flash(string $key): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

/**
 * 現在のユーザーIDを取得
 *
 * @return int|null ユーザーID
 */
function current_user_id(): ?int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user_id'] ?? null;
}

/**
 * ログイン中かチェック
 *
 * @return bool ログイン中の場合true
 */
function is_logged_in(): bool
{
    return current_user_id() !== null;
}

/**
 * 日付をフォーマット
 *
 * @param \DateTimeInterface $date 日付
 * @param string $format フォーマット
 * @return string フォーマットされた日付
 */
function format_date(\DateTimeInterface $date, string $format = 'Y年m月d日 H:i'): string
{
    return $date->format($format);
}
