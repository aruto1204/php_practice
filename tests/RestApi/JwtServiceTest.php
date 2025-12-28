<?php

declare(strict_types=1);

namespace Tests\RestApi;

use PHPUnit\Framework\TestCase;
use Phase4\RestApi\Services\JwtService;

/**
 * JWTサービスのテスト
 */
class JwtServiceTest extends TestCase
{
    private JwtService $jwtService;

    protected function setUp(): void
    {
        $this->jwtService = new JwtService();
    }

    /**
     * @test
     */
    public function アクセストークンを生成できる(): void
    {
        $token = $this->jwtService->generateAccessToken(1, 'testuser', false);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // トークンは3つのパートから構成される
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    /**
     * @test
     */
    public function リフレッシュトークンを生成できる(): void
    {
        $token = $this->jwtService->generateRefreshToken(1);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /**
     * @test
     */
    public function トークンをデコードできる(): void
    {
        $token = $this->jwtService->generateAccessToken(1, 'testuser', false);
        $payload = $this->jwtService->decode($token);

        $this->assertIsArray($payload);
        $this->assertEquals(1, $payload['user_id']);
        $this->assertEquals('testuser', $payload['username']);
        $this->assertFalse($payload['is_admin']);
        $this->assertEquals('access', $payload['type']);
    }

    /**
     * @test
     */
    public function 無効なトークンをデコードすると例外が発生する(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jwtService->decode('invalid.token.here');
    }

    /**
     * @test
     */
    public function トークンを検証できる(): void
    {
        $token = $this->jwtService->generateAccessToken(1, 'testuser', false);

        $this->assertTrue($this->jwtService->verify($token));
        $this->assertFalse($this->jwtService->verify('invalid.token.here'));
    }

    /**
     * @test
     */
    public function トークンからユーザーIDを取得できる(): void
    {
        $token = $this->jwtService->generateAccessToken(123, 'testuser', false);
        $userId = $this->jwtService->getUserId($token);

        $this->assertEquals(123, $userId);
    }

    /**
     * @test
     */
    public function トークンタイプを取得できる(): void
    {
        $accessToken = $this->jwtService->generateAccessToken(1, 'testuser', false);
        $refreshToken = $this->jwtService->generateRefreshToken(1);

        $this->assertEquals('access', $this->jwtService->getTokenType($accessToken));
        $this->assertEquals('refresh', $this->jwtService->getTokenType($refreshToken));
    }

    /**
     * @test
     */
    public function 管理者フラグが正しく設定される(): void
    {
        $adminToken = $this->jwtService->generateAccessToken(1, 'admin', true);
        $userToken = $this->jwtService->generateAccessToken(2, 'user', false);

        $adminPayload = $this->jwtService->decode($adminToken);
        $userPayload = $this->jwtService->decode($userToken);

        $this->assertTrue($adminPayload['is_admin']);
        $this->assertFalse($userPayload['is_admin']);
    }
}
