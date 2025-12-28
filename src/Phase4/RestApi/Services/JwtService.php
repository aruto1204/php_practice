<?php

declare(strict_types=1);

namespace Phase4\RestApi\Services;

use InvalidArgumentException;

/**
 * JWTサービス
 *
 * JSON Web Token (JWT)の生成、検証、デコードを担当
 */
class JwtService
{
    // シークレットキー（本番環境では環境変数から取得すべき）
    private const SECRET_KEY = 'your-256-bit-secret-key-change-this-in-production';

    // トークンの有効期限
    private const ACCESS_TOKEN_EXPIRY = 900; // 15分
    private const REFRESH_TOKEN_EXPIRY = 604800; // 7日

    /**
     * アクセストークンを生成
     *
     * @param int $userId ユーザーID
     * @param string $username ユーザー名
     * @param bool $isAdmin 管理者フラグ
     * @return string
     */
    public function generateAccessToken(int $userId, string $username, bool $isAdmin): string
    {
        $payload = [
            'user_id' => $userId,
            'username' => $username,
            'is_admin' => $isAdmin,
            'type' => 'access',
            'iat' => time(), // Issued At
            'exp' => time() + self::ACCESS_TOKEN_EXPIRY, // Expiration Time
        ];

        return $this->encode($payload);
    }

    /**
     * リフレッシュトークンを生成
     *
     * @param int $userId ユーザーID
     * @return string
     */
    public function generateRefreshToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + self::REFRESH_TOKEN_EXPIRY,
        ];

        return $this->encode($payload);
    }

    /**
     * トークンをデコード
     *
     * @param string $token JWT
     * @return array<string, mixed> ペイロード
     * @throws InvalidArgumentException 無効なトークンの場合
     */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException('無効なトークン形式です');
        }

        [$headerEncoded, $payloadEncoded, $signature] = $parts;

        // 署名を検証
        $expectedSignature = $this->sign($headerEncoded . '.' . $payloadEncoded);
        if (!hash_equals($expectedSignature, $signature)) {
            throw new InvalidArgumentException('トークンの署名が無効です');
        }

        // ペイロードをデコード
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        if (!is_array($payload)) {
            throw new InvalidArgumentException('ペイロードのデコードに失敗しました');
        }

        // 有効期限をチェック
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new InvalidArgumentException('トークンの有効期限が切れています');
        }

        return $payload;
    }

    /**
     * トークンを検証（例外を投げずにboolを返す）
     *
     * @param string $token JWT
     * @return bool
     */
    public function verify(string $token): bool
    {
        try {
            $this->decode($token);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * ペイロードをエンコードしてJWTを生成
     *
     * @param array<string, mixed> $payload ペイロード
     * @return string JWT
     */
    private function encode(array $payload): string
    {
        // ヘッダー
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        // 署名
        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * 署名を生成
     *
     * @param string $data 署名対象データ
     * @return string 署名
     */
    private function sign(string $data): string
    {
        $hash = hash_hmac('sha256', $data, self::SECRET_KEY, true);
        return $this->base64UrlEncode($hash);
    }

    /**
     * Base64URL エンコード
     *
     * @param string $data データ
     * @return string エンコード済み文字列
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL デコード
     *
     * @param string $data エンコード済み文字列
     * @return string デコード済みデータ
     */
    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * トークンからユーザーIDを取得
     *
     * @param string $token JWT
     * @return int|null
     */
    public function getUserId(string $token): ?int
    {
        try {
            $payload = $this->decode($token);
            return $payload['user_id'] ?? null;
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * トークンのタイプを取得
     *
     * @param string $token JWT
     * @return string|null
     */
    public function getTokenType(string $token): ?string
    {
        try {
            $payload = $this->decode($token);
            return $payload['type'] ?? null;
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
