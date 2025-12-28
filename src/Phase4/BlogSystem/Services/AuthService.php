<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Services;

use App\Phase4\BlogSystem\Entities\User;
use App\Phase4\BlogSystem\Repositories\UserRepository;
use InvalidArgumentException;

/**
 * 認証サービス
 *
 * ユーザー認証とセッション管理を担当
 */
class AuthService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * ユーザー登録
     *
     * @param string $username ユーザー名
     * @param string $email メールアドレス
     * @param string $password パスワード
     * @param string $displayName 表示名
     * @return User 作成されたユーザー
     * @throws InvalidArgumentException バリデーションエラー
     */
    public function register(string $username, string $email, string $password, string $displayName): User
    {
        // ユーザー名の重複チェック
        if ($this->userRepository->findByUsername($username) !== null) {
            throw new InvalidArgumentException('このユーザー名は既に使用されています');
        }

        // メールアドレスの重複チェック
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new InvalidArgumentException('このメールアドレスは既に登録されています');
        }

        // パスワードのバリデーション
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('パスワードは8文字以上で指定してください');
        }

        // ユーザーを作成
        return $this->userRepository->create($username, $email, $password, $displayName);
    }

    /**
     * ログイン
     *
     * @param string $username ユーザー名
     * @param string $password パスワード
     * @return User ログインしたユーザー
     * @throws InvalidArgumentException 認証失敗
     */
    public function login(string $username, string $password): User
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user === null || !$user->verifyPassword($password)) {
            throw new InvalidArgumentException('ユーザー名またはパスワードが正しくありません');
        }

        // セッションを開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッション固定化攻撃対策
        session_regenerate_id(true);

        // ユーザーIDをセッションに保存
        $_SESSION['user_id'] = $user->getId();

        // パスワードの再ハッシュ化が必要な場合
        if ($user->needsRehash()) {
            $user->changePassword($password);
            $this->userRepository->update($user);
        }

        return $user;
    }

    /**
     * ログアウト
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションデータを破棄
        $_SESSION = [];

        // セッションクッキーを削除
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // セッションを破棄
        session_destroy();
    }

    /**
     * 現在ログイン中のユーザーを取得
     *
     * @return User|null ログイン中のユーザー
     */
    public function getCurrentUser(): ?User
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->userRepository->findById((int) $_SESSION['user_id']);
    }

    /**
     * ログイン中かチェック
     *
     * @return bool ログイン中の場合true
     */
    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }
}
